<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SitePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow password page
        if ($request->is('site-password') || $request->is('site-password/*')) {
            return $next($request);
        }

        // Allow Filament admin
        if ($request->is('admin*')) {
            return $next($request);
        }

        // Check if password entered
        if (!$request->session()->has('site_access')) {
            return redirect('/site-password');
        }

        return $next($request);
    }
}
