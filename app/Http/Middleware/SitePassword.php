<?php

namespace App\Http\Middleware;

use App\Actions\SiteAccess\VerifySitePassword;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SitePassword
{
    public function __construct(
        private VerifySitePassword $verifySitePassword
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->is('site-password') ||
            $request->is('site-password/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('livewire/*') ||
            $request->is('favicon.ico') ||
            $request->is('robots.txt') ||
            $request->is('sitemap.xml') ||
            $request->is('sitemaps/*')
        ) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (is_string($routeName) && str_starts_with($routeName, 'filament.admin.')) {
            return $next($request);
        }

        if ($request->is('admin*')) {
            return $next($request);
        }

        $config = $this->verifySitePassword->getResolvedConfig();
        $configurationEnabled = (bool) ($config['enabled'] ?? false);
        $configuredPassword = $config['password'] ?? null;

        $protectionEnabled = $configurationEnabled;

        if ($protectionEnabled && $request->session()->get('site_access') === true) {
            $currentFingerprint = $this->verifySitePassword->getPasswordFingerprint();
            $sessionFingerprint = $request->session()->get('site_access_password_fingerprint');

            if (
                filled($currentFingerprint) &&
                is_string($sessionFingerprint) &&
                ! hash_equals($currentFingerprint, $sessionFingerprint)
            ) {
                $request->session()->forget(['site_access', 'site_access_password_fingerprint']);
            }
        }

        if ($protectionEnabled && $request->session()->get('site_access') !== true) {
            /** @var User|null $user */
            $user = Auth::user();

            if ($user && $user->role === User::ROLE_ADMIN) {
                return $next($request);
            }

            return redirect()->guest('/site-password');
        }

        return $next($request);
    }
}
