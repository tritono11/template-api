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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::middleware('auth:api')->get('/posts', 'PostsController@index');
Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
Route::post('authenticate', 'AuthenticateController@authenticate');
Route::post('logout', 'AuthenticateController@logout');
Route::post('signup', 'AuthenticateController@signup');
Route::post('beginPasswordReset', 'AuthenticateController@beginPasswordReset');


