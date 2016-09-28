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

Route::get('/', function () {
    return view('welcome');
});

// create record form is handled by record type
Route::resource('records', 'RecordController', [
    'parameters' => 'singular',
    'except' => ['create', 'index']]);
Route::resource('recordtypes', 'RecordTypeController', [
    'parameters' => 'singular']);
Route::resource('reporttypes', 'ReportTypeController', [
    'parameters' => 'singular']);

Route::resource('documents', 'DocumentController');
Route::resource('revisions', 'DocumentRevisionController');
Route::resource('reports', 'ReportController');

