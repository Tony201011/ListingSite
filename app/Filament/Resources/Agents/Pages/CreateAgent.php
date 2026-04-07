<?php

namespace App\Filament\Resources\Agents\Pages;

use App\Filament\Resources\Agents\AgentResource;
use App\Jobs\SendAgentAccountEmailJob;
use App\Models\SmtpSetting;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateAgent extends CreateRecord
{
    protected static string $resource = AgentResource::class;

    protected string $plainPassword = '';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainPassword = $data['password'] ?? '';

        return [
            ...$data,
            'role' => User::ROLE_AGENT,
            'is_blocked' => false,
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

            return;
        }

        SendAgentAccountEmailJob::dispatchSync($user->id, $this->plainPassword, $activeMailSetting->id);
    }
}