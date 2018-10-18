<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use EasyWeChat\Factory;

class WeChatController extends Controller
{
    public $app;

    //指定默认值，否则无法解析构造函数
    public function __construct($app='')
    {
        $app = app('wechat.official_account');
        $this->app = $app;
    }
    /**
     * 处理微信请求消息方法1
     * 
     * @return string
     */
    public function serve1()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        // $app = app('wechat.official_account');
        $this->app->server->push(function($message){
            if($message['Event']=='subscribe'){
                $openId = $message['FromUserName'];//获取用户的openId
                $user  = $this->app->user->get($openId);
                return '欢迎关注'.$user['nickname'];//获取用户的昵称
            }else{
                switch($message['Content']){
                    case '你好':
                        return '你好';
                        break;
                    case '小铭帅不帅':
                        return '帅';
                        break;
                    default:
                        return '收到你的消息啦';
                        break;
                }
            }    
        });

        return $this->app->server->serve();

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

    /**
     * 获取用户的信息
     * 
     * @return array
     */
    public function user($openId = null)
    {
        if($openId == null){
            //$app = app('wechat.official_account');
            return $this->app->user->list($nextOpenId = null);
        }else{
            //$app = app('wechat.official_account');
            $user = $this->app->user->get($openId);
            return $user;
        }       
    }
}