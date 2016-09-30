<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 30/09/2016
 * Time: 12:53
 */

namespace App\Http\Controllers;

/* return URLs for models */
use Exception;
use Illuminate\Database\Eloquent\Model;

class LinkMaker
{
    /**
     * @param Model $model
     * @return string
     * @throws Exception
     */
    public function link(Model $model)
    {
        $link = null;
        if (is_a($model, 'App\Models\Document')) {
            $link = "/documents/" . $model->id;
        }
        if (is_a($model, 'App\Models\DocumentRevision')) {
            $link = "/revisions/" . $model->id;
        }
        if (is_a($model, 'App\Models\RecordType')) {
            $link = "/record-types/" . $model->id;
        }
        if (is_a($model, 'App\Models\Record')) {
            $link = "/records/" . $model->id;
        }
        if (is_a($model, 'App\Models\LinkType')) {
            $link = "/link-types/" . $model->id;
        }
        if (is_a($model, 'App\Models\Link')) {
            $link = "/links/" . $model->id;
        }
        if (is_a($model, 'App\Models\ReportType')) {
            $link = "/report-types/" . $model->id;
        }
        if (is_a($model, 'App\Models\Report')) {
            $link = "/reports/" . $model->id;
        }
        if (is_a($model, 'App\Models\Rule')) {
            $link = "/rules/" . $model->id;
        }
        if ($link == null) {
            throw new Exception("Could not make a link for model of class " . get_class($model));
        }
        return $link;
    }

    /**
     * @param Model $model
     * @return string
     * @throws Exception
     */
    public function edit(Model $model)
    {
        return $this->link($model) . "/edit";
    }
}