<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\Models\DocumentRevision;
use App\Models\Report;
use App\Models\ReportType;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('report/{id}', function ($id) {
    $report = Report::find( $id );
    return view('report', ["report"=>$report] );
});

Route::get('revision/{id}', function ($id) {
    $documentRevision = DocumentRevision::find( $id );
    return view('documentRevision', ["documentRevision"=>$documentRevision] );
});

Route::get('report-type/{id}', function ($id) {
    $reportType = ReportType::find( $id );
    $report = $reportType->makeReport();
    return view('reportType', ["reportType"=>$reportType, "report"=>$report] );
});

