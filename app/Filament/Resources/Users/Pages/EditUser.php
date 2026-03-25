<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\ProviderProfile;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $profile = $this->record->providerProfile;

        return [
            ...$data,
            'profile_name' => $profile?->name,
            'profile_slug' => $profile?->slug,
            'profile_age' => $profile?->age,
            'profile_description' => $profile?->description,
            'phone' => $profile?->phone,
            'whatsapp' => $profile?->whatsapp,
            'is_verified' => $profile?->is_verified ?? false,
            'is_featured' => $profile?->is_featured ?? false,
            'profile_status' => $profile?->profile_status ?? 'pending',
            'expires_at' => $profile?->expires_at,
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $baseSlug = Str::slug($data['profile_slug'] ?: $data['profile_name']);
        $slug = $baseSlug;
        $index = 2;

        while (
            ProviderProfile::query()
                ->where('slug', $slug)
                ->when(
                    filled($record->providerProfile?->id),
                    fn (Builder $query): Builder => $query->where('id', '!=', $record->providerProfile?->id),
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $record->id],
            [
                'name' => $data['profile_name'],
                'slug' => $slug,
                'age' => $data['profile_age'] ?? null,
                'description' => $data['profile_description'] ?? null,
                'phone' => $data['phone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'is_verified' => $data['is_verified'] ?? false,
                'is_featured' => $data['is_featured'] ?? false,
                'profile_status' => $data['profile_status'] ?? 'pending',
                'expires_at' => $data['expires_at'] ?? null,
            ],
        );

        return $record->refresh();
    }
}
