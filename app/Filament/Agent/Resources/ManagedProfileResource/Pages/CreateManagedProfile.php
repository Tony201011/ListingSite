<?php

namespace App\Filament\Agent\Resources\ManagedProfileResource\Pages;

use App\Filament\Agent\Resources\ManagedProfileResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateManagedProfile extends CreateRecord
{
    protected static string $resource = ManagedProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['agent_id'] = Filament::auth()->id();
        $data['profile_status'] = 'pending';

        return $data;
    }
}
