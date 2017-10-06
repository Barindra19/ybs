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

Route::group(['prefix' => 'inout','middleware' => 'auth'], function () {
    Route::get('/','InoutController@show')->name('inout');
    Route::get('/show','InoutController@show')->name('inout_show');
    Route::get('/datatables','InoutController@datatables')->name('inout_datatables');
    Route::get('/add','InoutController@add')->name('inout_add');
    Route::post('/post','InoutController@post')->name('inout_post');
    Route::get('/edit/{id}','InoutController@edit')->name('inout_edit');
    Route::post('/update','InoutController@update')->name('inout_update');
    Route::get('/inactive/{id}','InoutController@inactive')->name('inout_inactive');
    Route::get('/activate/{id}','InoutController@activate')->name('inout_activate');});
