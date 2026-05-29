<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'password' => filled($data['password'] ?? null) ? $data['password'] : $record->password,
            'account_status' => $data['account_status'] ?? $record->account_status,
            'is_blocked' => (bool) ($data['is_blocked'] ?? false),
            'mobile_verified' => (bool) ($data['mobile_verified'] ?? false),
        ]);

        return $record->refresh();
    }
}

