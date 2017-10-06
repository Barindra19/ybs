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


Route::group(['prefix' => 'treatment','middleware' => 'auth'], function () {
    Route::get('/','TreatmentController@show')->name('treatment');
    Route::get('/show','TreatmentController@show')->name('treatment_show');
    Route::get('/datatables','TreatmentController@datatables')->name('treatment_datatables');
    Route::get('/add','TreatmentController@add')->name('treatment_add');
    Route::post('/post','TreatmentController@post')->name('treatment_post');
    Route::get('/edit/{id}','TreatmentController@edit')->name('treatment_edit');
    Route::post('/update','TreatmentController@update')->name('treatment_update');
    Route::get('/inactive/{id}','TreatmentController@inactive')->name('treatment_inactive');
});
