<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['prefix' => 'archive','middleware' => 'auth'], function () {
    Route::get('/','ArchiveController@show')->name('archive');
    Route::get('/show','ArchiveController@show')->name('archive_show');
    Route::get('/datatables','ArchiveController@datatables')->name('archive_datatables');
    Route::get('/take_items/{archive_id}','ArchiveController@take_items')->name('archive_take_items');

    Route::post('/info','ArchiveController@info')->name('archive_info');
});
