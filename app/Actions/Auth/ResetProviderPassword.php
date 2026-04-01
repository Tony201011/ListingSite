<?php

namespace App\Actions\Auth;

use App\Actions\Support\ActionResult;
use App\Jobs\SendPasswordResetSuccessEmailJob;
use App\Services\Mail\ActiveMailSettingService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetProviderPassword
{
    public function __construct(
        private ActiveMailSettingService $mailSettingService
    ) {}

    public function execute(array $validated): ActionResult
    {
        $resetUser = null;

        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $validated['token'],
            ],
            function ($user, string $password) use (&$resetUser): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $resetUser = $user;

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET && $resetUser) {
            $this->sendSuccessEmail($resetUser);

            Log::info('Password reset successful', ['email' => $validated['email']]);

            return ActionResult::success(
                [
                    'reset_status' => $status,
                    'user_email' => $validated['email'],
                ],
                'Password reset successful.'
            );
        }

        Log::warning('Password reset failed', [
            'email' => $validated['email'],
            'status' => $status,
        ]);

        return ActionResult::validationError(
            __($status),
            ['email' => [__($status)]],
            422
        );
    }

    private function sendSuccessEmail(mixed $user): void
    {
        $activeMailSetting = $this->mailSettingService->getActiveOrLatest();

        if ($activeMailSetting) {
            SendPasswordResetSuccessEmailJob::dispatch($user->id, $activeMailSetting->id);

            Log::info('Password reset success email queued', [
                'email' => $user->email,
                'mail_setting_id' => $activeMailSetting->id,
            ]);
        } else {
            Log::warning('Password reset success email not queued: no mail setting found.', [
                'email' => $user->email,
            ]);
        }
    }
}
