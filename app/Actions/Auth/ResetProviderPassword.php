<?php

namespace App\Actions\Auth;

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
    ) {
    }

    /**
     * @return array{status: string, success: bool, user_email: string|null}
     */
    public function execute(array $validated): array
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

            return [
                'status' => $status,
                'success' => true,
                'user_email' => $validated['email'],
            ];
        }

        Log::warning('Password reset failed', [
            'email' => $validated['email'],
            'status' => $status,
        ]);

        return [
            'status' => $status,
            'success' => false,
            'user_email' => $validated['email'],
        ];
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
