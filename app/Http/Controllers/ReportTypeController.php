<?php

namespace App\Http\Controllers;

use App\Exceptions\ReportingException;
use App\Models\ReportType;
use Illuminate\View\View;

class ReportTypeController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param ReportType $reportType
     * @return View
     */
    public function show(ReportType $reportType)
    {
        $this->authorize('view', $reportType);

        $renderErrors = [];
        $reportData = [];

        try {
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
            foreach ($reportType->baseRecordType()->records() as $record) {
                $recordReport = $report->recordReport($record->sid);
                $recordTarget = $recordReport->getLoadingTarget();
                $recordTotal = $recordReport->getLoadingTotal();
                $loadings = $recordReport->getLoadings();
                $categoryTotals = $categoryBase;
                foreach ($loadings as $loadItem) {
                    if (array_key_exists("category", $loadItem)) {
                        $category = $loadItem["category"];
                        $categoryTotals[$category] += $loadItem["load"];
                    }
                }
                $reportData["rows"][] = [
                    "record" => $record,
                    "recordReport" => $recordReport,
                    "target" => $recordTarget,
                    "total" => $recordTotal,
                    "loadings" => $loadings,
                    "units" => $recordReport->getOption("units"),
                    "categoryTotals" => $categoryTotals
                ];
            }
        } catch (ReportingException $e) {
            $renderErrors [] = $e->getMessage();
        }

        return view('reportType.show', [
            "reportType" => $reportType,
            "reportData" => $reportData,
            "renderErrors" => $renderErrors,
            "nav" => $this->navigationMaker->documentRevisionNavigation($reportType->documentRevision)
        ]);
    }

}
