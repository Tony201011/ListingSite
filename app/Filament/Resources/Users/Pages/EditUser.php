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
            'introduction_line' => $profile?->introduction_line,
            'profile_text' => $profile?->profile_text,
            'age_group' => $profile?->age_group_id,
            'hair_color' => $profile?->hair_color_id,
            'hair_length' => $profile?->hair_length_id,
            'ethnicity' => $profile?->ethnicity_id,
            'body_type' => $profile?->body_type_id,
            'bust_size' => $profile?->bust_size_id,
            'your_length' => $profile?->your_length_id,
            'availability' => $profile?->availability,
            'contact_method' => $profile?->contact_method,
            'phone_contact' => $profile?->phone_contact_preference,
            'time_waster' => $profile?->time_waster_shield,
            'primary_identity' => $profile?->primary_identity ?? [],
            'attributes' => $profile?->attributes ?? [],
            'services_style' => $profile?->services_style ?? [],
            'services_provided' => $profile?->services_provided ?? [],
            'twitter_handle' => $profile?->twitter_handle,
            'website' => $profile?->website,
            'onlyfans_username' => $profile?->onlyfans_username,
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
        $record->update(array_filter([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'suburb' => $data['suburb'] ?? null,
            'password' => $data['password'] ?? null,
        ], fn ($value): bool => $value !== null));

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
            $slug = $baseSlug.'-'.$index;
            $index++;
        }

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $record->id],
            [
                'name' => $data['profile_name'],
                'slug' => $slug,
                'age' => $data['profile_age'] ?? null,
                'description' => $data['profile_description'] ?? null,
                'introduction_line' => $data['introduction_line'] ?? null,
                'profile_text' => $data['profile_text'] ?? null,
                'age_group_id' => $data['age_group'] ?? null,
                'hair_color_id' => $data['hair_color'] ?? null,
                'hair_length_id' => $data['hair_length'] ?? null,
                'ethnicity_id' => $data['ethnicity'] ?? null,
                'body_type_id' => $data['body_type'] ?? null,
                'bust_size_id' => $data['bust_size'] ?? null,
                'your_length_id' => $data['your_length'] ?? null,
                'availability' => $data['availability'] ?? null,
                'contact_method' => $data['contact_method'] ?? null,
                'phone_contact_preference' => $data['phone_contact'] ?? null,
                'time_waster_shield' => $data['time_waster'] ?? null,
                'primary_identity' => $data['primary_identity'] ?? [],
                'attributes' => $data['attributes'] ?? [],
                'services_style' => $data['services_style'] ?? [],
                'services_provided' => $data['services_provided'] ?? [],
                'twitter_handle' => $data['twitter_handle'] ?? null,
                'website' => $data['website'] ?? null,
                'onlyfans_username' => $data['onlyfans_username'] ?? null,
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
