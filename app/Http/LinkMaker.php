<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 30/09/2016
 * Time: 12:53
 */

namespace App\Http;

/* return URLs for models */
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

class LinkMaker
{
    /**
     * Return the URL for a model, or an action on a model
     * @param MMModel|Field $model
     * @param string|null $action
     * @param array $params CGI parameters
     * @return string
     * @throws Exception
     */
    public function url($model, $action = null, $params = [])
    {
        $link = null;
        if (is_a($model, Document::class)) {
            $link = "/documents/" . $model->id;
        }
        if (is_a($model, DocumentRevision::class)) {
            $link = "/revisions/" . $model->id;
        }
        if (is_a($model, RecordType::class)) {
            $link = "/record-types/" . $model->id;
        }
        if (is_a($model, Record::class)) {
            $link = "/records/" . $model->id;
        }
        if (is_a($model, LinkType::class)) {
            $link = "/link-types/" . $model->id;
        }
        if (is_a($model, Link::class)) {
            $link = "/links/" . $model->id;
        }
        if (is_a($model, ReportType::class)) {
            $link = "/report-types/" . $model->id;
        }
        if (is_a($model, Report::class)) {
            $link = "/reports/" . $model->id;
        }
        if (is_a($model, Rule::class)) {
            $link = "/rules/" . $model->id;
        }
        if (is_a($model, Field::class)) {
            if (!isset($model->recordType)) {
                throw new Exception("Can't create a URL for a field that has no associated recordType.");
            }
            $link = $this->url($model->recordType) . "/fields/" . $model->data["name"];
        }

        if ($link == null) {
            throw new Exception("Could not make a link for model of class " . get_class($model));
        }
        if (isset($action)) {
            $link .= "/$action";
        }
        $link .= $this->params($params);

        return $link;
    }

    public function params($params = [])
    {
        if (count($params) == 0) {
            return "";
        }

        $list = array();
        foreach ($params as $key => $value) {
            $list [] = $key . "=" . urlencode($value);
        }
        return "?" . join("&", $list);
    }

    public function renderLink($link)
    {

    }
}