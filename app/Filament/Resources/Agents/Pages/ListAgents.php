<?php

namespace App\Filament\Resources\Agents\Pages;

use App\Filament\Concerns\ChecksEmailSendingOutcome;
use App\Filament\Resources\Agents\AgentResource;
use App\Jobs\SendAgentAccountEmailJob;
use App\Models\SmtpSetting;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;

class ListAgents extends ManageRecords
{
    use ChecksEmailSendingOutcome;

    protected static string $resource = AgentResource::class;

    private string $plainPassword = '';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Agent')
                ->mutateFormDataUsing(function (array $data): array {
                    $this->plainPassword = $data['password'] ?? '';

                    return [
                        ...$data,
                        'role' => User::ROLE_AGENT,
                        'is_blocked' => false,
                        'must_change_password' => true,
                    ];
                })
                ->after(function (User $record): void {
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
                            'user_id' => $record->id,
                            'email' => $record->email,
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
                    SendAgentAccountEmailJob::dispatchSync($record->id, $plainPassword, $activeMailSetting->id);

                    if ($this->hasRecentEmailFailure($record->email, $dispatchedAt)) {
                        Notification::make()
                            ->title('Email sending failed')
                            ->body('Agent was created but one or more account emails failed to send. Check Email Logs for details.')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}
