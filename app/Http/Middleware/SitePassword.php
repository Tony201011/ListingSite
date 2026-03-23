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

        // Defaults
        $configuredPassword = null;
        $configurationEnabled = false;

        if (Schema::hasTable('site_settings')) {
            $setting = SiteSetting::query()->latest('updated_at')->first();

            if ($setting) {
                $configuredPassword = $setting->site_password ?: null;
                $configurationEnabled = (bool) $setting->site_password_enabled;
            }
        }

        // Fallback to env only if DB password is not configured
        if (blank($configuredPassword)) {
            $envPassword = env('SITE_PASSWORD');
            $envEnabled = filter_var(env('SITE_PASSWORD_ENABLED', false), FILTER_VALIDATE_BOOL);

            if ($envEnabled && filled($envPassword)) {
                $configuredPassword = $envPassword;
                $configurationEnabled = true;
            }
        }

        // Protection is enabled only when both are true:
        // - password exists
        // - feature is enabled
        $protectionEnabled = $configurationEnabled && filled($configuredPassword);

        if ($protectionEnabled && $request->session()->get('site_access') !== true) {
            return redirect()->guest('/site-password');
        }

        return $next($request);
    }
}
