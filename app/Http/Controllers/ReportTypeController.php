<?php

namespace App\Http\Controllers;

use App\Exceptions\ReportingException;
use App\Models\ReportType;
use App\Models\Rule;
use Illuminate\View\View;
use Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportTypeController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param ReportType $reportType
     * @return View
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ReportType $reportType)
    {
        $this->authorize('view', $reportType);

        try {
            $reportData = $this->buildReportData($reportType);
        } catch (ReportingException $e) {
            return view('documentRevision.error', [
                "documentRevision" => $reportType->documentRevision,
                "renderErrors" => ["Could not complete report: " . $e->getMessage()],
                'nav' => $this->navigationMaker->documentRevisionNavigation($reportType->documentRevision)
            ]);
        }

        $rulesData = [];
        /** @var Rule $rule */
        foreach ($reportType->rules() as $rule) {
            $ruleData = [];
            $ruleData["rank"] = $rule->rank;
            $ruleData["number"] = $rule->rank + 1;
            $ruleData["title"] = $rule->data["title"];
            $ruleData["action"] = $rule->data["action"];
            $ruleData["action_type"] = $rule->getAction()->action_type;
            $ruleData["params"] = $rule->data["params"];

            if (isset($rule->data["trigger"])) {
                $ruleData["trigger"] = $rule->data["trigger"];
            } else {
                $ruleData["trigger"] = "";
            }
            $ruleData["route"] = [];
            $ruleData["route"] [] = [
                "type" => "recordType",
                "title" => $this->titleMaker->title($reportType->baseRecordType()),
                "codename" => $reportType->name
            ];
            if (!empty($rule->data["route"])) {
                $contexts = $rule->abstractContext();
                $contextOrder = $rule->abstractContextOrder();
                for ($i = 0; $i < sizeof($rule->data["route"]); ++$i) {
                    $ruleData["route"] [] = [
                        "type" => "link",
                        "title" => $rule->data["route"][$i]
                    ];
                    $ruleData["route"] [] = [
                        "type" => "recordType",
                        "title" => $this->titleMaker->title($contexts[$contextOrder[$i]]),
                        "codename" => $contextOrder[$i]
                    ];
                }
            }

            $rulesData [] = $ruleData;
            // action, params, trigger, route
        }

        return view('reportType.show', [
            "reportType" => $reportType,
            "reportData" => $reportData,
            "rulesData" => $rulesData,
            "rulesSections" => [
                ["action_type" => "columns", "label" => "Column Rules"],
                ["action_type" => "targets", "label" => "Loading Target Rules"],
                ["action_type" => "categories", "label" => "Loading Category Rules"],
                ["action_type" => "load", "label" => "Loading Assignment Rules"],
            ],
            "nav" => $this->navigationMaker->documentRevisionNavigation($reportType->documentRevision)
        ]);
    }

    /**
     * @param ReportType $reportType
     * @return View|StreamedResponse
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function exportSummaryCsv(ReportType $reportType)
    {
        $this->authorize('view', $reportType);

        return $this->exportCsv($reportType, 'summary');
    }

    /**
     * @param ReportType $reportType
     * @return View|StreamedResponse
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function exportFullCsv(ReportType $reportType)
    {
        $this->authorize('view', $reportType);

        return $this->exportCsv($reportType, 'full');
    }


    /**
     * @param ReportType $reportType
     * @param string $mode full or summary.
     * @return View|StreamedResponse
     * @throws \App\Exceptions\MMValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function exportCsv(ReportType $reportType, $mode)
    {
        $this->authorize('view', $reportType);

        try {
            $reportData = $this->buildReportData($reportType);
        } catch (ReportingException $e) {
            return view('documentRevision.error', [
                "documentRevision" => $reportType->documentRevision,
                "renderErrors" => [$e->getMessage()],
                'nav' => $this->navigationMaker->documentRevisionNavigation($reportType->documentRevision)
            ]);
        }

        $filename = $reportType->name . ".csv";
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
            'Expires' => '0',
            'Pragma' => 'public'
        ];

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
        //header("Content-type:text/plain");
        //print json_encode($reportData, JSON_PRETTY_PRINT);
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
                $row []= "";
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

        $callback = function () use ($rows) {
            $FH = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Later on this function should probably take a report, but for now
     * reports don't know their report type, so we'll do it like this.
     * @param ReportType $reportType
     * @return array
     * @throws ReportingException
     * @throws \App\Exceptions\MMValidationException
     */
    protected function buildReportData(ReportType $reportType)
    {
        $report = $reportType->makeReport();

        $categories = [];

        foreach ($reportType->baseRecordType()->records() as $record) {
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
        foreach ($reportType->baseRecordType()->records() as $record) {
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
            $sortKey = strtoupper($this->titleMaker->title($record)) . "#" . $record->sid;
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
}
