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

Route::group(['prefix' => 'stock', 'middleware' => 'auth'], function () {
    Route::get('/','StockController@show')->name('stock');
    Route::get('/show','StockController@show')->name('stock_show');
    Route::get('/datatables','StockController@datatables')->name('stock_datatables');
    Route::get('/datatables_list','StockController@datatables_list')->name('stocklist_datatables');
    Route::get('/add','StockController@add')->name('stock_add');
    Route::post('/post','StockController@post')->name('stock_post');
    Route::get('/edit/{id}','StockController@edit')->name('stock_edit');
    Route::post('/update','StockController@update')->name('stock_update');
    Route::get('/inactive/{id}','StockController@inactive')->name('stock_inactive');
    Route::get('/barcode/{id}','StockController@generate_barcode')->name('stock_generate_barcode');

    Route::post('/info','StockController@info')->name('stock_info');
    Route::post('/update_stock','StockController@update_stock')->name('stock_update_stock');


});
