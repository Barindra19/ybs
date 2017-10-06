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

Route::group(['prefix' => 'event','middleware' => 'auth'], function () {
    Route::get('/','EventController@show')->name('event');
    Route::get('/show','EventController@show')->name('event_show');
    Route::get('/datatables','EventController@datatables')->name('event_datatables');
    Route::get('/add','EventController@add')->name('event_add');
    Route::post('/post','EventController@post')->name('event_post');
    Route::get('/edit/{id}','EventController@edit')->name('event_edit');
    Route::post('/update','EventController@update')->name('event_update');
    Route::get('/delete/{id}','EventController@delete')->name('event_delete');
    Route::post('/resetsaldo','EventController@resetsaldo')->name('event_resetsaldo');
    Route::post('/info','EventController@info')->name('event_info');
    Route::post('/searchbybranch','EventController@searchbybranch')->name('event_searchbybranch');

});
