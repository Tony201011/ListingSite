<?php

namespace App\Filament\Agent\Resources\ManagedProfileResource\Pages;

use App\Filament\Agent\Resources\ManagedProfileResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateManagedProfile extends CreateRecord
{
    protected static string $resource = ManagedProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Filament::auth()->user()?->role === User::ROLE_ADMIN) {
            if (blank($data['agent_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'agent_id' => 'Please select an agent account.',
                ]);
            }
        } else {
            $data['agent_id'] = Filament::auth()->id();
        }

        $data['profile_status'] = 'pending';

        return $data;
    }
}
