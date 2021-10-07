<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class notVerified
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
//        dd($request);
        if (!$request->user()) {
            if($request->note->private) {
                return redirect(route('login'));
            }
        } else {
            if (!$request->user()->hasVerifiedEmail() && $request->note->private) {
                return redirect(route('verification.notice'));
            }
        }


//        if ($request->note->private && $request->user() && !$request->user()->hasVerifiedEmail()) {
//            return redirect(route('verification.notice'));
//        } elseif (!$request->note->private) {
//            return $next($request);
//        } else {
//            return redirect(route('login'));
//        }

        return $next($request);
    }
}
