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

Route::group(['prefix' => 'background'], function () {
    Route::get('/', function () {
        dd('This is the Background module index page. Build something great!');
    });

    Route::get('/customer_create_user/{BranchID}','BackgroundController@customer_create_user')->name('background_customer_create_user');
    Route::get('/changed_stock_code','BackgroundController@changed_stock_code')->name('background_changed_stock_code');
});
