<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\BuildAuthPageData;
use App\Actions\Auth\ChangeProviderEmail;
use App\Actions\Auth\ChangeProviderPassword;
use App\Actions\Auth\LogoutProvider;
use App\Actions\Auth\ResendProviderSignupOtp;
use App\Actions\Auth\ShowProviderOtpVerificationData;
use App\Actions\Auth\SigninProvider;
use App\Actions\Auth\SignupProvider;
use App\Actions\Auth\VerifyProviderSignupOtp;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderSigninRequest;
use App\Models\User;
use App\Services\SocialAuthService;
use App\Http\Requests\ProviderSignupRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProviderRegisterController extends Controller
{
    public function __construct(
        private BuildAuthPageData $buildAuthPageData,
        private SignupProvider $signupProvider,
        private SigninProvider $signinProvider,
        private ShowProviderOtpVerificationData $showProviderOtpVerificationData,
        private ResendProviderSignupOtp $resendProviderSignupOtp,
        private VerifyProviderSignupOtp $verifyProviderSignupOtp,
        private LogoutProvider $logoutProvider,
        private ChangeProviderPassword $changeProviderPassword,
        private ChangeProviderEmail $changeProviderEmail,
        private SocialAuthService $socialAuthService
    ) {}

    public function showSignupForm(): View
    {
        return view('auth.signup', $this->buildAuthPageData->execute());
    }

    public function signup(ProviderSignupRequest $request): RedirectResponse
    {
        return $this->signupProvider->execute($request->validated());
    }

    public function showSigninForm(): View
    {
        return view('auth.signin', $this->buildAuthPageData->execute());
    }

    public function signin(ProviderSigninRequest $request): RedirectResponse
    {
        return $this->signinProvider->execute($request);
    }

    public function otpVerificationForm(): View|RedirectResponse
    {
        $result = $this->showProviderOtpVerificationData->execute();

        if (! $result->isSuccess()) {
            return redirect('/signup')->withErrors($result->errors());
        }

        return view('auth.otp-verification', $result->data());
    }

    public function resendOtp(): JsonResponse
    {
        $result = $this->resendProviderSignupOtp->execute();

        return response()->json($result->toPayload(), $result->status());
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->verifyProviderSignupOtp->execute($request->validated('otp'));

        return response()->json($result->toPayload(), $result->status());
    }

    public function redirectToProvider(Request $request, string $provider): RedirectResponse
    {
        $provider = strtolower($provider);

        if (! in_array($provider, ['facebook', 'twitter', 'instagram'], true)) {
            abort(404);
        }

        if (Auth::check() && ($request->boolean('connect') || $request->input('intent') === 'connect')) {
            session()->put("social_oauth_intent.{$provider}", 'connect');
        } else {
            session()->forget("social_oauth_intent.{$provider}");
        }

        $redirectUrl = $this->socialAuthService->redirectUrl($provider)['url'];

        return redirect($redirectUrl);
    }

    public function handleProviderCallback(Request $request, string $provider): RedirectResponse
    {
        $provider = strtolower($provider);

        if (! in_array($provider, ['facebook', 'twitter', 'instagram'], true)) {
            abort(404);
        }

        $profile = $this->socialAuthService->handleCallback($provider, $request);
        $email = trim((string) ($profile['email'] ?? ''));

        if ($email === '') {
            $email = strtolower($provider).'_'.Str::random(8).'@example.invalid';
        }

        $connectIntent = session()->get("social_oauth_intent.{$provider}");

        if (Auth::check() && $connectIntent === 'connect') {
            $currentUser = Auth::user();

            if (! $currentUser instanceof User) {
                abort(401);
            }

            $existingProviderUser = User::query()->where('provider', $provider)->where('provider_id', (string) ($profile['id'] ?? ''))->first();

            if ($existingProviderUser && $existingProviderUser->getKey() !== $currentUser->getKey()) {
                session()->forget("social_oauth_intent.{$provider}");

                return redirect('/my-account')->withErrors([
                    'social' => 'This social account is already connected to another user.',
                ]);
            }

            $currentUser->forceFill([
                'provider' => $provider,
                'provider_id' => (string) ($profile['id'] ?? ''),
                'avatar_url' => $profile['avatar_url'] ?? $currentUser->avatar_url,
            ])->save();

            session()->forget("social_oauth_intent.{$provider}");

            return redirect('/my-account')->with('success', ucfirst($provider).' account connected successfully.');
        }

        $existingUser = User::query()->where('provider', $provider)->where('provider_id', (string) ($profile['id'] ?? ''))->first();

        if (! $existingUser) {
            $existingUser = User::query()->where('email', $email)->first();
        }

        if (! $existingUser) {
            $existingUser = User::create([
                'name' => (string) ($profile['name'] ?? explode('@', $email)[0]),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'role' => User::ROLE_PROVIDER,
                'provider' => $provider,
                'provider_id' => (string) ($profile['id'] ?? ''),
                'avatar_url' => $profile['avatar_url'] ?? null,
                'email_verified_at' => now(),
            ]);
        } else {
            $existingUser->forceFill([
                'provider' => $provider,
                'provider_id' => (string) ($profile['id'] ?? ''),
                'avatar_url' => $profile['avatar_url'] ?? $existingUser->avatar_url,
            ])->save();
        }

        Auth::login($existingUser, true);
        $request->session()->regenerate();
        session()->forget("social_oauth_intent.{$provider}");

        return redirect('/my-profiles');
    }

    public function changePassword(): View
    {
        return view('auth.change-password');
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->changeProviderPassword->execute(
            $request->user(),
            $request->validated('new_password')
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    public function changeEmail(): View
    {
        return view('auth.change-email');
    }

    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        return response()->json(
            $this->changeProviderEmail->execute(
                $request->user(),
                $request->validated('new_email')
            )
        );
    }

    public function deleteAccount(): View
    {
        return view('auth.delete-account');
    }
}
