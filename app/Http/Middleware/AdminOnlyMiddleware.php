<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('home');
        }
        // here in this middleware the user is already authenticated and we just need to check if the user is admin or not. If the user is not admin then we will return the home page and if the user is admin then we will allow the user to access the admin panel.
        if ($user->hasRole('admin')) {
            return $next($request);
        } else {
            return redirect()->route('home');
        }

    }
}
