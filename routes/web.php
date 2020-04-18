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

Route::get('/schedule','RadioProgramController@fetchProgramGuide')->name('schedule');

//番組タイトルで検索する
Route::get('search','CrudController@index')->name('search');

//番組の詳細情報
Route::get('list/{id}/{title}','ViewProgramDetailsController@index')->name('list');

//放送局の週間番組表を表示する
Route::get('station/{id}', 'RadioBroadcastController@getBroadCastId')->name('station');
