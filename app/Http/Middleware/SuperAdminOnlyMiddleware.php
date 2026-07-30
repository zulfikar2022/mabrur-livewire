<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('guest.home');
        }
        if ($user->hasRole('super-admin')) {
            return $next($request);
        } else {
            return redirect()->route('guest.home');
        }

    }
}
