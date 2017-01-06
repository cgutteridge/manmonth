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
    Route::get('documents/{document}/current', 'DocumentController@current');
    Route::get('documents/{document}/draft', 'DocumentController@draft');

    Route::get('revisions/{documentRevision}', 'DocumentRevisionController@show');
    Route::get('revisions/{documentRevision}/scrap', 'DocumentRevisionController@scrapForm');
    Route::post('revisions/{documentRevision}/scrap', 'DocumentRevisionController@scrapAction');
    Route::get('revisions/{documentRevision}/publish', 'DocumentRevisionController@publishForm');
    Route::post('revisions/{documentRevision}/publish', 'DocumentRevisionController@publishAction');

    Route::get('record-types/{recordType}', 'RecordTypeController@show');
    Route::get('record-types/{recordType}/records', 'RecordTypeController@records');
    Route::get('record-types/{recordType}/external-records', 'RecordTypeController@externalRecords');
    Route::get('record-types/{recordType}/create-record', 'RecordTypeController@createRecord');
    Route::post('record-types/{recordType}/create-record', 'RecordTypeController@storeRecord');

    Route::get('records/{record}', 'RecordController@show');
    Route::get('records/{record}/edit', 'RecordController@edit');
    Route::put('records/{record}', 'RecordController@update');
    Route::get('records/{record}/delete', 'RecordController@deleteForm');
    Route::delete('records/{record}', 'RecordController@delete');

    Route::get('report-types/{reportType}', 'ReportTypeController@show');

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


});

/*
 * Add the default authentication routes.
 */
Route::auth();

