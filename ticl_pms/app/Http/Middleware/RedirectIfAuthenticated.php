<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
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
        if (Auth::guard($guard)->check()) {
            $redirect = Input::get('redirect');
            $roleId = (int)Auth::user()->RoleId;
            switch($roleId):
                case 2: //USER
                case 5:
                    return redirect($redirect?$redirect:'viewprofile');
                    break;
                case 1: //ADMIN
                    return redirect($redirect?$redirect:'departmentindex');
                    break;
                default:
                    Auth::logout();
                    return redirect($redirect?('/?redirect='.$redirect):'/');
            endswitch;
        }

        return $next($request);
    }
}
