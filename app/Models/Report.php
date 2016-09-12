<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/09/2016
 * Time: 20:10
 */

namespace App\Models;

use App\RecordReport;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array data
 */
class Report extends Model
{
    public $timestamps = false;

    protected $casts = [
        "data"=>"array"
    ];

    public function documentRevision()
    {
        return $this->belongsTo('App\Models\DocumentRevision');
    }

    public function save(array $options = [])
    {
        $data = [ "records"=>[] ];
        foreach( $this->recordReports() as $id=>$report ) {
            $data["records"][$id] = $report->toData();
        }
        $this->data = $data;
        return parent::save($options);
    }

    private $loadingTypesCache=null;
    /*
     * Return the list of loadings this report has a target or total for.
     * @return array[string]
     */
    public function loadingTypes()
    {
        if ($this->loadingTypesCache==null) {
            $list = [];
            foreach ($this->recordReports() as $recordReport) {
                $list = array_merge($list, $recordReport->getLoadingTypes());
            }
            $this->loadingTypesCache = array_unique($list, SORT_REGULAR);
        }
        return $this->loadingTypesCache;
    }

    protected $maxTargetsCache=null;
    /*
     * Return the maximum target loading for each loading category.
     * @return array[float]
     */
    public function maxTargets()
    {
        if($this->maxTargetsCache == null) {
            $this->maxTargetsCache = [];
            foreach($this->loadingTypes() as $loadingType ) {
                $this->maxTargetsCache[$loadingType]=0;
            }
            foreach ($this->recordReports() as $recordReport) {
                foreach( $recordReport->getLoadingTargets() as $loadingType=>$total ) {
                    if( $total > $this->maxTargetsCache[$loadingType]) {
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
    public function maxTarget($loadingType) {
        return $this->maxTargets()[$loadingType];
    }

    private $maxLoadingsCache=null;
    /*
     * Return the maximum loading for each loading category.
     * @return array[float]
     */
    public function maxLoadings()
    {
        if ($this->maxLoadingsCache==null) {
            $cache = [];
            foreach($this->loadingTypes() as $loadingType ) {
                $cache[$loadingType]=0;
            }
            foreach ($this->recordReports() as $recordReport) {
                foreach( $recordReport->getLoadingTotals() as $loadingType=>$total ) {
                    if( $total > $cache[$loadingType]) {
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
    public function maxLoading($loadingType) {
        return $this->maxLoadings()[$loadingType];
    }


    private $maxLoadingRatiosCache = null;
    /*
     * Return the maximum loading ratio for each loading category.
     * @return array[float]
     */
    public function maxLoadingRatios()
    {
        if ($this->maxLoadingRatiosCache == null) {
            $this->maxLoadingRatiosCache = [];
            foreach($this->loadingTypes() as $loadingType ) {
                $this->maxLoadingRatiosCache[$loadingType]=0;
            }
            foreach ($this->recordReports() as $recordReport) {
                $totals = $recordReport->getLoadingTotals();
                $targets = $recordReport->getLoadingTargets();
                foreach($this->loadingTypes() as $loadingType ) {
                    if( isset($totals[$loadingType]) && isset($targets[$loadingType])) {
                        $ratio = $totals[$loadingType] / $targets[$loadingType];
                        if( $ratio > $this->maxLoadingRatiosCache[$loadingType]) {
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
    public function maxLoadingRatio($loadingType) {
        return $this->maxLoadingRatios()[$loadingType];
    }

    protected $recordReportsCache = null;

    /**
     * @return array[RecordReport]
     */
    public function recordReports() {
        if( $this->recordReportsCache == null ) {
            $this->recordReportsCache = [];
            if( $this->data !== null ) {
                foreach ($this->data["records"] as $sid => $recordReportData) {
                    $recordReport = new RecordReport($recordReportData);
                    $this->recordReportsCache[$sid] = $recordReport;
                }
            }
        }
        return $this->recordReportsCache;
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





