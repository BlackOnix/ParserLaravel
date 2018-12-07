<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'parsing'], function () {
    Route::get('/cat', 'ApiController@categoryParser');
    Route::get('/products', 'ApiController@productsParser');
});

Route::get('/truncate', 'ApiController@truncate');
Route::get('/products/{cat_id}', 'ApiController@getProducts');
