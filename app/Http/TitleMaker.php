<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 30/09/2016
 * Time: 12:53
 */

namespace App\Http;

/* return URLs for models */
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
     * @param MMModel $model
     * @return string
     * @throws Exception
     * @throws DataStructValidationException
     */
    public function title(MMModel $model)
    {
        $title = null;
        if (is_a($model, Document::class)) {
            /** @var Document $model */
            $title = $model->name;
        }
        if (is_a($model, DocumentRevision::class)) {
            /** @var DocumentRevision $model */
            $title = $model->document->title() . " rev #" . $model->id;
        }
        if (is_a($model, RecordType::class)) {
            /** @var RecordType $model */
            if (isset($model->label) && trim($model->label) != "") {
                return $model->label;
            }
            $title = $model->name;
        }
        if (is_a($model, Record::class)) {
            /** @var Record $model */
            $script = $model->recordType->titleScript();
            if (!$script) {
                $title = $model->recordType->name . "#" . $model->sid;
            } else {
                if ($script->type() != "string") {
                    throw new DataStructValidationException("If a record type has a title it should be an MMScript which returns a string. This returned a " . $script->type());
                }
                try {
                    $result = $script->execute(["record" => $model]);
                    $title = $result->value;
                } catch (MMScriptRuntimeException $e) {
                    $title = "[* mmscript failed: " . $e->getMessage() . " *]";
                }
            }
        }
        if (is_a($model, LinkType::class)) {
            /** @var LinkType $model */
            if (isset($model->label) && trim($model->label) != "") {
                $title = $model->label;
            } else {
                $title = $model->name;
            }
        }
        if (is_a($model, Link::class)) {
            /** @var Link $model */
            $title = "Link #" . $model->sid;
        }
        if (is_a($model, ReportType::class)) {
            /** @var ReportType $model */
            $title = $model->name;
        }
        if (is_a($model, Report::class)) {
            /** @var Report $model */
            $title = "Report #" . $model->id;
        }
        if (is_a($model, Rule::class)) {
            /** @var Rule $model */
            $title = "Rule #" . $model->sid;
        }
        if ($title == null) {
            throw new Exception("Could not make a title for model of class " . get_class($model));
        }

        return $title;
    }

}