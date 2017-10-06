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

Route::group(['prefix' => 'barcode','middleware' => 'auth'], function () {
    Route::get('/','BarcodeController@show')->name('barcode');
    Route::get('/show','BarcodeController@show')->name('barcode_show');
    Route::get('/datatables','BarcodeController@datatables')->name('barcode_datatables');
    Route::post('/generate','BarcodeController@generate')->name('barcode_generate');
    Route::get('/download/{id}','BarcodeController@download')->name('barcode_download');
});
