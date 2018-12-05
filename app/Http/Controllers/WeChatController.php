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
                // if($message['Event'] == 'subscribe'){
                //     $openId = $message['FromUserName'];//获取用户的openId
                //     $user  = $this->app->user->get($openId);
                //     return '欢迎关注'.$user['nickname'];//获取用户的昵称
                // }
                switch($message['Event']){
                    case 'subscribe':
                        $openId = $message['FromUserName'];//获取用户的openId
                        $user  = $this->app->user->get($openId);
                        return '欢迎关注'.$user['nickname'];//获取用户的昵称
                        break;
                    //菜单点击响应
                    case 'CLICK':
                        if($message['EventKey'] == "QRCODE" ){
                            return 'hello';
                        }
                        break;
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
        $user = session('wechat.oauth_user.default'); // 拿到授权用户资料
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
                "name" => "扫码关注呀",
                "key"  => "QRCODE"
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
        $this->app->menu->create($buttons);
    }

    /**
     * 微信卡券
     * @return json
     */
    public function createCard()
    {
        $card = $this->app->card;//获取实例
        //添加测试白名单
        $openids = ['oRBmk0n0GkOPh2PozUcj3E0jk4bg','oRBmk0omRyr48tYFCmpaKZ6ZPgGY'];
        $user_result = $card->setTestWhitelist($openids);

        //创建卡券

        /**
         * 卡券类型
         *  团购券：GROUPON;
         *  折扣券：DISCOUNT;
         *  礼品券：GIFT; 
         *  代金券：CASH;
         *  通用券：GENERAL_COUPON;
         *  会员卡：MEMBER_CARD;
         *  景点门票：SCENIC_TICKET ；
         *  电影票：MOVIE_TICKET；
         *  飞机票：BOARDING_PASS；
         *  会议门票：MEETING_TICKET；
         *  汽车票：BUS_TICKET;
         */
        $cardType = 'DISCOUNT';
        $attributes = [
            'base_info' => [
                'logo_url' => 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1540031633329&di=e45b9af751ee6aae9c8e4bac27b02bf9&imgtype=0&src=http%3A%2F%2Fimgsrc.baidu.com%2Fimgad%2Fpic%2Fitem%2F0b46f21fbe096b6337ef116a07338744ebf8ac12.jpg',
                'brand_name' => '小铭的小礼物',
                'code_type' => 'CODE_TYPE_QRCODE',
                'title' => '双人潮汕火锅套餐',
                'color' => 'Color030',
                'notice' => '使用时向服务员出示此券',
                'description'=>'不够再继续找我拿',
                "date_info" => [
                    "type" => "DATE_TYPE_FIX_TERM",
                    "fixed_term" => 365,
                    "fixed_begin_term"=> 0,
                ], 
                "sku" => [
                    "quantity"=> 10
                ],

            ],
            'discount' => 90,
        ]; 
        return $card_result = $card->create($cardType, $attributes);
    }

    /**
     * 创建卡券二维码
     */
    public function createCardQrcode()
    {
        $card = $this->app->card;
        $cards = [
            "action_name" => "QR_CARD",
            "action_info" => [
                "card" => [
                    "card_id" => "pRBmk0ojH8vj69PaETXYAbZ_48KU",
                    "outer_str" => "13b",
                ],
            ],
        ];
        //创建二维码
        $result = $card->createQrCode($cards);
    //     //获取二维码
    //     $qrcode = $card->getQrCode($result['ticket']);
    }

    /**
     *
     */
    public function getQrCode1()
    {
        $card = $this->app->card;
        $ticket = 'gQFM8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQWFUWTRwNHBlQWkxdy12SU50NEoAAgS_68pbAwSAM_EB';
        return $card->getQrCodeUrl($ticket);
    }
} 