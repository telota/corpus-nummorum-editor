<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Redirect index route to wiki
Route::get('/', function() {
    return redirect('/wiki#usage-api');
});

Route::namespace('App\Http\Controllers\dbi')->group(function () {

    Route::get  ('/documentation/{entity}',             'APIController@documentation');
    Route::get  ('/parameters/{entity}',                'APIController@parameters');

    Route::get  ('/cn_{entity}_{id}.{extension}',       'APIController@select');
    Route::get  ('/cn_{entity}_{id}',                   'APIController@select');

    Route::match(['get', 'post'], '/{entity}/{id?}',    'APIController@select');
});
