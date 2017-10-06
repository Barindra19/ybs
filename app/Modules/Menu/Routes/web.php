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

Route::group(['prefix' => 'menu','middleware' => 'auth'], function () {
    Route::get('/','MenuController@show')->name('menu');
    Route::get('/show','MenuController@show')->name('menu_show');
    Route::get('/datatables','MenuController@datatables')->name('menu_datatables');
    Route::get('/add','MenuController@add')->name('menu_add');
    Route::post('/post','MenuController@post')->name('menu_post');
    Route::get('/edit/{id}','MenuController@edit')->name('menu_edit');
    Route::post('/update','MenuController@update')->name('menu_update');
    Route::get('/delete/{id}','MenuController@delete')->name('menu_delete');

});
