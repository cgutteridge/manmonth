<?php

namespace App\Http\Controllers;

use App\Exceptions\ReportingException;
use App\Models\ReportType;
use Illuminate\View\View;

class ReportTypeController extends DocumentPartController
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

            foreach ($report->loadingTypes() as $loadingType) {
                $maxLoading = $report->maxLoading($loadingType);
                $maxTarget = $report->maxTarget($loadingType);
                $maxRatio = $report->maxLoadingRatio($loadingType);
                if ($maxRatio == 0) {
                    $maxRatio = 1;
                }

                $reportData[$loadingType] = [
                    "title" => $loadingType, // TODO better title
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
                    $recordTarget = $recordReport->getLoadingTarget($loadingType);
                    $recordTotal = $recordReport->getLoadingTotal($loadingType);
                    $reportData[$loadingType]["views"]["absolute"]["rows"][] = [
                        "showFree" => true,
                        "showTarget" => true,
                        "record" => $record,
                        "recordReport" => $recordReport,
                        "units" => $recordReport->getLoadingOption($loadingType, "units"),
                        "loadings" => $recordReport->getLoadings($loadingType),
                        "scale" =>
                            max($maxLoading, $maxTarget) == 0 ?
                                1 :
                                1 / max($maxLoading, $maxTarget),
                        "target" => $recordTarget,
                        "total" => $recordTotal
                    ];
                    $reportData[$loadingType]["views"]["target"]["rows"][] = [
                        "showFree" => true,
                        "showTarget" => true,
                        "record" => $record,
                        "recordReport" => $recordReport,
                        "units" => $recordReport->getLoadingOption($loadingType, "units"),
                        "loadings" => $recordReport->getLoadings($loadingType),
                        "scale" =>
                            $recordTarget * $maxRatio == 0 ?
                                1 :
                                1 / ($recordTarget * $maxRatio),
                        "target" => $recordTarget,
                        "total" => $recordTotal
                    ];
                    $reportData[$loadingType]["views"]["alloc"]["rows"][] = [
                        "showFree" => false,
                        "showTarget" => false,
                        "record" => $record,
                        "recordReport" => $recordReport,
                        "units" => $recordReport->getLoadingOption($loadingType, "units"),
                        "loadings" => $recordReport->getLoadings($loadingType),
                        "scale" => $recordTotal == 0 ? 1 : 1 / $recordTotal,
                        "target" => $recordTarget,
                        "total" => $recordTotal];
                }
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
