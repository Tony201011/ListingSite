<?php

namespace App\Filament\Resources\Agents\Pages;

use App\Filament\Resources\Agents\AgentResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateAgent extends CreateRecord
{
    protected static string $resource = AgentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'role' => User::ROLE_AGENT,
            'is_blocked' => false,
        ];
    }
}