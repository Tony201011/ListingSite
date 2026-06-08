<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('signin');
        }

        if ($user->role === User::ROLE_ADMIN) {
            return redirect('/admin');
        }

        // Reviewer role is handled by ReviewerMode middleware; allow through here
        if ($user->role === User::ROLE_REVIEWER) {
            return $next($request);
        }

        if ($user->is_blocked) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account has been blocked.'], 403);
            }

            return redirect()->route('signin')->withErrors([
                'email' => 'Your account has been blocked.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please verify your email address.'], 403);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
