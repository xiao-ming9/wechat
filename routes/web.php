<?php

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
    return view('welcome');
});
Route::any('/wechat1','WeChatController@serve1');
Route::any('/wechat2','WeChatController@serve2');
Route::get('/user/{openId?}','WeChatController@user');
Route::get('/sendmsg1','WeChatController@sendMsg1');
Route::get('/sendmsg2','WeChatController@sendMsg2');
Route::get('/template','WeChatController@templateMsg');
Route::get('/subscribtion','WeChatController@subscribtion');
Route::group(['middleware'=>['web','wechat.oauth']],function(){
    Route::get('/user','WeChatController@oauth');
});