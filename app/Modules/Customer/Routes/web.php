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

Route::group(['prefix' => 'customer','middleware' => 'auth'], function () {
    Route::get('/','CustomerController@show')->name('customer');
    Route::get('/show','CustomerController@show')->name('customer_show');
    Route::get('/datatables','CustomerController@datatables')->name('customer_datatables');
    Route::get('/datatables_list','CustomerController@datatables_list')->name('customerlist_datatables');
    Route::get('/datatables_detail/{CustomerID}','CustomerController@datatables_detail')->name('customerlist_datatables_detail');

    Route::get('/add','CustomerController@add')->name('customer_add');
    Route::post('/post','CustomerController@post')->name('customer_post');
    Route::get('/edit/{id}','CustomerController@edit')->name('customer_edit');
    Route::post('/update','CustomerController@update')->name('customer_update');
    Route::get('/delete/{id}','CustomerController@delete')->name('customer_delete');
    Route::post('/searchbybranch','CustomerController@searchbybranch')->name('customer_searchbybranch');
    Route::post('/setbranch','CustomerController@setbranch')->name('customer_setbranch');
    Route::post('/add_customer','CustomerController@add_customer')->name('customer_addbyorder');
    Route::get('/barcode/{id}','CustomerController@generate_barcode')->name('customer_generate_barcode');



    Route::get('/list_customer','CustomerController@list_by_popup')->name('customer_list_customer');
    Route::get('/detail_customer/{CustomerID}','CustomerController@detail_customer')->name('customer_detail_customer');
    Route::post('/getdetail','CustomerController@getdetail')->name('customer_getdetail');

    Route::post('/search_autocomplete','CustomerController@search_autocomplete')->name('customer_search_autocomplete');
    Route::post('/get_detail','CustomerController@get_detail')->name('customer_get_detail');





});
