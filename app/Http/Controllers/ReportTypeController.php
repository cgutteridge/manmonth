<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\Request;

class ReportTypeController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public function show(ReportType $report_type)
    {
        return view('reportType.show', [
            "reportType" => $report_type,
            "report" => $report_type->makeReport(),
            "nav" => $this->navigationMaker->documentRevisionNavigation($report_type->documentRevision)
        ]);
    }

}
