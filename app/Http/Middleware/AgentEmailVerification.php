<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentEmailVerification
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            if (! $request->routeIs('verification.notice')) {
                return redirect(route('verification.notice'));
            }
        }

        return $next($request);
    }
}
