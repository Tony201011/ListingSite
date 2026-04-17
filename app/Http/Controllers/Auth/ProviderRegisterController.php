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
use App\Http\Requests\ProviderSignupRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        private ChangeProviderEmail $changeProviderEmail
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

    public function logout(Request $request): RedirectResponse
    {
        return $this->logoutProvider->execute($request);
    }

    public function changePassword(): View
    {
        return view('auth.change-password');
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        return response()->json(
            $this->changeProviderPassword->execute(
                $request->user(),
                $request->validated('new_password')
            )
        );
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
