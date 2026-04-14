<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SitePassword
{
    public function handle(Request $request, Closure $next): Response
    {
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

        if ($request->is('admin*') || $request->is('agent*')) {
            return $next($request);
        }

        $configuredPassword = null;
        $configurationEnabled = false;

        if (Schema::hasTable('site_settings')) {
            $setting = SiteSetting::query()->latest('updated_at')->first();

            if ($setting) {
                $configuredPassword = $setting->site_password ?: null;
                $configurationEnabled = (bool) $setting->site_password_enabled;
            }
        }

        if (blank($configuredPassword)) {
            $envPassword = env('SITE_PASSWORD');
            $envEnabled = filter_var(env('SITE_PASSWORD_ENABLED', false), FILTER_VALIDATE_BOOL);

            if ($envEnabled && filled($envPassword)) {
                $configuredPassword = $envPassword;
                $configurationEnabled = true;
            }
        }

        $protectionEnabled = $configurationEnabled && filled($configuredPassword);

        if ($protectionEnabled && $request->session()->get('site_access') !== true) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if ($user && $user->role === User::ROLE_ADMIN) {
                return $next($request);
            }

            return redirect()->guest('/site-password');
        }

        return $next($request);
    }
}
