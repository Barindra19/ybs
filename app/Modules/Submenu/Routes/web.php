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

Route::group(['prefix' => 'submenu','middleware' => 'auth'], function () {
    Route::get('/','SubMenuController@show')->name('submenu');
    Route::get('/show','SubMenuController@show')->name('submenu_show');
    Route::get('/datatables','SubMenuController@datatables')->name('submenu_datatables');
    Route::get('/add','SubMenuController@add')->name('submenu_add');
    Route::post('/post','SubMenuController@post')->name('submenu_post');
    Route::get('/edit/{id}','SubMenuController@edit')->name('submenu_edit');
    Route::post('/update','SubMenuController@update')->name('submenu_update');
    Route::get('/delete/{id}','SubMenuController@delete')->name('submenu_delete');
});
