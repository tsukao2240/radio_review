<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

// 認証ルート
Auth::routes();

//放送中の番組を取得する
Route::get('/schedule', 'RadioProgramController@fetchRecentProgram')->name('program.schedule');

//番組タイトルで検索する
Route::get('search', 'CrudController@index')->name('program.search');

//番組の詳細情報を表示する
Route::get('list/{station_id}/{title}', 'ViewProgramDetailsController@index')->name('program.detail');

//詳細情報画面でレビューがあったら表示する
Route::get('list/{station_id}/{title}/review', 'PostController@list')->name('review.list');

//放送局の週間番組表を取得する
Route::get('schedule/{station_id}', 'RadioBroadcastController@getWeeklySchedule')->name('schedule.weekly');

//投稿されているレビューをすべて表示する
Route::get('/review/list', 'PostController@view')->name('review.view');

//検索画面
Route::get('/program', 'PostController@index')->name('post.program');

//メールアドレスの認証をしていないときは表示できなくする
Route::group(['middleware' => 'verified'], function () {
    //投稿画面
    Route::get('/review/{id}', 'PostController@review')->middleware('verified')->name('post.review');
    //レビューの投稿
    Route::post('/review/{id}', 'PostController@store')->middleware('verified')->name('post.store');
});

//自分が投稿したレビューを表示する
Route::get('/my', 'MypageController@index')->name('myreview.view');

//編集画面
Route::get('/my/edit/{program_id}', 'MypageController@edit')->name('myreview.edit');

//自分が投稿したレビューを編集する
Route::post('/my/edit/{program_id}', 'MypageController@update')->name('myreview.update');

//自分が投稿したレビューを削除する
Route::post('/my', 'MypageController@destroy')->name('myreview.delete');
