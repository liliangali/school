<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
class AsdminMiddleware
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
        $response = ['state'=>'0','msg'=>"只有管理员和区域管理员才可以操作"];

        if($user->IDLevel != "U" && $user->IDLevel != "Y")
        {
            return response()->json($response);
        }
        return $next($request);
    }
}
