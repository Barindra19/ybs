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

Route::group(['prefix' => 'emailtemplate'], function () {
    Route::get('/','EmailTemplateController@show')->name('emailtemplate');
    Route::get('/show','EmailTemplateController@show')->name('emailtemplate_show');
    Route::get('/datatables','EmailTemplateController@datatables')->name('emailtemplate_datatables');
    Route::get('/add','EmailTemplateController@add')->name('emailtemplate_add');
    Route::post('/post','EmailTemplateController@post')->name('emailtemplate_post');
    Route::get('/edit/{id}','EmailTemplateController@edit')->name('emailtemplate_edit');
    Route::post('/update','EmailTemplateController@update')->name('emailtemplate_update');
    Route::get('/delete/{id}','EmailTemplateController@delete')->name('emailtemplate_delete');
});
