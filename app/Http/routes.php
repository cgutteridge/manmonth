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

Route::get('documents', 'DocumentController@index');
Route::get('documents/{document}', 'DocumentController@show');
Route::get('documents/create', 'DocumentController@create');
Route::post('documents', 'DocumentController@store');
Route::get('documents/{document}/current', 'DocumentController@current');
Route::get('documents/{document}/draft', 'DocumentController@draft');
// TODO future features: retire, scrap, unscrap, clone, edit, update

Route::get('revisions/{documentRevision}', 'DocumentRevisionController@show');
Route::get('revisions/{documentRevision}/scrap', 'DocumentRevisionController@scrapForm');
Route::post('revisions/{documentRevision}/scrap', 'DocumentRevisionController@scrapAction');
Route::get('revisions/{documentRevision}/publish', 'DocumentRevisionController@publishForm');
Route::post('revisions/{documentRevision}/publish', 'DocumentRevisionController@publishAction');
// TODO future features: unscrap, edit, update, create recordType form/action, create linkType form/action, create reportType form/action, view all reports, enable/disable schema changes

Route::get('record-types/{recordType}', 'RecordTypeController@show');
Route::get('record-types/{recordType}/records', 'RecordTypeController@records');
Route::get('record-types/{recordType}/create-record', 'RecordTypeController@createRecord');
Route::post('record-types/{recordType}/create-record', 'RecordTypeController@storeRecord');
// TODO future features: destroy form/action, edit form/action

Route::get('records/{record}', 'RecordController@show');
Route::get('records/{record}/edit', 'RecordController@edit');
Route::put('records/{record}', 'RecordController@update');
Route::get('records/{record}/delete', 'RecordController@deleteForm');
Route::delete('records/{record}', 'RecordController@delete');

// TODO future: destroy form/action

Route::get('report-types/{reportType}', 'ReportTypeController@show');
// TODO future: list reports, create report, edit form/action, destroy form/action, create rule form/action

// Route::get('rules/{rule}', 'RuleController@show');
// TODO future: show, edit form/action, destroy form/action

Route::get('link-types/{linkType}', 'LinkTypeController@show');
Route::get('link-types/{linkType}/links', 'LinkTypeController@links');
Route::get('link-types/{linkType}/create-link', 'LinkTypeController@createLink');
Route::post('link-types/{linkType}/create-link', 'LinkTypeController@storeLink');
// TODO should these next two not belong to the link?
Route::get('link-types/{linkType}/delete-link', 'LinkTypeController@deleteLinkForm');
Route::post('link-types/{linkType}/delete-link', 'LinkTypeController@deleteLink');

// TODO Link
// TODO Report

// TODO 404 handling