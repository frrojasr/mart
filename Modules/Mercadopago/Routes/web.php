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

//Route::prefix('mercadopago')->group(function() {
//    Route::get('/', 'MercadopagoController@index');
//});

Route::group(['prefix' => 'gateway/mercadopago', 'as' => 'mercadopago.', 'middleware' => ['auth', 'permission', 'locale']], function () {
    Route::post('/store', 'MercadopagoController@store')->name('store')->middleware('checkForDemoMode');
    Route::get('/edit', 'MercadopagoController@edit')->name('edit');
    //Route::post('/ipn', 'MercadopagoController@ipn')->name('ipnMp');

});

Route::group(['prefix' => 'gateway/mercadopago', 'as' => 'mercadopago.'], function () {

    Route::post('/ipn', 'MercadopagoController@ipn')->name('ipnMp');

});