<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Concerns\ReviewerReadOnly;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;

class EditAccount extends EditRecord
{
    use ReviewerReadOnly;
    protected static string $resource = AccountResource::class;

    protected function getContentMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'account_status' => $data['account_status'] ?? $record->account_status,
            'is_blocked' => (bool) ($data['is_blocked'] ?? false),
            'mobile_verified' => (bool) ($data['mobile_verified'] ?? false),
        ]);

        return $record->refresh();
    }
}
