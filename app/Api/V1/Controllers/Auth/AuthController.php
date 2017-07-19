<?php

namespace App\Api\V1\Controllers\Auth;
use App\Models\ChannelInfo;
use App\Models\Exercise;
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
//        $aa = file_get_contents('log1.txt');
//        echo '<pre>';print_r(unserialize(base64_decode($aa)));exit;
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



    public function rep(Request $request)
    {
        $res = Exercise::addT($request);
        if($res['status'] == 1)
        {
            echo "<AUTHERROR>0</AUTHERROR>";
        }
        else
        {
            $CLASSNAME = mb_convert_encoding($res['msg'],"EUC-CN","UTF-8");
            echo  $CLASSNAME;
        }
    }

}
