<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Concerns\ReviewerReadOnly;
use App\Filament\Resources\Accounts\AccountResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAccount extends CreateRecord
{
    use ReviewerReadOnly;
    protected static string $resource = AccountResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'password' => $data['password'],
            'role' => User::ROLE_PROVIDER,
            'account_status' => $data['account_status'] ?? 'active',
            'is_blocked' => (bool) ($data['is_blocked'] ?? false),
            'mobile_verified' => (bool) ($data['mobile_verified'] ?? false),
            'email_verified_at' => now(),
        ]);
    }
}

