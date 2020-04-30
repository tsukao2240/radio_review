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

Auth::routes(['verify' => true]);

//放送中の番組を取得する
Route::get('/schedule', 'RadioProgramController@fetchProgramGuide')->name('schedule');

//番組タイトルで検索する
Route::get('search', 'CrudController@index')->name('search');

//番組の詳細情報を表示する
Route::get('list/{station_id}/{title}', 'ViewProgramDetailsController@index')->name('detail');

//放送局の週間番組表を取得する
Route::get('station/{station_id}', 'RadioBroadcastController@getBroadCastId')->name('station');

//投稿されているレビューをすべて表示する
Route::get('/review/list','PostController@view')->name('view');

//詳細情報画面でレビューがあったら表示する
// Route::get('list/{station_id}/{title}','PostController@list')->name('list');

//検索画面
Route::get('/program', 'PostController@index')->name('program');

//投稿画面
Route::get('/review/{id}', 'PostController@review')->name('review');

//レビューの投稿
Route::post('/review/{id}', 'PostController@store')->name('store');

//自分が投稿したレビューを表示する
Route::get('/my','MypageController@index')->name('myreview');

//編集画面
Route::get('/my/edit/{program_id}','MypageController@edit')->name('myreview_edit');

//自分が投稿したレビューを編集する
Route::post('/my/edit/{program_id}','MypageController@update')->name('myreview_update');

//自分が投稿したレビューを削除する
Route::post('/my','MypageController@destroy')->name('myreview_delete');



