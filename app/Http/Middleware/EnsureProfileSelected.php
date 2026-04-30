<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Admins bypass this check
        if ($user->role === User::ROLE_ADMIN) {
            return $next($request);
        }

        // If the user has no profiles at all, let them through so they can create their first one
        $profileCount = $user->providerProfiles()->count();

        if ($profileCount === 0) {
            return $next($request);
        }

        // If no active profile is in session, require them to choose one
        $activeId = session('active_provider_profile_id');

        if (! $activeId || ! $user->providerProfiles()->where('id', $activeId)->exists()) {
            // Single-profile users are auto-selected so they are never blocked
            if ($profileCount === 1) {
                $profile = $user->providerProfiles()->first();

                if ($profile) {
                    session(['active_provider_profile_id' => $profile->id]);

                    return $next($request);
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'No profile selected.'], 403);
            }

            return redirect()->route('select-profile');
        }

        return $next($request);
    }
}
