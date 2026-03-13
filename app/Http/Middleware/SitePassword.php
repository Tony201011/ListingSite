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
        // Allow password gate endpoints and static/framework assets required to render pages.
        if (
            $request->is('site-password') ||
            $request->is('site-password/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('livewire/*') ||
            $request->is('favicon.ico') ||
            $request->is('robots.txt')
        ) {
            return $next($request);
        }

        // Allow Filament admin
        if ($request->is('admin*')) {
            return $next($request);
        }

        // Force protection ON whenever a site password exists (DB or env),
        // regardless of the admin toggle.
        $protectionEnabled = false;
        $configuredPassword = null;

        if (Schema::hasTable('site_settings')) {
            $setting = SiteSetting::query()->latest('updated_at')->first();

            if ($setting) {
                $configuredPassword = $setting->site_password ?: null;
            }
        }

        if (! filled($configuredPassword)) {
            $configuredPassword = env('SITE_PASSWORD');
        }

        if (filled($configuredPassword)) {
            $protectionEnabled = true;
        }

        if ($protectionEnabled && $request->session()->get('site_access') !== true) {
            return redirect()->guest('/site-password');
        }

        return $next($request);
    }
}
