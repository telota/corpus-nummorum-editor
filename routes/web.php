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

Auth::routes();

Route::get('/', function () {
    return view('base.index');
});

Route::get('/home', function() {
    return redirect('/');
});


// Base Namespace ---------------------------------------------------------
Route::namespace('App\Http\Controllers\base')->group(function () {

    // Markdown
    Route::get('/readme', 'MarkdownController@readme')->name('readme');
    Route::get('/license', 'MarkdownController@license')->name('license');
    Route::get('/licensing', 'MarkdownController@licensing')->name('licensing');

    Route::get('/wiki', 'MarkdownController@documentation')->name('wiki');
    Route::get('/documentation', function() {
        return redirect('/wiki');
    });
    Route::get('/changelog', 'MarkdownController@changelog')->name('changelog');

    // Redirects
    Route::get('/public-website', function() {
        return redirect('https://www.corpus-nummorum.eu/');
    });
    Route::get('/imprint', function() {
        return redirect('https://www.corpus-nummorum.eu/legal-notice');
    });
    Route::get('/contact', function() {
        return redirect('https://www.corpus-nummorum.eu/contact');
    });
    Route::get('/data-protection', function() {
        return redirect('https://www.corpus-nummorum.eu/data-protection');
    });
    Route::get('/repository/research-data', function() {
        return redirect('https://www.corpus-nummorum.eu/d2r');
    });

    Route::get('/repository/source-code', function() {
        return redirect('https://github.com/telota/corpus-nummorum-editor');
    });
    Route::get('/repository/research-data', function() {
        return redirect('https://github.com/telota');
    });

	// SPARQL
	Route::get('/sparql', function() {
        return redirect('https://www.corpus-nummorum.eu/d2r/');
	});
});


// Editor Frontend --------------------------------------------------------
Route::prefix('editor')->namespace('App\Http\Controllers\editor')->group(function() {
    Route::get('/', 'AppController@initiate')->name('editor');
});

// Raw Datasets
Route::namespace('App\Http\Controllers\dbi')->group(function() {
    Route::get  ('/cn_{entity}_{id}.{extension}',       'APIController@select');
    Route::get  ('/cn_{entity}_{id}',                   'APIController@select');
});

// Routes protected by Middleware -----------------------------------------
Route::middleware(['auth'])->group(function () {

    // Account Status
    Route::get('/account', 'App\Http\Controllers\base\AccountController@status')->name('account-status');

    // Internal API (DBI)
    Route::prefix('dbi')->namespace('App\Http\Controllers\dbi')->group(function() {
        // Index
        Route::get  ('/',   'dbiController@index');

        // Dashboard
        Route::get  ('/dashboard',          'DashboardController@data');
        Route::post ('/dashboard/input',    'DashboardController@updateSettings');

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

    // Storage Namespace
    Route::prefix('storage-api')->namespace('App\Http\Controllers\storage')->group(function() {

        Route::get  ('/img',                     'ImageHandler@test');

        Route::get  ('/search',                     'SearchController@select');
        Route::get  ('/index/{directory?}',         'StorageController@index')->where('directory', '.+');
        Route::post ('/action/upload',              'StorageController@upload');
        Route::post ('/action/{action}',            'StorageController@action');

        Route::get  ('/details/{root}/{path}',      'StorageController@details')->where('path', '.+');
    });
});


//Route::get  ('/test/{input?}', 'App\Http\Controllers\dbi\handler\index_handler@parseStatement');
