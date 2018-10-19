<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use EasyWeChat\Factory;//用于注册微信接口
use EasyWeChat\Kernel\Messages\Text;//用于定义消息的类型

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
            if($message['MsgType']=='event'){
                if($message['Event'] == 'subscribe'){
                    $openId = $message['FromUserName'];//获取用户的openId
                    $user  = $this->app->user->get($openId);
                    return '欢迎关注'.$user['nickname'];//获取用户的昵称
                }
            }

            if($message['MsgType']=='text'){
                switch($message['Content']){
                    case '你好':
                        return '你好';
                        break;
                    case '小铭帅不帅':
                        return '帅';
                        break;
                    default:
                        return 'http://wx.xiaoming.net.cn/oauth';
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

    /**
     * 群发消息方法1
     * 
     * 此处必须定义$msg为Message下某个对象实例，因为sendMessage参数要求，否则报错
     * @return string
     */
    public function sendMsg1()
    {
        $msg = New Text('you are a clever boy');   
        $this->app->broadcasting->sendMessage($msg);
        return "send successfully";
    }

    /**
     * 群发消息方法2
     * 
     * @return string
     */
    public function sendMsg2()
    {
        $this->app->broadcasting->sendText('you are a handsome boy');
        return "send successfully";
    }

    /**
     * 模板消息
     * 
     */
    public function templateMsg()
    {
        $industryId1 = 1;
        $industryId2 = 2;
        //设置所属行业
        $this->app->template_message->setIndustry($industryId1, $industryId2);
        //return $this->app->template_message->getIndustry();
        //return $this->app->template_message->getPrivateTemplates();
        $this->app->template_message->send([
            'touser' => 'oRBmk0n0GkOPh2PozUcj3E0jk4bg',
            'template_id' => 'AjxeRo3HUdNjz7RWhGEsd8KnqnJsLIAIIiTs7-mifKY',
            'url' => 'http://wx.xiaoming.net.cn/oauth',
            'data' => [
                'Name' => 'xiaoming',
                'Age' => '8',
            ],
        ]);
    }

    /**
     * 微信网页认证
     */
    public function oauth()
    {
        $user = $this->app->oauth->user();
        return $user->getName().'是傻吊';
    }
    
} 