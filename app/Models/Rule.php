<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\Fields\Field;
use App\MMAction\Action;
use App\MMAction\AlterTarget;
use App\MMAction\AssignLoad;
use App\MMAction\ScaleTarget;
use App\MMAction\SetDecimalColumn;
use App\MMAction\SetStringColumn;
use App\MMAction\SetTarget;
use App\MMScript;
use App\RecordReport;
use Exception;
use DB;
use Validator;

/** @noinspection PhpUndefinedClassInspection */

/**
 * Class Rule
 * @property DocumentRevision documentRevision
 * @property int rank
 * @property ReportType reportType
 * @property int report_type_sid
 * @property array data
 * @property int document_revision_id
 * @package App\Models
 */
class Rule extends DocumentPart
{
    /**
     * Relationship
     * @return ReportType
     */
    public function reportType()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne('App\Models\ReportType', 'sid', 'report_type_sid')
            ->where('document_revision_id', $this->document_revision_id);
    }

    // there's probably a cleverer laravel way of doing this...
    /**
     * @var array
     */
    static protected $actions = [
        SetTarget::class,
        AlterTarget::class,
        ScaleTarget::class,
        AssignLoad::class,
        SetStringColumn::class,
        SetDecimalColumn::class,
    ];

    /**
     * @var array[Action]
     */
    static protected $actionCache;

    /**
     * @return array
     */
    public static function actions()
    {
        if (self::$actionCache) {
            return self::$actionCache;
        }
        self::$actionCache = [];
        foreach (self::$actions as $class) {
            $action = new $class();
            self::$actionCache[$action->name] = $action;
        }
        return self::$actionCache;
    }

    /**
     * @param string $actionName
     * @return \App\MMAction\Action
     */
    public static function actionFactory($actionName)
    {
        $actions = self::actions();
        return $actions[$actionName];
    }

    /**
     * @throws DataStructValidationException,Exception
     */
    public function validate()
    {
        $actions = Rule::actions();
        $validator = Validator::make(
            $this->data,
            [
                'action' => 'required|string|in:' . join(",", array_keys($actions)),
                'trigger' => 'string',
                'title' => 'string',
                'route' => 'array',
                'params' => 'array']);

        if ($validator->fails()) {
            throw new DataStructValidationException("Validation fail in rule.data: " . join(", ", $validator->errors()));
        }

        // run this function just to let it throw an exception
        $this->abstractContext();

        if (@$this->data["trigger"]) {
            $trigger = $this->script($this->data["trigger"]);
            $type = $trigger->type();
            if ($type != "boolean") {
                // TODO better class of exception?
                throw new DataStructValidationException("Trigger must either be unset or evaluate to true/false. Currently evaluates to $type");
            }
        }
        $action = $this->getAction();
        /** @var Field $field */
        foreach ($action->fields as $field) {
            if (!array_key_exists($field->data["name"], $this->data["params"])) {
                if ($field->required()) {
                    throw new DataStructValidationException("Action " . $action->name . " requires param '" . $field->data["name"] . "'");
                }
                continue;
            }
            $script = $this->script($this->data["params"][$field->data["name"]]);
            $type = $script->type();

            // not doing full autocasting but doing a special case to let decimal fields accpet integers
            $typeMatch = false;
            if ($type == $field->data["type"]) {
                $typeMatch = true;
            }
            if ($type == "integer" && $field->data["type"] == "decimal") {
                $typeMatch = true;
            }

            if (!$typeMatch) {
                throw new DataStructValidationException("Action " . $action->name . " param '" . $field->data["name"] . "' requires a value of type '" . $field->data["type"] . "' but got given '$type'");
            }
        }
    }

    /**
     * Return the action associated with this rule.
     * @return Action
     */
    public function getAction()
    {
        return Rule::actionFactory($this->data["action"]);
    }

    protected $scripts = [];

    /**
     * @param string $scriptText
     * @return MMScript
     */
    function script($scriptText)
    {
        if (isset($this->scripts[$scriptText])) {
            return $this->scripts[$scriptText];
        }
        $this->scripts[$scriptText] = new MMScript(
            $scriptText,
            $this->documentRevision,
            $this->abstractContext());
        return $this->scripts[$scriptText];
    }

    protected $abstractContext;
    // get the absract context for this rule. Returns record & link types,
    // not specific records and links
    /**
     * @return array
     * @throws Exception
     */
    public function abstractContext()
    {
        if (isset($this->abstractContext)) {
            return $this->abstractContext;
        }

        $this->abstractContext = [];

        $baseRecordType = $this->reportType->baseRecordType();
        $this->abstractContext[$baseRecordType->name] = $baseRecordType;
        // add all the other objects in the route
        $iterativeRecordType = $baseRecordType;

        // simple case
        if (!isset($this->data['route'])) {
            return $this->abstractContext;
        }

        foreach ($this->data['route'] as $linkName) {
            $fwd = true;
            if (substr($linkName, 0, 1) == "^") {
                $linkName = substr($linkName, 1);
                $fwd = false;
            }
            $linkType = $this->documentRevision->linkTypeByName($linkName);
            if (!$linkType) {
                // not sure what type of exception to make this (Script?)
                throw new Exception("Unknown linkName in context '$linkName'");
            }

            if ($fwd) {
                // check the domain of this link is the right recordtype
                if ($linkType->domain_sid != $iterativeRecordType->sid) {
                    throw new Exception("Domain of $linkName is not " . $iterativeRecordType->name);
                }
                $iterativeRecordType = $linkType->range;
            } else {
                // backlink, so check range, set type to domain
                if ($linkType->range_sid != $iterativeRecordType->sid) {
                    throw new Exception("Range of $linkName is not " . $iterativeRecordType->name);
                }
                $iterativeRecordType = $linkType->domain;
            }

            $name = $iterativeRecordType->name;

            // in case we meet the same class twice, will fallback
            // to class, class2, class3, etc.
            $i = 2;
            while (array_key_exists($name, $this->abstractContext)) {
                $name = $linkType->name . "$i";
                $i++;
            }
            $this->abstractContext[$name] = $iterativeRecordType;

        }

        return $this->abstractContext;
    }

    /**
     * @param Record $record
     * @param RecordReport $recordReport
     */
    public function apply($record, $recordReport)
    {
        $context = [];
        $baseRecordType = $this->reportType->baseRecordType();
        $context[$baseRecordType->name] = $record;
        $route = [];
        if (isset($this->data['route'])) {
            $route = $this->data['route'];
        }
        $this->applyToRoute($recordReport, $context, $route, $record);
    }

    // recursive function used to apply this rule to the record for every context possible with the given route

    /**
     * @param RecordReport $recordReport - the report to write to for this record
     * @param array $context - the context of the route followed so far
     * @param array $route - the remaining route to follow to complete the context
     * @param Record $focusObject - the object to which the remaining route applies
     * @throws Exception
     */
    private function applyToRoute($recordReport, $context, $route, $focusObject)
    {
        if (sizeof($route) == 0) {
            $this->applyToContext($recordReport, $context);
            return;
        }

        // follow the top link on the route
        $linkName = array_shift($route);

        $fwd = true;
        if (substr($linkName, 0, 1) == "^") {
            $linkName = substr($linkName, 1);
            $fwd = false;
        }
        $linkType = $this->documentRevision->linkTypeByName($linkName);
        if (!$linkType) {
            // not sure what type of exception to make this (Script?)
            throw new Exception("Unknown linkName in context '$linkName'");
        }

        if ($fwd) {
            // get ids of records of instances of this link for which the focus object is the subject
            $nextFocusObjectsSids = DB::table('links')
                ->where("links.document_revision_id", "=", $this->documentRevision->id)
                ->where("links.subject_sid", '=', $focusObject->sid)
                ->where("links.link_type_sid", '=', $linkType->sid)
                ->pluck("links.object_sid");
        } else {
            // get ids of records of instances of this link for which the focus object is the object
            $nextFocusObjectsSids = DB::table('links')
                ->where("links.document_revision_id", "=", $this->documentRevision->id)
                ->where("links.object_sid", '=', $focusObject->sid)
                ->where("links.link_type_sid", '=', $linkType->sid)
                ->pluck("links.subject_sid");
        }

        if (count($nextFocusObjectsSids) == 0) {
            return; // this route doesn't resolve to any contexts to run the rule in
        }
        $nextThing = $this->documentRevision->records()->getQuery()->where('sid', '=', $nextFocusObjectsSids[0])->first();
        $baseNextTypeName = $nextThing->recordType->name;
        $nextTypeName = $baseNextTypeName;
        // in case we meet the same class twice, will fallback
        // to class, class2, class3, etc.
        $i = 2;
        while (array_key_exists($nextTypeName, $context)) {
            $nextTypeName = $baseNextTypeName . $i;
            $i++;
        }
        foreach ($nextFocusObjectsSids as $sid) {
            /** @var Record $nextFocusObject */
            $nextFocusObject = $this->documentRevision->records()->getQuery()->where('sid', '=', $sid)->first();

            $context[$nextTypeName] = $nextFocusObject;
            $this->applyToRoute($recordReport, $context, $route, $nextFocusObject);
        }
    }

    /**
     * @param RecordReport $recordReport
     * @param array $context
     */
    private function applyToContext($recordReport, $context)
    {
        if (isset($this->data["trigger"])) {
            $trigger = $this->script($this->data["trigger"]);
            $result = $trigger->execute($context);
            if (!$result->value) {
                return;
            }
        }

        $action = $this->getAction();
        $params = [];
        foreach ($action->fields as $field) {
            $fieldName = $field->data["name"];
            $paramCode = @$this->data["params"][$fieldName];
            if (!isset($paramCode)) {
                continue;
            }
            $script = $this->script($paramCode);
            $params[$fieldName] = $script->execute($context)->value;
        }
        $action->execute($recordReport, $params);
    }

}
