<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthButNotVerified
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
        if (!$request->user()) {
            if ($request->note->private) {
                return redirect(route('login'));
            }
        } else {
            if (!$request->user()->hasVerifiedEmail() && $request->note->private) {
                return redirect(route('verification.notice'));
            }
        }

        return $next($request);
    }
}
