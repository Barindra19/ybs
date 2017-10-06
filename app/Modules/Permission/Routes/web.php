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

Route::group(['prefix' => 'permission','middleware' => 'auth'], function () {
    Route::get('/','PermissionController@index')->name('permission');
    Route::get('/show','PermissionController@show')->name('permission_show');
    Route::get('/datatables','PermissionController@datatables')->name('permission_datatables');
    Route::get('/add','PermissionController@add')->name('permission_add');
    Route::post('/post','PermissionController@post')->name('permission_post');
    Route::get('/edit/{id}','PermissionController@edit')->name('permission_edit');
    Route::post('/update','PermissionController@update')->name('permission_update');
    Route::get('/delete/{id}','PermissionController@delete')->name('permission_delete');
});
