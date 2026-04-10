<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Try to authenticate via the web guard first, then the agent guard.
        foreach (['web', 'agent'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);
                break;
            }
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('signin');
        }

        if ($user->role === User::ROLE_ADMIN) {
            return redirect('/admin');
        }

        if ($user->role === User::ROLE_AGENT) {
            return redirect('/agent');
        }

        return $next($request);
    }
}
