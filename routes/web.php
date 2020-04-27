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

//放送中の番組を取得する
Route::get('/schedule', 'RadioProgramController@fetchProgramGuide')->name('schedule');

//番組タイトルで検索する
Route::get('search', 'CrudController@index')->name('search');

//番組の詳細情報を表示する
Route::get('list/{station_id}/{title}', 'ViewProgramDetailsController@index')->name('list');

//放送局の週間番組表を取得する
Route::get('station/{station_id}', 'RadioBroadcastController@getBroadCastId')->name('station');

//検索画面
Route::get('/program', 'PostCommentsController@index')->name('program');

//投稿画面
Route::get('/review/{id}', 'PostCommentsController@review')->name('review');

//レビューの投稿
Route::post('/review/{id}', 'PostCommentsController@store')->name('store');



