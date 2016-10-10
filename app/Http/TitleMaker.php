<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 30/09/2016
 * Time: 12:53
 */

namespace App\Http;

/* return URLs for models */
use App\Exceptions\DataStructValidationException;
use App\Exceptions\MMScriptRuntimeException;
use App\Fields\Field;
use App\Models\Document;
use App\Models\DocumentRevision;
use App\Models\Link;
use App\Models\LinkType;
use App\Models\MMModel;
use App\Models\Record;
use App\Models\RecordType;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\Rule;
use Exception;

class TitleMaker
{
    /**
     * @param MMModel|Field $item
     * @return string
     * @throws Exception
     * @throws DataStructValidationException
     */
    public function title($item)
    {
        $title = null;
        if (is_a($item, Document::class)) {
            /** @var Document $item */
            $title = $item->name;
        }
        if (is_a($item, DocumentRevision::class)) {
            /** @var DocumentRevision $item */
            $title = $item->document->title() . " rev #" . $item->id;
        }
        if (is_a($item, RecordType::class)) {
            /** @var RecordType $item */
            if (isset($item->label) && trim($item->label) != "") {
                return $item->label;
            }
            $title = $item->name;
        }
        if (is_a($item, Record::class)) {
            /** @var Record $item */
            $script = $item->recordType->titleScript();
            if (!$script) {
                $title = $item->recordType->name . "#" . $item->sid;
            } else {
                if ($script->type() != "string") {
                    throw new DataStructValidationException("If a record type has a title it should be an MMScript which returns a string. This returned a " . $script->type());
                }
                try {
                    $result = $script->execute(["record" => $item]);
                    $title = $result->value;
                } catch (MMScriptRuntimeException $e) {
                    $title = "[* mmscript failed: " . $e->getMessage() . " *]";
                }
            }
        }
        if (is_a($item, LinkType::class)) {
            /** @var LinkType $item */
            if (isset($item->label) && trim($item->label) != "") {
                $title = $item->label;
            } else {
                $title = $item->name;
            }
        }
        if (is_a($item, Link::class)) {
            /** @var Link $item */
            $title = "Link #" . $item->sid;
        }
        if (is_a($item, ReportType::class)) {
            /** @var ReportType $item */
            $title = $item->name;
        }
        if (is_a($item, Report::class)) {
            /** @var Report $item */
            $title = "Report #" . $item->id;
        }
        if (is_a($item, Rule::class)) {
            /** @var Rule $item */
            $title = "Rule #" . $item->sid;
        }

        if (is_a($item, Field::class)) {
            if (array_key_exists("label", $item->data)) {
                return $item->data["label"];
            }
            return $item->data["name"];
        }

        if ($title == null) {
            throw new Exception("Could not make a title for model of class " . get_class($item));
        }
        return $title;
    }

}