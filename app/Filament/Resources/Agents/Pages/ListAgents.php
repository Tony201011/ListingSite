<?php

namespace App\Filament\Resources\Agents\Pages;

use App\Filament\Resources\Agents\AgentResource;
use App\Jobs\SendAgentAccountEmailJob;
use App\Models\SmtpSetting;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;

class ListAgents extends ManageRecords
{
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

                        $this->plainPassword = '';

                        return;
                    }

                    $plainPassword = $this->plainPassword;
                    $this->plainPassword = '';

                    SendAgentAccountEmailJob::dispatchSync($record->id, $plainPassword, $activeMailSetting->id);
                }),
        ];
    }
}
