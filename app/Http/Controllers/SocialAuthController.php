<?php

namespace App\Http\Controllers;

use App\Actions\SocialAuth\GetEnabledSocialProviders;
use App\Actions\SocialAuth\HandleSocialAuthCallback;
use App\Actions\SocialAuth\RedirectToSocialProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SocialAuthController extends Controller
{
    public function __construct(
        private GetEnabledSocialProviders $getEnabledSocialProviders,
        private RedirectToSocialProvider $redirectToSocialProvider,
        private HandleSocialAuthCallback $handleSocialAuthCallback
    ) {
    }

    public function showLogin(): View
    {
        return view('auth.social-login', [
            'providers' => $this->getEnabledSocialProviders->execute(),
        ]);
    }

    public function redirect(string $provider): RedirectResponse
    {
        return $this->redirectToSocialProvider->execute($provider);
    }

    public function callback(string $provider): RedirectResponse
    {
        return $this->handleSocialAuthCallback->execute($provider);
    }
}
