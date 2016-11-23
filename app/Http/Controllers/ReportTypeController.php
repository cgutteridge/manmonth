<?php

namespace App\Http\Controllers;

use App\Exceptions\ReportingException;
use App\Models\ReportType;
use Illuminate\Http\Response;

class ReportTypeController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param ReportType $reportType
     * @return Response
     */
    public function show(ReportType $reportType)
    {
        $errors = [];
        $reportData = [];

        try {
            $report = $reportType->makeReport();

            foreach ($report->loadingTypes() as $loadingType) {
                $maxLoading = $report->maxLoading($loadingType);
                $maxTarget = $report->maxTarget($loadingType);
                $maxRatio = $report->maxLoadingRatio($loadingType);

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
                        "scale" => $recordTotal == 0 ? 1 : 1 / $recordTotal,
                        "target" => $recordTarget,
                        "total" => $recordTotal];
                }
            }
        } catch (ReportingException $e) {
            $errors [] = $e->getMessage();
        }

        return view('reportType.show', [
            "reportType" => $reportType,
            "reportData" => $reportData,
            "nav" => $this->navigationMaker->documentRevisionNavigation($reportType->documentRevision)
        ])->withErrors($errors);
    }

}
