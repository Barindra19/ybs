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
Route::group(['prefix' => 'order'], function () {
    Route::get('/detail_order/{id}','OrderViewController@details')->name('order_details_customer');
});

Route::group(['prefix' => 'order','middleware' => 'auth'], function () {
    Route::get('/','OrderController@show')->name('order');
    Route::get('/show','OrderController@show')->name('order_show');
    Route::get('/show_kirimworkshop','OrderController@show_kirimworkshop')->name('order_show_kirimworkshop');
    Route::get('/show_proccessworkshop','OrderController@show_proccessworkshop')->name('order_show_proccessworkshop');
    Route::get('/show_kirimcounter','OrderController@show_kirimcounter')->name('order_show_kirimcounter');
    Route::get('/show_terimacounter','OrderController@show_terimacounter')->name('order_show_terimacounter');
    Route::get('/show_qcchecked','OrderController@show_qcchecked')->name('order_show_qcchecked');

    Route::get('/show_all','OrderController@show_all')->name('order_show_all');
    Route::get('/show_done','OrderController@show_done')->name('order_show_done');
    Route::get('/show_takeitems','OrderController@show_takeitems')->name('order_show_takeitems');
    Route::get('/datatables','OrderController@datatables')->name('order_datatables');
    Route::get('/datatables_kirimworkshop','OrderController@datatables_kirimworkshop')->name('order_datatables_kirimworkshop');
    Route::get('/datatables_proccessworkshop','OrderController@datatables_proccessworkshop')->name('order_datatables_proccessworkshop');
    Route::get('/datatables_kirimcounter','OrderController@datatables_kirimcounter')->name('order_datatables_kirimcounter');
    Route::get('/datatables_terimacounter','OrderController@datatables_terimacounter')->name('order_datatables_terimacounter');
    Route::get('/datatables_qcchecked','OrderController@datatables_qcchecked')->name('order_datatables_qcchecked');
    Route::get('/datatables_all','OrderController@datatables_all')->name('order_datatables_all');
    Route::get('/datatables_done','OrderController@datatables_done')->name('order_datatables_done');
    Route::get('/datatables_takeitems','OrderController@datatables_takeitems')->name('order_datatables_takeitems');
    Route::get('/add/{CustomerID}','OrderController@step1')->name('order_add');
    Route::get('/details/{id}','OrderController@details')->name('order_details');
    Route::post('/save_step1','OrderController@save_step1')->name('order_save_step1');
    Route::get('/step_2/{id}','OrderController@step2')->name('order_show_step2');
    Route::post('/save_step2','OrderController@save_step2')->name('order_save_step2');
    Route::get('/step_3/{id}','OrderController@step3')->name('order_show_step3');
    Route::post('/save_step3','OrderController@save_step3')->name('order_save_step3');
    Route::get('/step_4/{id}','OrderController@step4')->name('order_show_step4');
    Route::post('/save_laststep','OrderController@save_laststep')->name('order_save_laststep');
    Route::get('/invoice/{id}','OrderController@invoice')->name('order_invoice');
    Route::get('/repayable/{id}','OrderController@repayable')->name('order_repayable');
    Route::post('/repay','OrderController@repay')->name('order_repay');
    Route::get('/upload_image_done/{order_id}','OrderController@upload_image_done')->name('order_upload_image_done');



    Route::post('/calculate','OrderController@calculate')->name('order_calculate');
    Route::post('/calculate_result','OrderController@calculate_result')->name('order_calculate_result');
    Route::post('/calculate_header','OrderController@calculate_header')->name('order_calculate_header');
    Route::post('/calculate_cost','OrderController@calculate_cost')->name('order_calculate_cost');
    Route::post('/save_item','OrderController@save_item')->name('order_save_item');
    Route::post('/deletedetail','OrderController@deletedetail')->name('order_deletedetail');
    Route::post('/getdetail','OrderController@getdetail')->name('order_getdetail');
    Route::post('/upload','OrderController@upload')->name('order_upload');
    Route::post('/upload_finish','OrderController@upload_finish')->name('order_upload_finish');
    Route::post('/get_detailorder','OrderController@get_detailorder')->name('order_detailorder');
    Route::post('/set_changedstatus','OrderController@set_changedstatus')->name('order_changed_status');
    Route::get('/show_imagedetail/{order_detail_id}','OrderController@show_imagedetail')->name('order_show_imagedetail');
    Route::post('/get_detailorderinfo','OrderController@get_detailorderinfo')->name('order_get_detailorderinfo');
    Route::post('/edit_merk','OrderController@edit_merk')->name('order_edit_merk');
    Route::post('/form_upload_finish','OrderController@form_upload_finish')->name('order_form_upload_finish');
    Route::post('/search_autocomplete','OrderController@search_autocomplete')->name('order_search_autocomplete');
    Route::post('/get_detail','OrderController@get_detail')->name('order_get_detail');

});


Route::group(['prefix' => 'order/items','middleware' => 'auth'], function () {
    Route::get('/','OrderItemsController@show')->name('order_items');
    Route::get('/show','OrderItemsController@show')->name('order_items_show');
    Route::get('/datatables','OrderItemsController@datatables')->name('order_items_datatables');
    Route::get('/new/{CustomerID}','OrderItemsController@new')->name('order_items_new');
    Route::get('/add/{OrderID}','OrderItemsController@add')->name('order_items_add');
    Route::post('/save','OrderItemsController@save')->name('order_items_save');
    Route::get('/invoice/{order_detail_id}','OrderItemsController@invoice')->name('order_items_invoice');


    Route::post('/calculate','OrderItemsController@calculate')->name('order_items_calculate');
    Route::post('/calculate_price','OrderItemsController@calculate_price')->name('order_items_calculate_price');
    Route::post('/calculate_result','OrderItemsController@calculate_result')->name('order_items_calculate_result');
    Route::post('/calculate_header','OrderItemsController@calculate_header')->name('order_items_calculate_header');
    Route::post('/calculate_cost','OrderItemsController@calculate_cost')->name('order_items_calculate_cost');

    Route::post('/save_item','OrderItemsController@save_item')->name('order_items_save_item');
    Route::post('/deletedetail','OrderItemsController@deletedetail')->name('order_items_deletedetail');
    Route::post('/get_detailorder','OrderItemsController@get_detailorder')->name('order_items_detailorder');

});
