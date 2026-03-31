<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetProviderPassword;
use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetLinkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function __construct(
        private SendPasswordResetLink $sendPasswordResetLink,
        private ResetProviderPassword $resetProviderPassword
    ) {
    }

    public function showLinkRequestForm()
    {
        return view('auth.reset-password');
    }

    public function sendResetLinkEmail(SendPasswordResetLinkRequest $request)
    {
        $result = $this->sendPasswordResetLink->execute($request->validated('email'));

        if (! $result['success']) {
            return back()->withErrors(['email' => $result['message']])->withInput();
        }

        return back()->with('success', $result['message']);
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password-form', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $result = $this->resetProviderPassword->execute($request->validated());

        if ($result['success']) {
            return redirect()
                ->route('signin')
                ->with('success', 'Password reset successful. Please sign in.');
        }

        return back()
            ->with('error', __($result['status']))
            ->withErrors(['email' => [__($result['status'])]])
            ->withInput(['email' => $result['user_email']]);
    }
}
