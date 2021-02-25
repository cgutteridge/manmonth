<?php

namespace App\Models;

use App\Exceptions\MMValidationException;
use App\Exceptions\ReportingException;
use App\Http\TitleMaker;
use App\RecordReport;
use Exception;
use Validator;

/**
 * @property int base_record_type_sid
 * @property DocumentRevision documentRevision
 * @property string name
 * @property int sid
 * @property array data
 */
class ReportType extends DocumentPart
{
    /**
     * @throws MMValidationException
     */
    public function validateName()
    {

        $validator = Validator::make(
            ['name' => $this->name],
            ['name' => 'required|alpha_dash|min:2|max:255']);

        if ($validator->fails()) {
            throw new MMValidationException("Validation fail in reportType.name: " . implode(", ", $validator->errors()->all()));
        }
    }

    /**
     * @throws MMValidationException
     */
    public function validateData()
    {

        $validator = Validator::make(
            $this->data,
            ['title' => 'required']
        );

        if ($validator->fails()) {
            throw new MMValidationException("Validation fail in reportType.data: " . implode(", ", $validator->errors()->all()));
        }
    }

    /**
     * @param array $data
     * @return Rule
     * @throws MMValidationException
     */
    public function createRule($data)
    {

        // all OK, let's make this rule
        $rank = 0;
        /** @noinspection PhpUndefinedMethodInspection */
        $lastrule = $this->rules()->sortByDesc('id')->first();
        if ($lastrule) {
            $rank = $lastrule->rank + 1;
        }

        $rule = new Rule();
        $rule->documentRevision()->associate($this->documentRevision);
        $rule->rank = $rank;
        $rule->report_type_sid = $this->sid;
        $rule->data = $data;

        $rule->validate();
        $rule->save();

        return $rule;
    }

    /**
     * @return array[Rule]
     */
    public function rules()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->rules";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->documentRevision->rules()
                ->where("report_type_sid", $this->sid)
                ->orderBy('rank')
                ->get();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * Run this report type on the current document revision and produce a report object.
     * Doesn't save the object.
     * @return Report
     * @throws MMValidationException
     * @throws ReportingException
     */
    function makeReport()
    {
        $records = $this->baseRecordType()->records();
        $report = new Report();
        $report->documentRevision()->associate($this);
        $report->report_type_sid = $this->sid;

        foreach ($records as $record) {
            try {
                $recordReport = $this->recordReport($record);
            } catch (ReportingException $e) {
                $titleMaker = new TitleMaker();
                throw new ReportingException("In record " . $titleMaker->title($record) . ": " . $e->getMessage(), 0, $e);
            }
            $report->setRecordReport($record->sid, $recordReport);
        }

        return $report;
    }

    /**
     * note that this is NOT a laravel relation
     * @return RecordType
     */
    public function baseRecordType()
    {
        return $this->documentRevision->recordType($this->base_record_type_sid);
    }

    /*
     * for each rule get all possible contexts based on this record and the rule type 'route'
     * then apply the rule.
     * @param Record $record
     * @return RecordReport
     * @throws ReportingException
     */

    function recordReport($record)
    {
        $recordReport = new RecordReport();
        foreach ($this->rules() as $rule) {
            // apply this rule to every possible context based on the route
            /** @var Rule $rule */
            try {
                $rule->apply($record, $recordReport);
            } catch (Exception $e) {
                $titleMaker = new TitleMaker();
                throw new ReportingException("In rule " . $titleMaker->title($rule) . ": " . $e->getMessage(), 0, $e);
            }
        }
        return $recordReport;
    }

    /**
     * Later on this function should probably take a report, but for now
     * reports don't know their report type, so we'll do it like this.
     * I'm not 100% sure that this belongs in the Model, but I need the same function usable
     * from Console & HTTP interfaces to export CSV
     * @return array
     * @throws ReportingException
     * @throws \App\Exceptions\MMValidationException
     */
    public function buildReportData()
    {
        $report = $this->makeReport();
        $categories = [];

        foreach ($this->baseRecordType()->records() as $record) {
            $recordReport = $report->recordReport($record->sid);

            // if this is slow it could just run on the first recordreport?
            $recordCategories = $recordReport->categories();
            foreach ($recordCategories as $category => $opts) {
                foreach ($opts as $param => $value) {
                    $categories[$category][$param] = $value;
                }
            }

            // implicit categories
            $loadings = $recordReport->getLoadings();
            foreach ($loadings as $loadItem) {
                if (array_key_exists("category", $loadItem)) {
                    $categories[$loadItem['category']]['exists'] = true;
                }
            }
        }
        $categoryBase = [];
        foreach ($categories as $category => $options) {
            $categoryBase[$category] = 0;
        }
        $reportData = [
            "categories" => $categories,
            "means" => $report->columnMeans(),
            "totals" => $report->columnTotals(),
            "maxLoading" => $report->maxLoading(),
            "maxTarget" => $report->maxTarget(),
            "maxRatio" => $report->maxLoadingRatio(),
            "rows" => []
        ];
        $unsortedRows = [];
        $titleMaker = new TitleMaker();
        foreach ($this->baseRecordType()->records() as $record) {
            $recordReport = $report->recordReport($record->sid);

            $recordTarget = $recordReport->getLoadingTarget();
            $recordTotal = $recordReport->getLoadingTotal();
            $loadings = $recordReport->getLoadings();
            $categoryTotals = $categoryBase;

            $columns = $recordReport->getColumns();
            if (!array_key_exists("columns", $reportData)) {
                // do this on the first row in the loop.
                $reportData["columns"] = array_keys($columns);
            }

            foreach ($loadings as $loadItem) {
                if (array_key_exists("category", $loadItem)) {
                    $category = $loadItem["category"];
                    $categoryTotals[$category] += $loadItem["load"];
                }
            }
            $sortKey = strtoupper($titleMaker->title($record)) . "#" . $record->sid;
            $unsortedRows[$sortKey] = [
                "record" => $record,
                "target" => $recordTarget,
                "total" => $recordTotal,
                "loadings" => $loadings,
                "units" => $recordReport->getOption("units"),
                "categoryTotals" => $categoryTotals,
                "columns" => $columns,
            ];
        }
        ksort($unsortedRows);
        $reportData["rows"] = array_values($unsortedRows);
        if (!array_key_exists("columns", $reportData)) {
            // if there's no rows then columns could be undefined which could cause bother later
            $reportData['columns'] = [];
        }
        return $reportData;
    }

    /**
     * Return the data from a buildReportData as simple tabular data suitable for CSV export
     * @param string $mode
     * @return void
     * @throws MMValidationException
     * @throws ReportingException
     */
    public function buildTabularReportData($mode = 'summary')
    {
        $reportData = $this->buildReportData($this);

        $headings = [];
        foreach ($reportData['columns'] as $colName) {
            $headings [] = $colName;
        }
        foreach ($reportData["categories"] as $category => $opts) {
            if (!array_key_exists('show_column', $opts) || $opts['show_column']) {
                $headings [] = array_key_exists('label', $opts) ? $opts['label'] : $category;
            }
        }

        $headings [] = "Total";
        $headings [] = "Target";
        $headings [] = "Ratio";
        if ($mode == 'full') {
            $headings [] = "Load";
            $headings [] = "Load type";
            $headings [] = "Load description";
            $headings [] = "Load rule";
        }

        $rows = [];
        $rows [] = $headings;

        foreach ($reportData['rows'] as $reportRow) {
            $row = [];
            foreach ($reportData['columns'] as $colName) {
                $row [] = $reportRow['columns'][$colName];
            }
            foreach ($reportData["categories"] as $category => $opts) {
                if (!array_key_exists('show_column', $opts) || $opts['show_column']) {
                    $row [] = $reportRow['categoryTotals'][$category];
                }
            }
            $row [] = $reportRow['total'];
            $row [] = $reportRow['target'];
            if ($reportRow['target'] > 0) {
                $row [] = sprintf("%.2f", $reportRow['total'] / $reportRow['target']);
            } else {
                $row [] = "";
            }

            if ($mode == 'full' && count($reportRow['loadings'])) {
                foreach ($reportRow['loadings'] as $loading) {
                    $subRow = $row;
                    $subRow [] = $loading['load'];
                    $subRow [] = $loading['category'];
                    $subRow [] = $loading['description'];
                    $subRow [] = $loading['rule_title'];
                    $rows [] = $subRow;
                }
            } else {
                $rows [] = $row;
            }
        }
        return $rows;
    }


}


