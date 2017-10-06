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

Route::group(['prefix' => 'branch','middleware' => 'auth'], function () {
    Route::get('/','BranchController@show')->name('branch');
    Route::get('/show','BranchController@show')->name('branch_show');
    Route::get('/datatables','BranchController@datatables')->name('branch_datatables');
    Route::get('/add','BranchController@add')->name('branch_add');
    Route::post('/post','BranchController@post')->name('branch_post');
    Route::get('/edit/{id}','BranchController@edit')->name('branch_edit');
    Route::post('/update','BranchController@update')->name('branch_update');
    Route::get('/delete/{id}','BranchController@delete')->name('branch_delete');
    Route::post('/resetsaldo','BranchController@resetsaldo')->name('branch_resetsaldo');
    Route::post('/info','BranchController@info')->name('branch_info');


});
