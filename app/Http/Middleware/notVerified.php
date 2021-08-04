<?php

namespace App\Http\Middleware;

use App\Models\Note;
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
        // todo Property accessed via magic method
        if($request->note->private && $request->user() && !$request->user()->hasVerifiedEmail()) {
            return redirect(route('verification.notice'));
        } elseif(!$request->user()) {
            return redirect(route('login'));
        }

        return $next($request);
    }
}
