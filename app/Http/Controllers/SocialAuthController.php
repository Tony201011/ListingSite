<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\SocialLoginSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * @var array<string, string>
     */
    private array $driverMap = [
        'google' => 'google',
        'facebook' => 'facebook',
        'twitter' => 'twitter-oauth-2',
    ];

    public function showLogin(): \Illuminate\View\View
    {
        $providers = SocialLoginSetting::query()
            ->where('is_enabled', true)
            ->whereIn('provider', array_keys($this->driverMap))
            ->orderByRaw("FIELD(provider, 'google', 'facebook', 'twitter')")
            ->get(['provider']);

        return view('auth.social-login', [
            'providers' => $providers,
        ]);
    }

    public function redirect(string $provider): RedirectResponse
    {
        $setting = $this->getProviderSettingOrFail($provider);

        $driver = $this->driverMap[$provider] ?? null;

        abort_unless(filled($driver), 404);

        $this->configureProvider($provider, $setting);

        return Socialite::driver($driver)
            ->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $setting = $this->getProviderSettingOrFail($provider);

        $driver = $this->driverMap[$provider] ?? null;

        abort_unless(filled($driver), 404);

        $this->configureProvider($provider, $setting);

        $socialUser = Socialite::driver($driver)->user();

        $account = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', (string) $socialUser->getId())
            ->first();

        if ($account) {
            Auth::login($account->user, remember: true);

            return redirect()->intended('/');
        }

        $email = $socialUser->getEmail();

        $user = null;

        if (filled($email)) {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            $generatedEmail = filled($email)
                ? $email
                : Str::slug($provider . '-' . $socialUser->getId()) . '@social.local';

            $user = User::query()->create([
                'name' => $socialUser->getName() ?: ucfirst($provider) . ' User',
                'email' => $generatedEmail,
                'password' => Str::random(40),
                'profile_image' => $socialUser->getAvatar(),
                'role' => User::ROLE_PROVIDER,
            ]);
        }

        SocialAccount::query()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_user_id' => (string) $socialUser->getId(),
            ],
            [
                'user_id' => $user->id,
                'provider_email' => $socialUser->getEmail(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'token_expires_at' => filled($socialUser->expiresIn) ? now()->addSeconds((int) $socialUser->expiresIn) : null,
                'avatar' => $socialUser->getAvatar(),
            ],
        );

        Auth::login($user, remember: true);

        return redirect()->intended('/');
    }

    private function getProviderSettingOrFail(string $provider): SocialLoginSetting
    {
        abort_unless(array_key_exists($provider, $this->driverMap), 404);

        $setting = SocialLoginSetting::query()
            ->where('provider', $provider)
            ->where('is_enabled', true)
            ->first();

        abort_unless(
            $setting && filled($setting->client_id) && filled($setting->client_secret) && filled($setting->redirect_url),
            404,
        );

        return $setting;
    }

    private function configureProvider(string $provider, SocialLoginSetting $setting): void
    {
        config([
            "services.{$provider}.client_id" => $setting->client_id,
            "services.{$provider}.client_secret" => $setting->client_secret,
            "services.{$provider}.redirect" => $setting->redirect_url,
        ]);
    }
}