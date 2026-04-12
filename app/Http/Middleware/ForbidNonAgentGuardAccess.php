<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForbidNonAgentGuardAccess implements AuthenticatesRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is authenticated on the web guard (e.g. an admin or provider),
        // they must not be allowed into the agent panel.
        if (Auth::guard('web')->check()) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
