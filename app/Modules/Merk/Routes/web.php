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

Route::group(['prefix' => 'merk','middleware' => 'auth'], function () {
    Route::get('/','MerkController@show')->name('merk');
    Route::get('/show','MerkController@show')->name('merk_show');
    Route::get('/datatables','MerkController@datatables')->name('merk_datatables');
    Route::get('/add','MerkController@add')->name('merk_add');
    Route::post('/post','MerkController@post')->name('merk_post');
    Route::get('/edit/{id}','MerkController@edit')->name('merk_edit');
    Route::post('/update','MerkController@update')->name('merk_update');
    Route::get('/inactive/{id}','MerkController@inactive')->name('merk_dinactive');
});
