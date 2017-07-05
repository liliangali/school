<?php

namespace App\Api\V1\Controllers\Auth;
use App\Models\ChannelInfo;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Api\V1\Controllers\BaseController;
use App\Models\ApiSign;
use App\Models\LaravelSms;
use App\Models\Order;
use App\Models\WxConfig;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use EasyWeChat\Message\Material;

class AuthController extends BaseController {

    /**
     * @SWG\Post(
     *   path="/auth/login",
     *   summary="用户登录",
     *   tags={"Auth"},
     *   @SWG\Response(
     *     response=200,
     *     description="token"
     *   ),
     *   @SWG\Parameter(name="email", in="query", required=true, type="string", description="登录邮箱"),
     *   @SWG\Parameter(name="password", in="query", required=true, type="string", description="登录密码"),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */

    public function authenticate(Request $request) {
        $credentials = $request->only('LoginID', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->errorResponse('用户名或密码错误');
            }
        } catch (JWTException $e) {
            return $this->errorResponse('创建token时出错');
        }

        $user = User::where('LoginID', $credentials['LoginID'])->first();
        $user->token = $token;
        $user->LoginTime = Carbon::now(config('app.timezone'))->timestamp;
        $user->LastLoginTime = Carbon::now(config('app.timezone'))->timestamp;
        $user->save();
        if($user->IDLevel  != "U")
        {
            return $this->errorResponse('您不是管理员');
        }
        $return['token'] = $token;
        $return['IDLevel'] = $user->IDLevel;
        $return['LastLoginTime'] = $user->LastLoginTime;
        return $this->successResponse($return);
    }

    //后台登陆
    public function aauthenticate(Request $request) {
        // grab credentials from the request
        $credentials = $request->only('username', 'password');
        $credentials['user_name'] = $credentials['username'];
        unset($credentials['username']);
        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                $response = array(
                    'msg' => '用户名或密码错误',
                    'status' => 1
                );
                return response()->json($response);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            $response = array(
                'msg' => '创建token时出错',
                'status' => 1
            );
            return response()->json($response);
        }

        $user = User::where('user_name', $credentials['user_name'])->first()->toArray();
        if($user['user_id'] != 1)
        {
            $response = array(
                'msg' => '权限不足',
                'status' => 1
            );
            return response()->json($response);
        }

        $user['token'] = $token;
        $user['username'] = $user['user_name'];
        $response = array(
            'token' => $token,
            'status' => 200,
            'userinfo' => $user
        );
        return response()->json($response);
    }

    /**
     * @param 获得 appid
     * @return \Illuminate\Http\JsonResponse
     */
    public function appid(Request $request,$remark,$all=0)
    {
        $api_sign_mod = new ApiSign();
        if($all == 100) //查看所有的appid
        {
           $list =  $api_sign_mod->get();
            return response()->json($list->toArray());
        }
        if($api_sign_mod->where('remark', $remark)->first())
        {
            $response = array(
                'error' => '备注重复',
                'status' => 300
            );
            return response()->json($response);
        }
        $appid = Str::random(16);
        $appsecret = Str::random(32);
        while ($api_sign_mod->where('appid', $appid)->first())
        {
            $appid = Str::random(32);
        }
        $api_sign_mod->appid = $appid;
        $api_sign_mod->appsecret = $appsecret;
        $api_sign_mod->remark = $remark;

        if($api_sign_mod->save())
        {
            $response = array(
                'result' => $api_sign_mod->toArray(),
                'status' => 200
            );
            return $response;
        }
        $response = array(
            'error' => '添加失败',
            'status' => 300
        );
        return response()->json($response);
    }

    /**
     * @param 用户上传图片
     * @return \Illuminate\Http\JsonResponse
     */
    public function img($oid)
    {
        $order = new Order();
        $order->getOrderQrcode($oid);
    }


    /**
     * @param 用户上传图片
     * @return \Illuminate\Http\JsonResponse
     */
    public function imgup(Request $request)
    {
        $manager = new ImageManager(array('driver' => 'imagick'));
        $img =  $manager->make($_FILES['file']['tmp_name']);       // Image::make() 支持这种方式
        $dir = storage_path('app/public/upload/');
        $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).'.png';
        $img->save($dir.$string);
        $response = array(
            'img_url' =>asset('storage/upload/'.$string)
        );
        return $this->successResponse($response);
        $imagick = new \Imagick();
        //狗图像resize大小
        $manager->make(storage_path('app/tqrcode/s.png'))->resize(290,290)->save(storage_path('app/tqrcode/ss.png'));
        $manager->make(storage_path('app/tqrcode/q.png'))->resize(130,130)->save();
        $manager->make(storage_path('app/tqrcode/a.png'))->resize(90,90)->save();//人

        //diy图片做成圆形
        $imagick->readImage(storage_path('app/tqrcode/ss.png'));
        $circle = new \Imagick();
        $circle->newImage(290, 290, 'none');
        $circle->setimageformat('png');
        $circle->setimagematte(true);
        $draw = new \ImagickDraw();
        $draw->setfillcolor('#ffffff');
        $draw->circle(290/2, 290/2, 290/2, 290);
        $circle->drawimage($draw);
        $imagick->setImageFormat( "png" );
        $imagick->setimagematte(true);
        $imagick->cropimage(290, 290, 0, 0);
        $imagick->compositeimage($circle, \Imagick::COMPOSITE_DSTIN, 0, 0);
        $imagick->writeImage(storage_path('app/tqrcode/ss.png'));
        $imagick->destroy();


       
        //初始化空白版
        $imgblank = $manager->make(storage_path('app/tqrcode/wx.png'));
        //合成人图像
        $imgblank->insert(storage_path('app/tqrcode/a.png'),'top-left', 45,  480);
        //合成狗头
        $imgblank->insert(storage_path('app/tqrcode/ss.png'),'top-left', 175,  465);
        //合成二维码
        $imgblank->insert(storage_path('app/tqrcode/q.png'),'top-left', 493,  435);
        $strr = htmlspecialchars_decode("狗'仔");
        //合成文字
        $imgblank->text($strr, 90, 620, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(17);
            $font->align('center');
            $font->angle('top');
        });
        
        $imgblank->text('狗狗 : 安迪', 30, 685, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(20);
            $font->align('left');
            $font->angle('top');
        });
        $imgblank->text('犬期 : 成全期', 30, 720, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(20);
            $font->align('left');
            $font->angle('top');
        });
        $imgblank->text('生日 : 2017-09-08', 30, 750, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(20);
            $font->align('left');
            $font->angle('top');
        });
        $imgblank->text('功效 : 减肥瘦身,减肥瘦身,减肥瘦身,', 30, 780, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(20);
            $font->align('left');
            $font->angle('top');
        });
        $str = "365填的配合,谢谢有你相信你是我独一无二的选择";
        $jiyu1 = mb_substr($str,0,12);
        $jiyu2 = mb_substr($str,12);
        $imgblank->text('主人寄语', 518, 720, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(19);
            $font->align('left');
            $font->angle('top');
        });
        $imgblank->text($jiyu1, 445, 750, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(17);
            $font->align('left');
            $font->angle('top');
        });
        $imgblank->text($jiyu2, 400, 777, function($font) {
            $font->file(resource_path("assets/img/msyh.ttf"));
            $font->size(17);
            $font->align('left');
            $font->angle('top');
        });
        $imgblank->save(storage_path('app/tqrcode/f.png'));
        echo '<pre>';print_r(11);exit;
        
        //上传素材并发送消息
    }

    /**
     * @SWG\Post(
     *   path="/auth/register",
     *   summary="用户注册",
     *   tags={"Auth"},
     *   @SWG\Response(
     *     response=200,
     *     description="register success"
     *   ),
     *   @SWG\Parameter(name="name", in="query", required=true, type="string", description="用户名"),
     *   @SWG\Parameter(name="email", in="query", required=true, type="string", description="登录邮箱"),
     *   @SWG\Parameter(name="password", in="query", required=true, type="string", description="登录密码"),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'LoginID' => 'required|unique:user|max:255',
            'password' => 'required|max:12|min:6',
        ]);

        if ($validator->fails())
        {
            return $this->errorResponse($validator->errors()->first());
        }
        $req = $request->all();

        User::register($req);
        return $this->successResponse();
    }
    /**
     * @SWG\Post(
     *   path="/auth/resetPassword",
     *   summary="重置密码",
     *   tags={"Auth"},
     *   @SWG\Response(
     *     response=200,
     *     description="modify success"
     *   ),
     *   @SWG\Parameter(name="email", in="query", required=true, type="string", description="登录邮箱"),
     *   @SWG\Parameter(name="password", in="query", required=true, type="string", description="登录密码"),
     *   @SWG\Parameter(name="resetPassword", in="query", required=true, type="string", description="确认密码"),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function resetPassword(Request $request){
        $per = [
           'email'=>$request ->get('email'),
           'password'=>bcrypt($request ->get('password')),
       ];
        $peo = [
           'resetPassword'=>bcrypt($request ->get('resetPassword'))
        ];
        $userExist = User::findUserEmail($per['email']);
        if(empty($userExist)){
            $response = array(
                'error'=>'用户不存在',
                'status'=>400,
                );
            return response() -> json($response);
        }
        $user = User::changePassword($userExist['id'],$per['password']);
        if($user === false){
            return $this->errorResponse("重置密码失败");
        } else {
            return $this->successResponse("重置密码成功");
        }

    }
}
