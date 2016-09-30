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
    return view('welcome');
});
Route::resource('documents', 'DocumentController',
    ['only' => ['index', 'show', 'create', 'store']]
);
Route::get('documents/{document}/current', 'DocumentController@current');
Route::get('documents/{document}/draft', 'DocumentController@draft');

Route::resource('revisions', 'DocumentRevisionController',
    ['only' => ['show']]
);

Route::resource('records', 'RecordController',
    ['only' => ['show', 'store', 'edit', 'update']]
);

Route::resource('record-types', 'RecordTypeController',
    ['only' => ['show']]
);

Route::resource('report-types', 'ReportTypeController',
    ['only' => ['show']]
);

Route::resource('reports', 'ReportController',
    ['only' => ['show']]
);

Route::resource('link-types', 'LinkTypeController',
    ['only' => ['show']]
);

