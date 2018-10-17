<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class WeChatController extends Controller
{
    /**
     * 处理微信请求消息
     * 
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.');

        $app = app('wechat.official_account');
        $app->server->push(function($message){
            return "连接成功";
        });

        return $app->server->serve();
    }
}
