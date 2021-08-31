<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class GuardMiddleware
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @param null $defaultGuard
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $defaultGuard = null)
    {
        if (in_array($defaultGuard, array_keys(config("auth.guards")))) {
            config(["auth.defaults.guard" => $defaultGuard]);
        }
        return $next($request);
    }

}
