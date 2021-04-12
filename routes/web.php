<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('base.index');
});

Auth::routes();

Route::get('/home', function() {
    return redirect('/');
});


// Base Namespace ---------------------------------------------------------
Route::namespace ('App\Http\Controllers\base')->group(function () {

    Route::get('/account-status', 'AccountStatusController@index')->name('account-status');

    Route::get('/readme', 'InformationController@readme')->name('readme');
    Route::get('/license', 'InformationController@license')->name('license');
    Route::get('/licensing', 'InformationController@licensing')->name('licensing');
    Route::get('/imprint', function() {
        return redirect('https://www.corpus-nummorum.eu/legal-notice');
    });

    // Wiki
    Route::prefix('wiki')->group(function() {
        Route::get('/', 'InformationController@wikiIndex')->name('wikiIndex');
    });
});

// Editor Frontend
Route::prefix('editor')->namespace ('App\Http\Controllers\editor')->group(function() {
    Route::get('/', 'AppController@initiate')->name('editor');
});

// Protected Routes -------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // Internal API (DBI)
    Route::prefix('dbi')->namespace('App\Http\Controllers\dbi')->group(function() {
        // Index
        Route::get  ('/',   'dbiController@index');

        // Dashboard
        Route::get  ('/dashboard',          'DashboardController@data');
        Route::post ('/dashboard/input',    'DashboardController@update_presets');

        // Import
        Route::post ('/import/{entity}',    'import\ImportController@parse');

        // File Browser
        Route::get  ('/files/browse/storage',               'FileController@index');
        Route::get  ('/files/browse/storage/{directory}',   'FileController@browse')->where('directory', '.+');
        Route::post ('/files/upload/storage/{directory}',   'FileController@upload')->where('directory', '.+');
        Route::post ('/files/delete/file',                  'FileController@delete');

        // Input
        Route::post ('/{entity}/input',     'dbiController@input');
        Route::post ('/{entity}/delete',    'dbiController@delete');
        Route::post ('/{entity}/connect',   'dbiController@connect');

        // Output
        Route::match(['get', 'post'], '/{entity}/{id?}',      'dbiController@select');

        //Route::get  ('/files/info/storage/{directory}',     'FileController@info')   -> where('directory', '.+');
    });
});

/*// CN Editor
Route::prefix('editor')
    ->namespace ('App\Http\Controllers\editor')
    ->middleware(['auth'])
    ->group(function() {
        Route::get('/', 'MainController@initiate')->name('editor');
    }
);

// DBI (internal API)
Route::prefix('dbi')
    ->namespace('App\Http\Controllers\dbi')
    ->middleware(['auth'])
    ->group(function() {

        // Index
        Route::get  ('/',   'dbiController@index');

        // Dashboard
        Route::get  ('/dashboard',          'DashboardController@data');
        Route::post ('/dashboard/input',    'DashboardController@update_presets');

        // Import
        Route::post ('/import/{entity}',    'import\ImportController@parse');

        // File Browser
        Route::get  ('/files/browse/storage',               'FileController@index');
        Route::get  ('/files/browse/storage/{directory}',   'FileController@browse')->where('directory', '.+');
        Route::post ('/files/upload/storage/{directory}',   'FileController@upload')->where('directory', '.+');
        Route::post ('/files/delete/file',                  'FileController@delete');

        // Input
        Route::post ('/{entity}/input',     'dbiController@input');
        Route::post ('/{entity}/delete',    'dbiController@delete');
        Route::post ('/{entity}/connect',   'dbiController@connect');

        // Output
        Route::match(['get', 'post'], '/{entity}/{id?}',      'dbiController@select');

        //Route::get  ('/files/info/storage/{directory}',     'FileController@info')   -> where('directory', '.+');
    }
);*/
