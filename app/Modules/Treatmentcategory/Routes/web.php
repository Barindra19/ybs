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

Route::group(['prefix' => 'treatmentcategory','middleware' => 'auth'], function () {
    Route::get('/','TreatmentCategoryController@show')->name('treatmentcategory');
    Route::get('/show','TreatmentCategoryController@show')->name('treatmentcategory_show');
    Route::get('/datatables','TreatmentCategoryController@datatables')->name('treatmentcategory_datatables');
    Route::get('/add','TreatmentCategoryController@add')->name('treatmentcategory_add');
    Route::post('/post','TreatmentCategoryController@post')->name('treatmentcategory_post');
    Route::get('/edit/{id}','TreatmentCategoryController@edit')->name('treatmentcategory_edit');
    Route::post('/update','TreatmentCategoryController@update')->name('treatmentcategory_update');
    Route::get('/inactive/{id}','TreatmentCategoryController@inactive')->name('treatmentcategory_inactive');

    Route::post('/searchbyparent','TreatmentCategoryController@searchbyparent')->name('treatmentcategory_searchbyparent');

});
