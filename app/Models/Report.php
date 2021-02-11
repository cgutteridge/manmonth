<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/09/2016
 * Time: 20:10
 */

namespace App\Models;

use App\RecordReport;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int report_type_id
 * @property ReportType $reportType
 * @property array data
 */
class Report extends DocumentPart
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
    protected $columnTotals;
    protected $columnMeans;
    /**
     * @var float
     */
    private $maxLoading;
    /**
     * @var float
     */
    private $maxLoadingRatio;

    /*************************************
     * RELATIONSHIPS
     *************************************/

    /**
     * @return BelongsTo
     */
    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }


    /*************************************
     * READ FUNCTIONS
     *************************************/

    /**
     * @return RecordReport[]
     */
    public function recordReports()
    {
        if (!isset($this->recordReportsCache)) {
            $this->recordReportsCache = [];
            if ($this->data !== null) {
                foreach ($this->data["records"] as $id => $recordReportData) {
                    $recordReport = new RecordReport($recordReportData);
                    $this->recordReportsCache[$id] = $recordReport;
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
     * @return float[]
     */
    public function columnMeans()
    {
        if (!isset($this->columnMeans)) {
            $this->columnMeans = [];
            // we're treating null as not adding to the mean so counting all not null rows per column
            $rowCounts = [];
            $totals = [];
            foreach ($this->recordReports() as $recordReport) {
                $means = $recordReport->getMeanColumns();
                foreach ($means as $columnName => $value) {
                    if (!array_key_exists($columnName, $rowCounts)) {
                        $rowCounts[$columnName] = 0;
                        $totals[$columnName] = 0;
                    }
                    $rowCounts[$columnName] += 1;
                    $totals[$columnName] += $value;
                }
            }
            foreach ($rowCounts as $columnName => $columnCount) {
                $this->columnMeans[$columnName] = $totals[$columnName] / $columnCount;
            }
        }
        return $this->columnMeans;
    }

    /**
     * @return float[]
     */
    public function columnTotals()
    {
        if (!isset($this->columnTotals)) {
            $this->columnTotals = [];

            foreach ($this->recordReports() as $recordReport) {
                $totals = $recordReport->getTotalColumns();
                foreach ($totals as $columnName => $value) {
                    if (!array_key_exists($columnName, $this->columnTotals)) {
                        $this->columnTotals[$columnName] = 0;
                    }
                    $this->columnTotals[$columnName] += $value;
                }
            }
        }
        return $this->columnTotals;
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

                $ratio = ($target == 0) ? 1 : ($total / $target);
                if ($ratio > $this->maxLoadingRatio) {
                    $this->maxLoadingRatio = $ratio;
                }
            }
        }
        return $this->maxLoadingRatio;
    }

    /**
     * @param integer $id
     * @return RecordReport
     */
    public function recordReport($id)
    {
        return $this->recordReports()[$id];
    }

    /*************************************
     * ACTION FUNCTIONS
     *************************************/

    /**
     * @param int $recordID
     * @param RecordReport $recordReport
     */
    public function setRecordReport($recordID, $recordReport)
    {
        $this->recordReportsCache[$recordID] = $recordReport;
        // force cached values to decache
        $this->maxLoading = null;
        $this->maxLoadingRatio = null;
        $this->columnMeans = null;
        $this->columnTotals = null;
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


}





