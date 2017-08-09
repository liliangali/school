<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
class AdminMiddleware
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
        $response = ['state'=>'0','msg'=>"只有管理员可以操作"];

        if($user->IDLevel != "U")
        {
            return response()->json($response);
        }
        return $next($request);
    }
}
