<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

        // Check if site password protection is enabled in settings
        if (Schema::hasTable('site_settings')) {
            $setting = SiteSetting::query()->latest('updated_at')->first();

            if ($setting && $setting->site_password_enabled) {
                // If site password enabled and session doesn't have access, redirect to password page
                if (! $request->session()->has('site_access')) {
                    return redirect('/site-password');
                }
            }
        }

        return $next($request);
    }
}
