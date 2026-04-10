<?php

namespace App\Filament\Resources\Agents\Pages;

use App\Filament\Concerns\ChecksEmailSendingOutcome;
use App\Filament\Resources\Agents\AgentResource;
use App\Jobs\SendAgentAccountEmailJob;
use App\Models\SmtpSetting;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateAgent extends CreateRecord
{
    use ChecksEmailSendingOutcome;

    protected static string $resource = AgentResource::class;

    protected string $plainPassword = '';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainPassword = $data['password'] ?? '';

        return [
            ...$data,
            'role' => User::ROLE_AGENT,
            'is_blocked' => false,
            'must_change_password' => true,
        ];
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if (! $this->plainPassword) {
            return;
        }

        $activeMailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first()
            ?? SmtpSetting::query()->latest('updated_at')->first();

        if (! $activeMailSetting) {
            Log::error('Agent account email skipped: no mail setting found.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            Notification::make()
                ->title('Email not sent')
                ->body('Agent was created but the account email could not be sent: no mail setting found.')
                ->warning()
                ->send();

            return;
        }

        $plainPassword = $this->plainPassword;
        $this->plainPassword = '';

        $dispatchedAt = now();
        SendAgentAccountEmailJob::dispatchSync($user->id, $plainPassword, $activeMailSetting->id);

        if ($this->hasRecentEmailFailure($user->email, $dispatchedAt)) {
            Notification::make()
                ->title('Email sending failed')
                ->body('Agent was created but one or more account emails failed to send. Check Email Logs for details.')
                ->warning()
                ->send();
        }
    }
}
