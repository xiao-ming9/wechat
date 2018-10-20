<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use EasyWeChat\Factory;//用于注册微信接口
use EasyWeChat\Kernel\Messages\Text;//用于定义消息的类型
use Illuminate\Support\Facades\Storage;//用于文件上传

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
        $user = session(['wechat.oauth_user.default']);
        return $user->getName().'是傻吊';
    }
    
    /**
     * 上传素材
     */
    public function media()
    {
        $path = '/home/xiaoming/图片/2018-07-29 20-22-41 的屏幕截图.png';
        $info = $this->app->media->uploadImage($path);//上传文件（图片）
        $stream = $this->app->media->get($info['media_id']);//获取文件

        //$stream返回\EasyWeChat\Kernel\Http\StreamResponse实例
        //instanceof:（1）判断一个对象是否是某个类的实例，（2）判断一个对象是否实现了某个接口
        if ($stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $this->app->broadcasting->sendImage($info['media_id']);
            return 'ok';
        }
    }

    /**
     * 二维码
     */
    public function qrcode()
    {
        //获取二维码信息
        $result = $this->app->qrcode->temporary('foo',60*60);
        //通过上面获取的二维码信息的ticket获取二维码的url
        $url = $this->app->qrcode->url($result['ticket']);
        $content = file_get_contents($url);//获得二进制文件
        Storage::put('qrcode.jpg',$content);
    }

    /**
     * 公众号菜单
     */
    public function menu()
    {
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url"  => "http://www.baidu.com/"
                    ],
                    [
                        "type" => "view",
                        "name" => "网页认证测试",
                        "url"  => "http://wx.xiaoming.net.cn/oauth"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        $app->menu->create($buttons);
    }
} 