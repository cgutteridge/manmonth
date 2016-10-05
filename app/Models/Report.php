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
     * Relation to DocumentRevision
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentRevision()
    {
        return $this->belongsTo('App\Models\DocumentRevision');
    }

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
     * @var array
     */
    private $loadingTypesCache;

    /*
     * Return the list of loadings this report has a target or total for.
     * @return array[string]
     */
    public function loadingTypes()
    {
        if (!isset($this->loadingTypesCache)) {
            $list = [];
            foreach ($this->recordReports() as $recordReport) {
                $list = array_merge($list, $recordReport->getLoadingTypes());
            }
            $this->loadingTypesCache = array_unique($list, SORT_REGULAR);
        }
        return $this->loadingTypesCache;
    }

    /**
     * @var float[]
     */
    protected $maxTargetsCache;

    /*
     * Return the maximum target loading for each loading category.
     * @return array[float]
     */
    public function maxTargets()
    {
        if (!isset($this->maxTargetsCache)) {
            $this->maxTargetsCache = [];
            foreach ($this->loadingTypes() as $loadingType) {
                $this->maxTargetsCache[$loadingType] = 0;
            }
            foreach ($this->recordReports() as $recordReport) {
                foreach ($recordReport->getLoadingTargets() as $loadingType => $total) {
                    if ($total > $this->maxTargetsCache[$loadingType]) {
                        $this->maxTargetsCache[$loadingType] = $total;
                    }
                }
            }
        }
        return $this->maxTargetsCache;
    }

    /**
     * @param string $loadingType
     * @return float
     */
    public function maxTarget($loadingType)
    {
        return $this->maxTargets()[$loadingType];
    }

    /**
     * @var float[]
     */
    private $maxLoadingsCache;

    /*
     * Return the maximum loading for each loading category.
     * @return array[float]
     */
    public function maxLoadings()
    {
        if (!isset($this->maxLoadingsCache)) {
            $cache = [];
            foreach ($this->loadingTypes() as $loadingType) {
                $cache[$loadingType] = 0;
            }
            foreach ($this->recordReports() as $recordReport) {
                foreach ($recordReport->getLoadingTotals() as $loadingType => $total) {
                    if ($total > $cache[$loadingType]) {
                        $cache[$loadingType] = $total;
                    }
                }
            }
            $this->maxLoadingsCache = $cache;
        }
        return $this->maxLoadingsCache;
    }

    /**
     * @param string $loadingType
     * @return float
     */
    public function maxLoading($loadingType)
    {
        return $this->maxLoadings()[$loadingType];
    }


    /**
     * @var float[]
     */
    private $maxLoadingRatiosCache;
    /*
     * Return the maximum loading ratio for each loading category.
     * @return array[float]
     */
    /**
     * @return float[]
     */
    public function maxLoadingRatios()
    {
        if (!isset($this->maxLoadingRatiosCache)) {
            $this->maxLoadingRatiosCache = [];
            foreach ($this->loadingTypes() as $loadingType) {
                $this->maxLoadingRatiosCache[$loadingType] = 0;
            }
            foreach ($this->recordReports() as $recordReport) {
                $totals = $recordReport->getLoadingTotals();
                $targets = $recordReport->getLoadingTargets();
                foreach ($this->loadingTypes() as $loadingType) {
                    if (isset($totals[$loadingType]) && isset($targets[$loadingType])) {
                        $ratio = $totals[$loadingType] / $targets[$loadingType];
                        if ($ratio > $this->maxLoadingRatiosCache[$loadingType]) {
                            $this->maxLoadingRatiosCache[$loadingType] = $ratio;
                        }
                    }
                }
            }
        }
        return $this->maxLoadingRatiosCache;
    }

    /**
     * @param string $loadingType
     * @return float
     */
    public function maxLoadingRatio($loadingType)
    {
        return $this->maxLoadingRatios()[$loadingType];
    }

    /**
     * @var RecordReport[]
     */
    protected $recordReportsCache;

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
        $this->loadingTypesCache = null;
        $this->maxLoadingsCache = null;
        $this->maxLoadingRatiosCache = null;
    }


}





