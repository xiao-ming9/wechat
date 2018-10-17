<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use EasyWeChat\Factory;

class WeChatController extends Controller
{
    /**
     * 处理微信请求消息方法1
     * 
     * @return string
     */
    public function serve1()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $app = app('wechat.official_account');
        $app->server->push(function($message){
            return "欢迎来到小铭的测试号！";
        });

        return $app->server->serve();

        
    }

    /**
     * 处理微信请求方法2
     * 
     * @return array
     */
    public function serve2()
    {
        $config = [
            'app_id' => 'wxb2da08dedee851c5',
            'secret' => 'db5fe129882bfd683fe267609882f671',
            'token' => 'xm666',
            'response_type' => 'array',           
        ];
        $app = Factory::officialAccount($config);
    
        return $response = $app->server->serve();
    }
    public function user()
    {
        $app = app('wechat.official_account');
        return $app->user->list($nextOpenId = null);
    }
}
