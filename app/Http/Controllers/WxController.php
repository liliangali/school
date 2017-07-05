<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Error;
use App\Models\Teacher;
use App\Models\WxConfig;
use App\Models\WxSublog;
use App\User;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Text;
use EasyWeChat\OpenPlatform\Guard;
use JWTAuth;
class WxController extends Controller
{
    public function index()
    {
        $list = Teacher::where([])->orderBy('TID', 'desc')->paginate();

        return view('welcome');
        
        $options = [
            // ...
            'open_platform' => [
                'app_id'   => env('OPEN_WECHAT_APPID'),
                'secret'   => env('OPEN_WECHAT_SECRET'),
                'token'    => env('OPEN_WECHAT_TOKEN'),
                'aes_key'  => env('OPEN_WECHAT_AES_KEY')
            ],
            // ...
        ];
        $app = new Application($options);
        $openPlatform = $app->open_platform;//开发平台
        $server = $openPlatform->server;
        $server->setMessageHandler(function($event) use ($openPlatform) {
            // 事件类型常量定义在 \EasyWeChat\OpenPlatform\Guard 类里namespace EasyWeChat\OpenPlatform;
            switch ($event->InfoType) {
                case Guard::EVENT_AUTHORIZED: // 授权成功
                    $authorizationInfo = $openPlatform->getAuthorizationInfo($event->AuthorizationCode);
                // 保存数据库操作等...
                case Guard::EVENT_UPDATE_AUTHORIZED: // 更新授权
                    // 更新数据库操作等...
                case Guard::EVENT_UNAUTHORIZED: // 授权取消
                    // 更新数据库操作等...
            }
        });
        $response = $server->serve();
        return $response;
    }
    
    public function wxthree()
    {

        $options = [
            // ...
            'open_platform' => [
                'app_id'   => env('OPEN_WECHAT_APPID'),
                'secret'   => env('OPEN_WECHAT_SECRET'),
                'token'    => env('OPEN_WECHAT_TOKEN'),
                'aes_key'  => env('OPEN_WECHAT_AES_KEY')
            ],
            // ...
        ];
        $app = new Application($options);
        $openPlatform = $app->open_platform;//开发平台

        // 直接跳转
        $response = $openPlatform->pre_auth->redirect(route('wxcode'));
        // 获取跳转的 URL
        return view('wx/wx',['url'=> $response->getTargetUrl()]);
    }


    public function wcode()
    {
        $options = [
            'open_platform' => [
                'app_id'   => env('OPEN_WECHAT_APPID'),
                'secret'   => env('OPEN_WECHAT_SECRET'),
                'token'    => env('OPEN_WECHAT_TOKEN'),
                'aes_key'  => env('OPEN_WECHAT_AES_KEY')
            ],
        ];
        $app = new Application($options);

        $openPlatform = $app->open_platform;//开发平台

        $res = $openPlatform->getAuthorizationInfo();

        $authorization_info = $res->authorization_info;

        $timestamp = Carbon::now(config('app.timezone'))->timestamp;
        $authorizer_refresh_token = $authorization_info['authorizer_refresh_token'];
        if($authorizer_refresh_token)
        {
            $wxconfig = WxConfig::where("wxcode","authorizer_refresh_token")->first();
            if($wxconfig)
            {
                $wxconfig->wxvalue = $authorizer_refresh_token;
                $wxconfig->add_time = $timestamp;
                $wxconfig->save();
            }
            else
            {
                WxConfig::insert(['wxcode'=>'authorizer_refresh_token','wxvalue'=>$authorizer_refresh_token,'add_time'=>$timestamp]);
            }
             echo '<pre>';print_r("SUCCESS");exit;
        }
        echo '<pre>';print_r("无法获得authorizer_refresh_token");exit;
    }
    
    public function notify($appid)
    {
        $application = new Application(config("wechat.open_app_config"));
        $app = $application->open_platform->createAuthorizerApplication(config("wechat.app_id"), WxConfig::getRefreshToken());
        $message = $app->server->getMessage();
        $user =  $app->user->get($message['FromUserName']);//获取用户信息
        $wx_sublog_mod = new WxSublog();
        $eventKey = 0;
        if(isset($message['EventKey']))
        {
            $key = str_replace('qrscene_', '', $message['EventKey']);
            if(str_contains($key,'bangding_') !== false)//渠道要绑定自己的微信号
            {
                User::bangCha($user,str_replace('bangding_', '', $key));
                $app->staff->message(new Text(['content' =>'恭喜您,绑定成功']))->to($message['FromUserName'])->send();
            }
            $eventKey = intval($key);
        }
        if($message['MsgType'] == 'event' &&  ($message['Event'] == 'subscribe' || $message['Event'] == 'unsubscribe')) //关注事件
        {
            if($eventKey)
            {
                User::qrcode($eventKey,$user);
                $wx_sublog_mod->remark = $eventKey;
            }
            if($message['Event'] == 'unsubscribe') //取消关注要取消渠道信息
            {
                User::uqrcode($user);
            }
            //存入log
            $wx_sublog_mod->openid = $user->openid;
            $wx_sublog_mod->stype = $message['Event'];
            $wx_sublog_mod->save();
        }

        //狗民专属券Beg
        if($eventKey == 1)
        {
            if(User::dogVoucher($user))
            {
                $messagea = new Text(['content' =>
                    "特别的爱，给特别的TA
炎炎夏日
我们共同守护TA
<a href='http://h5.myfoodiepet.com/member-quan-1.html'>20元优惠券</a>
笑纳"]);
                $app->staff->message($messagea)->to($message['FromUserName'])->send();
            }
        }
        //狗民专属券End

        if($eventKey == 543)
        {

            $messagea = new Text(['content' =>'测试bug']);
            $app->staff->message($messagea)->to($message['FromUserName'])->send();
        }

    }
}
