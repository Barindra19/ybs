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

Route::group(['prefix' => 'report', 'middleware' => 'auth'], function () {
    Route::get('/','OrderController@show')->name('order_report');
    Route::get('/show','OrderController@show')->name('order_report_show');
});


Route::group(['prefix' => 'report/order', 'middleware' => 'auth'], function () {
    Route::get('/','OrderController@show')->name('order_reportorder');
    Route::get('/show','OrderController@show')->name('order_reportorder_show');
    Route::get('/retrieve/{get}','OrderController@retrieve')->name('order_reportorder_retrieve');

});

Route::group(['prefix' => 'report/order/item', 'middleware' => 'auth'], function () {
    Route::get('/','OrderItemController@show')->name('order_reportorderitem');
    Route::get('/show','OrderItemController@show')->name('order_reportorderitem_show');
    Route::get('/retrieve/{get}','OrderItemController@retrieve')->name('order_reportorderitem_retrieve');

});


Route::group(['prefix' => 'report/transaction', 'middleware' => 'auth'], function () {
    Route::get('/','TransactionController@show')->name('order_reporttransaction');
    Route::get('/show','TransactionController@show')->name('order_reporttransaction_show');
    Route::get('/retrieve/{get}','TransactionController@retrieve')->name('order_reporttransaction_retrieve');
});

Route::group(['prefix' => 'report/event', 'middleware' => 'auth'], function () {
    Route::get('/','EventController@show')->name('order_reportevent');
    Route::get('/show','EventController@show')->name('order_reportevent_show');
    Route::get('/retrieve/{get}','EventController@retrieve')->name('order_reportevent_retrieve');
});

Route::group(['prefix' => 'report/bukubesar', 'middleware' => 'auth'], function () {
    Route::get('/','BukubesarController@show')->name('order_reportbukubesar');
    Route::get('/show','BukubesarController@show')->name('order_reportbukubesar_show');
    Route::get('/retrieve/{get}','BukubesarController@retrieve')->name('order_reportbukubesar_retrieve');
});


Route::group(['prefix' => 'report/profitloss', 'middleware' => 'auth'], function () {
    Route::get('/','ProfitLossController@show')->name('order_reportprofitloss');
    Route::get('/show','ProfitLossController@show')->name('order_reportprofitloss_show');
    Route::get('/retrieve/{get}','ProfitLossController@retrieve')->name('order_reportprofitloss_retrieve');
});
