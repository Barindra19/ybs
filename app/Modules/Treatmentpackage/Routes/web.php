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

Route::group(['prefix' => 'treatmentpackage','middleware' => 'auth'], function () {
    Route::get('/','TreatmentPackageController@show')->name('treatmentpackage');
    Route::get('/show','TreatmentPackageController@show')->name('treatmentpackage_show');
    Route::get('/datatables','TreatmentPackageController@datatables')->name('treatmentpackage_datatables');
    Route::get('/add','TreatmentPackageController@add')->name('treatmentpackage_add');
    Route::post('/post','TreatmentPackageController@post')->name('treatmentpackage_post');
    Route::get('/edit/{id}','TreatmentPackageController@edit')->name('treatmentpackage_edit');
    Route::post('/update','TreatmentPackageController@update')->name('treatmentpackage_update');
    Route::get('/inactive/{id}','TreatmentPackageController@inactive')->name('treatmentpackage_inactive');

    Route::post('/searchbycategory','TreatmentPackageController@searchbycategory')->name('treatmentcategory_searchbycategory');
    Route::post('/searchbytreatment','TreatmentPackageController@searchbytreatment')->name('treatmentcategory_searchbytreatment');
    Route::post('/getdetailpackage','TreatmentPackageController@getdetailpackage')->name('treatmentcategory_getdetailpackage');


});
