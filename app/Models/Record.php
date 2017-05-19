<?php

namespace App\Models;

use App\Exceptions\MMValidationException;
use App\Exceptions\ScriptException;
use App\Fields\Field;
use App\Http\TitleMaker;
use App\MMScript\Values\Value;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Validator;

/**
 * @property DocumentRevision documentRevision
 * @property int sid
 * @property RecordType recordType
 * @property int document_revision_id
 * @property string array
 * @property Collection forwardLinks
 * @property Collection backLinks
 * @property int record_type_sid
 * @property array data
 */
class Record extends DocumentPart
{
    private $external;
    private $external_loaded = false;

    public function __get($key)
    {
        if ($key == 'recordType') {
            return $this->recordType();
        }
        return parent::__get($key);
    }

    /**
     * @return RecordType
     */
    public function recordType()
    {
        return $this->documentRevision->recordType($this->record_type_sid);
    }

    /**
     * Access via parameter
     * @return Collection (list of Link)
     */
    public function forwardLinks()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->forwardLinks";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->hasMany('App\Models\Link', 'subject_sid', 'sid')
                ->where('document_revision_id', $this->document_revision_id);
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * Access via parameter
     * @return Collection (list of Link)
     */
    public function backLinks()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->backLinks";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->hasMany('App\Models\Link', 'object_sid', 'sid')
                ->where('document_revision_id', $this->document_revision_id);
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * Return an array of the local values of fields
     * @return array
     */
    public function localValues()
    {
        return $this->data;
    }


    public function externalValues()
    {
        $values = [];
        foreach ($this->recordType->fields() as $field) {
            $values[$field->name()] = $this->getExternal($field->name());
        }
        return $values;
    }

    /**
     * @param string $fieldName
     * @return mixed
     */
    public function getExternal($fieldName)
    {
        $field = $this->recordType->field($fieldName);

        // if this is a local value get it from the SQL
        // candidate for caching if it's slow.
        if (!empty($field->data['external_column'])
            && !empty($field->data['external_table'])
            && !empty($field->data['external_key'])
            && !empty($field->data['external_local_key'])
        ) {
            // this is an external value from a table other than the primary one
            $localKeyValue = $this->getLocal($field->data['external_local_key']);
            if (empty($localKeyValue)) {
                return null;
            }
            $tableName = 'imported_' . $field->data['external_table'];
            $table = DB::table($tableName);
            $row = $table->where(
                $field->data['external_key'],
                $localKeyValue)->first();
            try {
                $localName = $field->data["external_column"];
                if (!empty($localName)) {
                    return $row->$localName;
                }
            } catch (\ErrorException $e) {
                // didn't exist
            }
            return null;
        }


        if (empty($this->recordType->external_table)) {
            return null;
        }
        $localKeyValue = $this->getLocal($this->recordType->external_local_key);

        if (empty($localKeyValue)) {
            return null;
        }

        if (!$this->external_loaded) {
            // load
            $tableName = 'imported_' . $this->recordType->external_table;
            $table = DB::table($tableName);
            $row = $table->where(
                $this->recordType->external_key,
                $localKeyValue)->first();
            $this->external = $row;
            $this->external_loaded = true;
        }
        try {
            $localName = $field->data["external_column"];
            if (!empty($localName)) {
                return $this->external->$localName;
            }
        } catch (\ErrorException $e) {
            // didn't exist
        }
        return null;
    }

    /**
     * If this field has a script which doesn't work it throws an exception rather
     * than return a default value
     * @param string $fieldName
     * @return mixed
     * @throws ScriptException
     */
    public function getLocal($fieldName)
    {
        $field = $this->recordType->field($fieldName);

        if ($field->hasScript()) {
            // failing scripts should pass exceptions upwards

            $script = $field->getScript($this->recordType);

            $result = $script->execute([
                "record" => $this,
                "config" => $this->documentRevision->configRecord()
            ]);

            return $result->value;
        }

        if (!empty($this->data) && array_key_exists($fieldName, $this->data)) {
            return $this->data[$fieldName];
        }
        return null;
    }

    /**
     * Get the typed value (or null value object) from a field
     * @param string $fieldName
     * @return Value
     * @throws Exception
     */
    public function getValue($fieldName)
    {
        $field = $this->recordType->field($fieldName);

        $value = null;
        $mode = $field->getMode();

        if ($mode == 'prefer_local') {
            // local, exernal, default
            $value = $this->getLocal($fieldName);
            if ($value === null || $value === "") {
                $value = $this->getExternal($fieldName);
            }
        } elseif ($mode == 'prefer_external') {
            // external, local, default
            $value = $this->getExternal($fieldName);
            if ($value === null || $value === "") {
                $value = $this->getLocal($fieldName);
            }
        } elseif ($mode == 'only_local') {
            // local, default
            $value = $this->getLocal($fieldName);
        } elseif ($mode == 'only_external') {
            // external, default
            $value = $this->getExternal($fieldName);
        } else {
            throw new Exception("Unknown field mode: '" . $field->data["mode"] . "'");
        }
        if ($value === null || $value === "") {
            $value = $this->getDefault($fieldName);
        }

        return $field->makeValue($value);
    }

    /**
     * @param string $fieldName
     * @return mixed
     */
    public function getDefault($fieldName)
    {
        $field = $this->recordType->field($fieldName);
        if (isset($field->data['default'])) {
            return $field->data['default'];
        }
        return null;
    }

    /**
     * Return the source for a field's data.
     * @param string $fieldName
     * @return string
     * @throws Exception
     */
    public function getSource($fieldName)
    {
        $field = $this->recordType->field($fieldName);
        $value = null;
        $mode = $field->getMode();
        if ($mode == 'prefer_local') {
            // local, exernal, default
            $value = $this->getLocal($fieldName);
            if ($value !== null || $value != "") {
                return "local";
            }
            $value = $this->getExternal($fieldName);
            if ($value !== null || $value != "") {
                return "external";
            }
        } elseif ($mode == 'prefer_external') {
            // external, local, default
            $value = $this->getExternal($fieldName);
            if ($value !== null || $value != "") {
                return "external";
            }
            $value = $this->getLocal($fieldName);
            if ($value !== null || $value != "") {
                return "local";
            }
        } elseif ($mode == 'only_local') {
            // local, default
            $value = $this->getLocal($fieldName);
            if ($value !== null || $value != "") {
                return "local";
            }
        } elseif ($mode == 'only_external') {
            // external, default
            $value = $this->getExternal($fieldName);
            if ($value !== null || $value != "") {
                return "external";
            }
        } else {
            throw new Exception("Unknown field mode: '" . $field->data["mode"] . "'");
        }
        $value = $this->getDefault($fieldName);
        if ($value !== null || $value != "") {
            return "default";
        }
        return "none";
    }

    /**
     * @param string $indent
     * @return string
     */
    function dumpText($indent = "")
    {
        $r = "";
        $r .= $indent . "" . $this->recordType->name . "#" . $this->sid . " " . json_encode($this->data) . "\n";
        foreach ($this->forwardLinks as $link) {
            $r .= $indent . "  ->" . $link->linkType->name . "->\n";
            $r .= $link->objectRecord->dumpText($indent . "    ");
        }
        return $r;
    }


    /**
     * @throws MMValidationException
     */
    public function validate()
    {
        $validationCodes = [];
        foreach ($this->recordType->fields() as $field) {
            /** @var Field $field */
            $validationCodes[$field->data["name"]] = $field->valueValidationCode();
        }

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($this->data, $validationCodes);
        if ($validator->fails()) {
            throw $this->makeValidationException($validator);
        }
    }

    // return a text representation and all associated records 
    // following subject->object direction links only.
    // does not (yet) worry about loops.

    /**
     * Validate forward links to be added to this record
     *  must be relevant links and a legal number
     *  $links are of the format [ link_name=>[ $record,... ]]
     * @param Record[][] $links
     * @throws MMValidationException
     */
    public function validateWithForwardLinks($links)
    {
        $linkTypes = $this->recordType->forwardLinkTypes;
        $unknownLinks = $links; // we'll reduce this list to actually unknown items
        $issues = [];
        foreach ($linkTypes as $linkType) {
            // check domain restrictions
            if (isset($linkType->domain_min)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) < $linkType->domain_min
            ) {
                $issues [] = "Expected minimum of " . $linkType->domain_min . " forward links of type " . $linkType->title();
            }
            if (isset($linkType->domain_max)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) > $linkType->domain_max
            ) {
                $issues [] = "Expected maximum of " . $linkType->domain_max . " forward links of type " . $linkType->title();
            }
            // check target object(s) are correct type
            if (isset($links[$linkType->name])) {
                foreach ($links[$linkType->name] as $record) {
                    $linkType->validateLinkObject($record);
                    // could/should check also  $record can accept this additional incoming link
                }
            }

            unset($unknownLinks[$linkType->name]);
        }
        if (count($unknownLinks)) {
            foreach ($unknownLinks as $linkName => $record) {
                $issues [] = "Attempt to add an invalid link type: $linkName";
            }
        }
        if (count($issues)) {
            throw new MMValidationException("Validation fail in recordtype.forwardLinks: " . join(", ", $issues));
        }
    }

    /**
     * Validate back links to be added to this record
     *  must be relevant links and a legal number
     *  $links are of the format [ link_name=>[ $record,... ]]
     * @param $links
     * @throws MMValidationException
     */
    public function validateWithBackLinks($links)
    {
        $linkTypes = $this->recordType->backLinkTypes;
        $unknownLinks = $links; // we'll reduce this list to actually unknown items
        $issues = [];
        foreach ($linkTypes as $linkType) {
            // check range restrictions
            if (isset($linkType->range_min)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) < $linkType->range_min
            ) {
                $issues [] = "Expected minimum of " . $linkType->range_min . " back links of type " . $linkType->title();
            }
            if (isset($linkType->dataCache)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) > $linkType->range_max
            ) {
                $issues [] = "Expected maximum of " . $linkType->range_max . " back links of type " . $linkType->title();
            }
            // check target subject(s) are correct type
            if (@$links[$linkType->name]) {
                foreach ($links[$linkType->name] as $record) {
                    $linkType->validateLinkSubject($record);
                    // could/should check also  $record can accept this additional incoming link
                }
            }
            unset($unknownLinks[$linkType->name]);
        }
        if (count($unknownLinks)) {
            foreach ($unknownLinks as $linkName => $record) {
                $issues [] = "Attempt to add an invalid link type: $linkName";
            }
        }
        if (count($issues)) {
            throw new MMValidationException("Validation fail in recordtype.backLinks: " . join(", ", $issues));
        }
    }

    /**
     * @param array $update
     */
    public function updateData(array $update)
    {
        $data = $this->data;
        foreach ($update as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }
        $this->data = $data;
    }

    /**
     * Reads the requested changes to links and checks they are valid.
     * @param array $linkChanges
     */
    public function validateLinkChanges($linkChanges)
    {
        foreach ($linkChanges["fwd"] as $sid => $changes) {
            $linkType = $this->documentRevision->linkType($sid);
            $this->_validateLinkChanges($linkType, $changes, true);
        }
        foreach ($linkChanges["bck"] as $sid => $changes) {
            $linkType = $this->documentRevision->linkType($sid);
            $this->_validateLinkChanges($linkType, $changes, false);
        }
    }


    /**
     * @param LinkType $linkType
     * @param array $linkChanges
     * @param bool $isForwards false if this call is looking at inverse links
     * @throws MMValidationException
     */
    protected function _validateLinkChanges(LinkType $linkType, $linkChanges, $isForwards)
    {
        if ($isForwards) {
            // forward
            $linkedRecords = $this->forwardLinkedRecords($linkType);
            $targetRecordTypeSid = $linkType->range_sid;
            $targetRecordType = $linkType->range();
            $from_min = $linkType->domain_min;
            $from_max = $linkType->domain_max;
            $to_min = $linkType->range_min;
            $to_max = $linkType->range_max;
        } else {
            // backwards
            $linkedRecords = $this->backLinkedRecords($linkType);
            $targetRecordTypeSid = $linkType->domain_sid;
            $targetRecordType = $linkType->domain();
            $from_min = $linkType->range_min;
            $from_max = $linkType->range_max;
            $to_min = $linkType->domain_min;
            $to_max = $linkType->domain_max;
        }

        $resultingLinkedRecords = [];

        // find all the back links of this type both TO and FROM those records, so we can check
        // changes to their cardinality
        $resultingLinkedRecordsInverseRecords = [];
        /** @var Record $linkedRecord */
        foreach ($linkedRecords as $linkedRecord) {
            $resultingLinkedRecords[$linkedRecord->sid] = true;

            $resultingLinkedRecordsInverseRecords[$linkedRecord->sid] = [];
            if ($isForwards) {
                // forward (nb. opposite of direction of main link)
                $inverseLinkedRecords = $linkedRecord->backLinkedRecords($linkType);
            } else {
                // backwards
                $inverseLinkedRecords = $linkedRecord->forwardLinkedRecords($linkType);
            }
            foreach ($inverseLinkedRecords as $inverseLinkedRecord) {
                $resultingLinkedRecordsInverseRecords[$linkedRecord->sid][$inverseLinkedRecord->sid] = true;
            }
        }

        foreach ($linkChanges["remove"] as $remove => $flag) {
            // does this link exist?
            if (
                !array_key_exists($remove, $resultingLinkedRecordsInverseRecords)
                ||
                !array_key_exists($this->sid, $resultingLinkedRecordsInverseRecords[$remove])
            ) {
                // this warning seems a little strict and could cause issues if two people worked
                // on the system at once, but better to have it for now and relax it later if it's an issue
                throw new MMValidationException("Attempting to remove a link which does not exist.");
            }
            unset($resultingLinkedRecordsInverseRecords[$remove][$this->sid]);
            unset($resultingLinkedRecords[$remove]);
        }

        // validate changes to links
        foreach ($linkChanges["add"] as $add => $title) {
            /** @var Record $otherRecord */
            $otherRecord = $this->documentRevision->record($add);
            if ($otherRecord->record_type_sid != $targetRecordTypeSid) {
                throw new MMValidationException("Attempting to link record of wrong type for the link type.");
            }
            $resultingLinkedRecordsInverseRecords[$add][$this->sid] = true;
            $resultingLinkedRecords[$add] = true;
        }

        // check cardinality of main record
        $titleMaker = new TitleMaker();
        $linkTypeName = $titleMaker->title($linkType);
        $targetRecordTypeName = $titleMaker->title($targetRecordType);

        $from_n = count($resultingLinkedRecords);
        if ($from_n < $from_min) {
            throw new MMValidationException("Change would result in $from_n $linkTypeName links; below the minimum of $from_min");
        }
        if (isset($from_max) && $from_n > $from_max) {
            throw new MMValidationException("Change would result in $from_n $linkTypeName links; above the maximum of $from_max");
        }

        // check cardinality of linked records
        foreach ($resultingLinkedRecordsInverseRecords as $linkedRecordSID => $inverseLinks) {
            $to_n = count($inverseLinks);
            $otherRecord = $this->documentRevision->record($linkedRecordSID);
            $otherRecordTitle = $titleMaker->title($otherRecord);
            if ($to_n < $from_min) {
                throw new MMValidationException("Change would result in $to_n $linkTypeName links on linked $targetRecordTypeName record '$otherRecordTitle'; below the minimum of $to_min");
            }
            if (isset($to_max) && $to_n > $to_max) {
                throw new MMValidationException("Change would result in $to_n $linkTypeName links on linked $targetRecordTypeName record '$otherRecordTitle'; above the maximum of $to_max");
            }
        }
        // changes to linktype seem OK
    }

    /**
     * @param LinkType $linkType
     * @return array[Record]
     */
    public function forwardLinkedRecords($linkType)
    {
        $recordIds = DB::table('links')
            ->where("links.document_revision_id", "=", $this->documentRevision->id)
            ->where("links.subject_sid", '=', $this->sid)
            ->where("links.link_type_sid", '=', $linkType->sid)
            ->pluck("links.object_sid");
        $records = [];
        foreach ($recordIds as $recordSid) {
            $record = $this->documentRevision->records()->getQuery()
                ->where('sid', '=', $recordSid)
                ->first();
            if ($record != null) {
                // this is risky, we should give a warning if this happens.
                // Warning, could not find back-linked record X
                $records [] = $record;
            }
        }
        return $records;
    }

    /**
     * @param LinkType $linkType
     * @return array [Record]
     */
    public function backLinkedRecords($linkType)
    {
        $recordIds = DB::table('links')
            ->where("links.document_revision_id", "=", $this->documentRevision->id)
            ->where("links.object_sid", '=', $this->sid)
            ->where("links.link_type_sid", '=', $linkType->sid)
            ->pluck("links.subject_sid");
        $records = [];
        foreach ($recordIds as $recordId) {
            /** @noinspection PhpUndefinedMethodInspection */
            $record = $this->documentRevision->records()->where('sid', '=', $recordId)->first();
            if ($record != null) {
                // this is risky, we should give a warning if this happens.
                // Warning, could not find back-linked record X
                $records [] = $record;
            }
        }
        return $records;
    }

    /**
     * @param array $linkChanges
     */
    public function applyLinkChanges($linkChanges)
    {
        foreach ($linkChanges["fwd"] as $sid => $changes) {
            $linkType = $this->documentRevision->linkType($sid);
            $this->_applyLinkChanges($linkType, $changes, true);
        }
        foreach ($linkChanges["bck"] as $sid => $changes) {
            $linkType = $this->documentRevision->linkType($sid);
            $this->_applyLinkChanges($linkType, $changes, false);
        }
    }


    /**
     * @param LinkType $linkType
     * @param array $linkChanges
     * @param bool $isForwards false if this call is looking at inverse links
     * @throws MMValidationException
     */
    protected function _applyLinkChanges(LinkType $linkType, $linkChanges, $isForwards)
    {
        // MUST call validate before this method. It does no checking!

        // find all records currently in play.
        if ($isForwards) {
            // fowards
            $linkedRecords = $this->forwardLinkedRecords($linkType);
            $targetRecordTypeSid = $linkType->range_sid;
            $links = $this->forwardLinks;
        } else {
            // backwards
            $linkedRecords = $this->backLinkedRecords($linkType);
            $targetRecordTypeSid = $linkType->domain_sid;
            $links = $this->backLinks;
        }
        // find all the links of this type FROM this records, so we can check we don't add one
        // that already exists.
        $alreadyLinkedRecords = [];
        foreach ($linkedRecords as $linkedRecord) {
            $alreadyLinkedRecords[$linkedRecord->sid] = true;
        }
        foreach ($linkChanges["remove"] as $remove => $flag) {
            /** @var Link $link */
            foreach ($links as $link) {
                if ($link->link_type_sid == $linkType->sid) {
                    // link is of right type
                    if (($isForwards && $link->object_sid == $remove)
                        || (!$isForwards && $link->subject_sid = $remove)
                    ) {
                        $link->delete();
                    }
                }
            }
            unset($alreadyLinkedRecords[$remove]);
        }
        foreach ($linkChanges["add"] as $add => $title) {
            // don't add it if it's already there
            if (array_key_exists($add, $alreadyLinkedRecords)) {
                continue;
            }
            $otherRecord = $this->documentRevision->record($add);
            if ($otherRecord->record_type_sid != $targetRecordTypeSid) {
                throw new MMValidationException("Attempting to link record of wrong type for the link type.");
            }

            $link = new Link();
            $link->documentRevision()->associate($linkType->documentRevision);
            $link->link_type_sid = $linkType->sid;
            if ($isForwards) {
                $link->subject_sid = $this->sid;
                $link->object_sid = $add;
            } else {
                $link->subject_sid = $add;
                $link->object_sid = $this->sid;
            }

            $link->save();
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteWithDependencies()
    {
        $errors = $this->reasonsThisCantBeErased();
        if (count($errors)) {
            throw new Exception(join(", ", $errors));
        }
        // find all reasons not to continue.
        // get all records that would be deleted
        $recordsToDelete = $this->getDependentRecords();
        $linksToDelete = new Collection();
        /** @var Record $record */
        foreach ($recordsToDelete as $record) {
            foreach ($record->forwardLinks as $link) {
                $linksToDelete->put($link->sid, $link);
            }
            foreach ($record->backLinks as $link) {
                $linksToDelete->put($link->sid, $link);
            }
        }
        $result = true;
        /** @var Link $link */
        foreach ($linksToDelete as $link) {
            $result &= $link->delete();
        }
        foreach ($recordsToDelete as $record) {
            $result &= $record->delete();
        }
        return $result;
    }

    /**
     * @return array
     */
    public function reasonsThisCantBeErased()
    {
        // find all reasons not to continue.
        // get all records that would be deleted
        $recordsToDelete = $this->getDependentRecords();
        $linksToDelete = new Collection();
        /** @var Record $record */
        foreach ($recordsToDelete as $record) {
            foreach ($record->forwardLinks as $link) {
                $linksToDelete->put($link->sid, $link);
            }
            foreach ($record->backLinks as $link) {
                $linksToDelete->put($link->sid, $link);
            }
        }
        $errors = [];
        return $errors;
    }

    /**
     * Return a collection containing this record and any records dependent on it.
     * @param null|Collection $collected built up over recursion to prevent loops
     * @return Collection
     */
    public function getDependentRecords($collected = null)
    {
        if ($collected != null) {
            if ($collected->contains($this->sid)) {
                return $collected;
            }
        } else {
            $collected = new Collection();
        }
        $collected->put($this->sid, $this);

        foreach ($this->forwardLinks as $link) {
            $linkType = $link->linkType;
            if ($linkType->range_type != 'dependent') {
                continue;
            }
            $link->objectRecord->getDependentRecords($collected);
        }

        foreach ($this->backLinks as $link) {
            $linkType = $link->linkType;
            if ($linkType->domain_type != 'dependent') {
                continue;
            }
            $link->subjectRecord->getDependentRecords($collected);
        }
        return $collected;
    }

}
