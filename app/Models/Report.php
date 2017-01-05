<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/09/2016
 * Time: 20:10
 */

namespace App\Models;

use App\RecordReport;

/**
 * @property array data
 */
class Report extends MMModel
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $casts = [
        "data" => "array"
    ];
    /**
     * @var float
     */
    protected $maxTarget;
    /**
     * @var RecordReport[]
     */
    protected $recordReportsCache;
    /**
     * @var float
     */
    private $maxLoading;

    /*
     * Return the maximum target loading
     * @return float
     */
    /**
     * @var float
     */
    private $maxLoadingRatio;

    /**
     * Relation to DocumentRevision
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentRevision()
    {
        return $this->belongsTo('App\Models\DocumentRevision');
    }

    /*
     * Return the maximum loading
     * @return float
     */

    /**
     * Overrides the DocumentPart save method to turn all the document reports
     * into a datastructure and assigning it the the data field which casts
     * it into encoded json.
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $data = ["records" => []];
        foreach ($this->recordReports() as $id => $report) {
            $data["records"][$id] = $report->toData();
        }
        $this->data = $data;
        return parent::save($options);
    }

    /**
     * @return RecordReport[]
     */
    public function recordReports()
    {
        if (!isset($this->recordReportsCache)) {
            $this->recordReportsCache = [];
            if ($this->data !== null) {
                foreach ($this->data["records"] as $sid => $recordReportData) {
                    $recordReport = new RecordReport($recordReportData);
                    $this->recordReportsCache[$sid] = $recordReport;
                }
            }
        }
        return $this->recordReportsCache;
    }

    /*
     * Return the maximum loading ratio
     * @return float
     */

    public function maxTarget()
    {
        if (!isset($this->maxTarget)) {
            $this->maxTarget = 0;
            foreach ($this->recordReports() as $recordReport) {
                $total = $recordReport->getLoadingTarget();
                if ($total > $this->maxTarget) {
                    $this->maxTarget = $total;
                }
            }
        }

        return $this->maxTarget;
    }

    public function maxLoading()
    {
        if (!isset($this->maxLoading)) {
            $this->maxLoading = 0;
            foreach ($this->recordReports() as $recordReport) {
                $total = $recordReport->getLoadingTotal();
                if ($total > $this->maxLoading) {
                    $this->maxLoading = $total;
                }
            }
        }
        return $this->maxLoading;
    }

    /**
     * @return float
     */
    public function maxLoadingRatio()
    {
        if (!isset($this->maxLoadingRatio)) {
            $this->maxLoadingRatio = 0;

            foreach ($this->recordReports() as $recordReport) {
                $total = $recordReport->getLoadingTotal();
                $target = $recordReport->getLoadingTarget();

                $ratio = $total / $target;
                if ($ratio > $this->maxLoadingRatio) {
                    $this->maxLoadingRatio = $ratio;
                }
            }
        }
        return $this->maxLoadingRatio;
    }

    /**
     * @param integer $sid
     * @return RecordReport
     */
    public function recordReport($sid)
    {
        return $this->recordReports()[$sid];
    }

    /**
     * @param int $recordSid
     * @param RecordReport $recordReport
     */
    public function setRecordReport($recordSid, $recordReport)
    {
        $this->recordReportsCache[$recordSid] = $recordReport;
        // force cached values to decache
        $this->maxLoading = null;
        $this->maxLoadingRatio = null;
    }


}





