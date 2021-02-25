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
            $reportData = $reportType->buildReportData();
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
                "codename" => $reportType->baseRecordType()->name
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
            $rows = $reportType->buildTabularReportData($mode);
        } catch (ReportingException $e) {
            return view('documentRevision.error', [
                "documentRevision" => $reportType->documentRevision,
                "renderErrors" => [$e->getMessage()],
                'nav' => $this->navigationMaker->documentRevisionNavigation($reportType->documentRevision)
            ]);
        }

        $callback = function () use ($rows) {
            $FH = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        $filename = $reportType->name . ".csv";
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
            'Expires' => '0',
            'Pragma' => 'public'
        ];

        return Response::stream($callback, 200, $headers);
    }

}
