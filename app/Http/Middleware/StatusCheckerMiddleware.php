<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StatusCheckerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = Auth::user()->status ?? 'disabled';

        if ($status !== 'active') {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Your account is disabled. Please contact support.');
        }

        return $next($request);
    }
}
