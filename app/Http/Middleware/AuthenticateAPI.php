<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class AuthenticateAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        $user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : false;
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : false;
        if ($user && $password) {
            $matchingUserPassword = DB::table('api_users')->where('username', $user)->take(1)->value('password');
            if ($matchingUserPassword) {
                if (!Hash::check($password, $matchingUserPassword)) {
                    //API PASSWORD WRONG
                    return Response::json(['success' => false, 'code'=>4, 'message' => 'Unauthorized!'],401);
                }
            } else {
                //API USER NOT FOUND
                return Response::json(['success' => false, 'code'=>4, 'message' => 'Unauthorized!'],401);
            }
        } else {
            //API CREDENTIALS NOT PROVIDED
            return Response::json(['success' => false, 'code'=>4, 'message' => 'Unauthorized!'],401);
        }

        return $next($request);
    }
}

