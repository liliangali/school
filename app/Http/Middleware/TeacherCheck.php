<?php

namespace App\Http\Middleware;

use Closure;
use  JWTAuth;
class TeacherCheck
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user)
        {
            return response()->json(['error'=>'token_expired'])->setStatusCode(401);
        }
        $response = ['state'=>'0','msg'=>"只有教师可以操作"];
        echo '<pre>';print_r($user);exit;
        
        if($user->IDLevel != "T")
        {
            return response()->json($response);
        }
        return $next($request);
    }
}
