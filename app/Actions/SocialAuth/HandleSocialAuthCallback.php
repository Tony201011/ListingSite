<?php

namespace App\Actions\SocialAuth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class HandleSocialAuthCallback
{
    public function __construct(
        private ResolveSocialProviderSetting $resolveSocialProviderSetting,
        private ConfigureSocialProvider $configureSocialProvider
    ) {}

    public function execute(string $provider): RedirectResponse
    {
        $resolved = $this->resolveSocialProviderSetting->execute($provider);

        $this->configureSocialProvider->execute(
            $resolved['provider'],
            $resolved['setting']
        );

        $socialUser = Socialite::driver($resolved['driver'])->user();

        $account = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', (string) $socialUser->getId())
            ->first();

        if ($account) {
            Auth::login($account->user, true);

            return redirect()->intended('/');
        }

        $email = $socialUser->getEmail();
        $user = null;

        if (filled($email)) {
            $user = User::query()
                ->where('email', $email)
                ->first();
        }

        if (! $user) {
            $generatedEmail = filled($email)
                ? $email
                : Str::slug($provider.'-'.$socialUser->getId()).'@social.local';

            $user = User::query()->create([
                'name' => $socialUser->getName() ?: ucfirst($provider).' User',
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
                'token_expires_at' => filled($socialUser->expiresIn)
                    ? now()->addSeconds((int) $socialUser->expiresIn)
                    : null,
                'avatar' => $socialUser->getAvatar(),
            ]
        );

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
