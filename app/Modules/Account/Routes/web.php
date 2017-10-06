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

Route::group(['prefix' => 'account','middleware' => 'auth'], function () {
    Route::get('/','BranchController@show')->name('account');
    Route::get('/show','AccountController@show')->name('account_show');
    Route::get('/datatables','AccountController@datatables')->name('account_datatables');
    Route::get('/add','AccountController@add')->name('account_add');
    Route::post('/post','AccountController@post')->name('account_post');
    Route::get('/edit/{id}','AccountController@edit')->name('account_edit');
    Route::post('/update','AccountController@update')->name('account_update');
    Route::get('/inactive/{id}','AccountController@inactive')->name('account_inactive');
    Route::get('/activate/{id}','AccountController@activate')->name('account_activate');
    Route::post('/get_listbyflow','AccountController@get_listbyflow')->name('account_get_listbyflow');
});
