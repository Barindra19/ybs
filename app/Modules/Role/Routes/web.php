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

Route::group(['prefix' => 'role', 'middleware' => 'auth'], function () {
    Route::get('/','RoleController@show')->name('role');
    Route::get('/show','RoleController@show')->name('role_show');
    Route::get('/datatables','RoleController@datatables')->name('role_datatables');
    Route::get('/add','RoleController@add')->name('role_add');
    Route::post('/post','RoleController@post')->name('role_post');
    Route::get('/edit/{id}','RoleController@edit')->name('role_edit');
    Route::post('/update','RoleController@update')->name('role_update');
    Route::get('/delete/{id}','RoleController@delete')->name('role_delete');
});
