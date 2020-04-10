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
    return view('home.top');
});

Auth::routes();

Route::get('/fetchRadioProgramData','InsertRadioProgramController@fetchRadioInfoOneweek');

Route::get('/radioProgramList','RadioProgramController@fetchProgramGuide');

//番組タイトルで検索する

Route::get('Search','CrudController@index')->name('search');
