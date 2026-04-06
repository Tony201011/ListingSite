<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Jobs\SendAdminProviderEmailJob;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'suburb' => $data['suburb'] ?? null,
            'password' => $data['password'],
            'role' => User::ROLE_PROVIDER,
            'is_blocked' => false,
        ]);

        // Auto-generate slug from profile name if not provided, before
        // saveRelationships() stores the providerProfile section data.
        $profileData = $this->data['providerProfile'] ?? [];
        if (blank($profileData['slug'] ?? null)) {
            $this->data['providerProfile']['slug'] = Str::slug($profileData['name'] ?? '');
        }

        return $user;
    }

    protected function afterCreate(): void
    {
        SendAdminProviderEmailJob::dispatch($this->record->id, 'created');
    }
}