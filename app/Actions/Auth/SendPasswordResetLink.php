<?php

namespace App\Actions\Auth;

use App\Jobs\SendPasswordResetLinkJob;
use App\Services\Mail\ActiveMailSettingService;
use Illuminate\Support\Facades\Log;

class SendPasswordResetLink
{
    public function __construct(
        private ActiveMailSettingService $mailSettingService
    ) {
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function execute(string $email): array
    {
        $activeMailSetting = $this->mailSettingService->getActiveOrLatest();

        if (! $activeMailSetting) {
            Log::error('Password reset email queue failed: no mail setting found.', [
                'email' => $email,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to send reset email right now. Please try again later.',
            ];
        }

        SendPasswordResetLinkJob::dispatch($email, $activeMailSetting->id);

        Log::info('Password reset email queued', [
            'email' => $email,
            'mail_setting_id' => $activeMailSetting->id,
        ]);

        return [
            'success' => true,
            'message' => 'If your email exists in our system, a password reset link has been queued.',
        ];
    }
}
