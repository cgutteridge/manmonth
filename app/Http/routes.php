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

Route::singularResourceParameters();

Route::get('/', function () {
    return Redirect::to('/documents');
});

Route::group(['middleware' => 'auth'], function () {

    Route::get('documents', 'DocumentController@index');
    Route::get('documents/{document}', 'DocumentController@show');
    Route::get('documents/create', 'DocumentController@create');
    Route::post('documents', 'DocumentController@store');
    Route::get('documents/{document}/latest-published', 'DocumentController@latestPublished');
    Route::get('documents/{document}/latest', 'DocumentController@latest');
    Route::get('documents/{document}/draft', 'DocumentController@draft');
    Route::get('documents/{document}/create-draft', 'DocumentController@makeDraftForm');
    Route::post('documents/{document}/create-draft', 'DocumentController@makeDraft');

    Route::get('revisions/{documentRevision}', 'DocumentRevisionController@show');
    Route::get('revisions/{documentRevision}/scrap', 'DocumentRevisionController@scrapForm');
    Route::post('revisions/{documentRevision}/scrap', 'DocumentRevisionController@scrapAction');
    Route::get('revisions/{documentRevision}/commit', 'DocumentRevisionController@commitForm');
    Route::post('revisions/{documentRevision}/commit', 'DocumentRevisionController@commitAction');
    Route::get('revisions/{documentRevision}/commit-and-publish', 'DocumentRevisionController@commitAndPublishForm');
    Route::post('revisions/{documentRevision}/commit-and-publish', 'DocumentRevisionController@commitAndPublishAction');
    Route::get('revisions/{documentRevision}/commit-and-continue', 'DocumentRevisionController@commitAndContinueForm');
    Route::post('revisions/{documentRevision}/commit-and-continue', 'DocumentRevisionController@commitAndContinueAction');
    Route::get('revisions/{documentRevision}/publish', 'DocumentRevisionController@publishForm');
    Route::post('revisions/{documentRevision}/publish', 'DocumentRevisionController@publishAction');
    Route::get('revisions/{documentRevision}/unpublish', 'DocumentRevisionController@unpublishForm');
    Route::post('revisions/{documentRevision}/unpublish', 'DocumentRevisionController@unpublishAction');

    Route::get('record-types/{recordType}', 'RecordTypeController@show');
    Route::get('record-types/{recordType}/records', 'RecordTypeController@records');
    Route::get('record-types/{recordType}/external-records', 'RecordTypeController@externalRecords');
    Route::get('record-types/{recordType}/external-records-bulk-import', 'RecordTypeController@bulkImportConfirm');
    Route::post('record-types/{recordType}/external-records-bulk-import', 'RecordTypeController@bulkImport');
    Route::get('record-types/{recordType}/create-record', 'RecordTypeController@createRecord');
    Route::post('record-types/{recordType}/create-record', 'RecordTypeController@storeRecord');
    Route::get('record-types/{recordType}/edit', 'RecordTypeController@edit');
    Route::put('record-types/{recordType}', 'RecordTypeController@update');

    Route::get('record-types/{recordType}/fields/{field}/edit', 'RecordTypeFieldController@edit');
    Route::put('record-types/{recordType}/fields/{field}', 'RecordTypeFieldController@update');

    Route::get('records/{record}', 'RecordController@show');
    Route::get('records/{record}/edit', 'RecordController@edit');
    Route::put('records/{record}', 'RecordController@update');
    Route::get('records/{record}/delete', 'RecordController@deleteForm');
    Route::delete('records/{record}', 'RecordController@delete');

    Route::get('report-types/{reportType}', 'ReportTypeController@show');
    /* in future we may support plug-in style export formats. For now it's hard-wired to CSV, but we'll keep
     * the "CSV" in the URL to leave room to expand.
     */
    Route::get('report-types/{reportType}/export/summary/csv', 'ReportTypeController@exportSummaryCsv');
    Route::get('report-types/{reportType}/export/full/csv', 'ReportTypeController@exportFullCsv');


// Route::get('rules/{rule}', 'RuleController@show');

    Route::get('link-types/{linkType}', 'LinkTypeController@show');
    Route::get('link-types/{linkType}/links', 'LinkTypeController@links');
    Route::get('link-types/{linkType}/create-link', 'LinkTypeController@createLink');
    Route::post('link-types/{linkType}/create-link', 'LinkTypeController@storeLink');

    /*
     * routes for direct link removal. I'm not sure if we want to allow this.
    Route::get('link-types/{linkType}/delete-link', 'LinkTypeController@deleteLinkForm');
    Route::post('link-types/{linkType}/delete-link', 'LinkTypeController@deleteLink');
*/

    Route::get('permissions', 'PermissionController@index');

    Route::get('profile', 'UserController@profile');

});

/*
 * Add the default authentication routes.
 */
Route::auth();


/*
 * Turn this on to debug SQL.
 */
if (App::environment('dev')) {
    \DB::listen(function ($sql) {
        Log::info($sql->sql . " [" . join(", ", $sql->bindings) . "]");
    });
}

/* turn this on to count SQL queries */
if (App::environment('dev')) {
    global $dbQueries;
    $dbQueries = 0;
    \DB::listen(function ($sql) {
        global $dbQueries;
        ++$dbQueries;
    });
    register_shutdown_function(function () {
        global $dbQueries;
        Log::info("SQL QUERIES: $dbQueries");
    });
}


