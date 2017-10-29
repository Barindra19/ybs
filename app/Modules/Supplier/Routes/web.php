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

Route::group(['prefix' => 'supplier','middleware' => 'auth'], function () {
    Route::get('/','SupplierController@show')->name('supplier');
    Route::get('/show','SupplierController@show')->name('supplier_show');
    Route::get('/datatables','SupplierController@datatables')->name('supplier_datatables');
    Route::get('/datatables_detail/{SupplierID}','SupplierController@datatables_detail')->name('supplierlist_datatables_detail');

    Route::get('/add','SupplierController@add')->name('supplier_add');
    Route::post('/post','SupplierController@post')->name('supplier_post');
    Route::get('/edit/{id}','SupplierController@edit')->name('supplier_edit');
    Route::post('/update','SupplierController@update')->name('supplier_update');
    Route::get('/delete/{id}','SupplierController@delete')->name('supplier_delete');
    Route::post('/add_supplier','SupplierController@add_supplier')->name('supplier_addbystock');



    Route::get('/list_customer','SupplierController@list_by_popup')->name('supplier_list_customer');
    Route::get('/detail_customer/{CustomerID}','SupplierController@detail_customer')->name('supplier_detail_supplier');
    Route::post('/getdetail','SupplierController@getdetail')->name('customer_getdetail');

    Route::post('/search_autocomplete','SupplierController@search_autocomplete')->name('supplier_search_autocomplete');
    Route::post('/get_detail','SupplierController@get_detail')->name('supplier_get_detail');

});
