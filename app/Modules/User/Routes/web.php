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

Route::group(['prefix' => 'user','middleware' => 'auth'], function () {
    Route::get('/','UserController@show')->name('user');
    Route::get('/show','UserController@show')->name('user_show');
    Route::get('/datatables','UserController@datatables')->name('user_datatables');
    Route::get('/add','UserController@add')->name('user_add');
    Route::post('/post','UserController@post')->name('user_post');
    Route::get('/edit/{id}','UserController@edit')->name('user_edit');
    Route::post('/update','UserController@update')->name('user_update');
    Route::get('/inactive/{id}','UserController@inactive')->name('user_inactive');
    Route::get('/changed_password/{id}','UserController@changed_password')->name('user_changed_password');
    Route::post('/changed_password_act','UserController@changed_password_act')->name('user_changed_password_act');

    Route::get('/profile_changed_password','ProfileController@showChangedInForm')->name('profile_changed_password');
    Route::post('/profile_changed_password_act','ProfileController@actionChangedInForm')->name('profile_changed_password_act');

    Route::get('/info','ProfileController@showInfoForm')->name('profile_info');
    Route::post('/info','ProfileController@actionInfoForm')->name('profile_info_saved');

});

Route::group(['prefix' => 'user'], function () {
    Route::get('activation/{token}', 'UserActivationController@activateUser')->name('user_activate');

});
