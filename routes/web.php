<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/login_act','Auth\LoginController@login')->name('login_act');

### REQUEST PASSWORD ###
Route::get('/password_request','RequestController@password_request')->name('password_request');
Route::post('/password_request','RequestController@password_requestsend')->name('password_request_send');
Route::get('/form_password_request/{token}','RequestController@form_password_request')->name('users_request_reset_password');
Route::post('/password_act','RequestController@password_requestact')->name('password_request_act');
### REQUEST PASSWORD ###

Auth::routes();

Route::group(['middleware' => 'auth'], function(){
    Route::get('/','HomeController@index');
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/home_by_branch/{BranchID}', 'HomeController@by_branch')->name('home_by_branch');
    Route::get('/dashboard','HomeController@dashboard')->name('dashboard');
    Route::get('/home_datatables','HomeController@datatables')->name('home_order_datatables');
    Route::post('/check_transaction','HomeController@checkTransaction')->name('home_check_transaction');
});
