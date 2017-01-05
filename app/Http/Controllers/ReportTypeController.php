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

            $maxLoading = $report->maxLoading();
            $maxTarget = $report->maxTarget();
            $maxRatio = $report->maxLoadingRatio();
            if ($maxRatio == 0) {
                $maxRatio = 1;
            }
            $categoryBase = [];
            foreach ($reportType->baseRecordType()->records as $record) {
                $recordReport = $report->recordReport($record->sid);
                $loadings = $recordReport->getLoadings();
                foreach ($loadings as $loadItem) {
                    if (array_key_exists("category", $loadItem)) {
                        $categoryBase[$loadItem["category"]] = 0;
                    }
                }
            }
            $categories = array_keys($categoryBase);
            sort($categories);
            $reportData = [
                "categories" => $categories,
                "views" => [
                    "absolute" => [
                        "title" => "Absolute Scale",
                        "tabTitle" => "Absolute",
                        "first" => true,
                        "rows" => []
                    ],
                    "target" => [
                        "title" => "Scaled relative to target loading",
                        "tabTitle" => "Relative to target",
                        "rows" => []
                    ],
                    "alloc" => [
                        "title" => "Scaled relative to allocated loading", "button" => "Relative to target",
                        "tabTitle" => "Relative to allocation",
                        "rows" => []
                    ]]
            ];
            foreach ($reportType->baseRecordType()->records as $record) {
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
                $reportData["views"]["absolute"]["rows"][] = [
                    "showFree" => true,
                    "showTarget" => true,
                    "record" => $record,
                    "recordReport" => $recordReport,
                    "categoryTotals" => $categoryTotals,
                    "units" => $recordReport->getOption("units"),
                    "loadings" => $loadings,
                    "scale" =>
                        max($maxLoading, $maxTarget) == 0 ?
                            1 :
                            1 / max($maxLoading, $maxTarget),
                    "target" => $recordTarget,
                    "total" => $recordTotal
                ];
                $reportData["views"]["target"]["rows"][] = [
                    "showFree" => true,
                    "showTarget" => true,
                    "record" => $record,
                    "recordReport" => $recordReport,
                    "categoryTotals" => $categoryTotals,
                    "units" => $recordReport->getOption("units"),
                    "loadings" => $loadings,
                    "scale" =>
                        max($maxLoading, $maxTarget) == 0 ?
                            1 :
                            1 / $recordTarget / max(1, $maxRatio),
                    "target" => $recordTarget,
                    "total" => $recordTotal
                ];
                $reportData["views"]["alloc"]["rows"][] = [
                    "showFree" => false,
                    "showTarget" => false,
                    "record" => $record,
                    "recordReport" => $recordReport,
                    "categoryTotals" => $categoryTotals,
                    "units" => $recordReport->getOption("units"),
                    "loadings" => $loadings,
                    "scale" => $recordTotal == 0 ? 1 : 1 / $recordTotal,
                    "target" => $recordTarget,
                    "total" => $recordTotal];
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
