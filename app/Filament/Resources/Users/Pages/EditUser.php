<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\ProfileMessage;
use App\Models\ProviderProfile;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(array_filter([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'suburb' => $data['suburb'] ?? null,
            'password' => filled($data['password'] ?? null) ? $data['password'] : null,
        ], fn ($value): bool => $value !== null));

        $profileData = $data['providerProfile'] ?? [];
        $existingProfile = $record->providerProfile;

        $profileName = filled($profileData['name'] ?? null)
            ? $profileData['name']
            : ($existingProfile?->name ?? ($data['name'] ?? ''));

        $requestedSlug = filled($profileData['slug'] ?? null)
            ? $profileData['slug']
            : $profileName;

        $baseSlug = Str::slug($requestedSlug);

        if (! filled($baseSlug)) {
            $baseSlug = $existingProfile?->slug ?: 'provider-' . $record->id;
        }

        $slug = $baseSlug;
        $index = 2;

        while (
            ProviderProfile::query()
                ->where('slug', $slug)
                ->when(
                    filled($existingProfile?->id),
                    fn (Builder $query): Builder => $query->where('id', '!=', $existingProfile->id),
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $record->id],
            [
                'name' => $profileName,
                'slug' => $slug,
                'description' => $profileData['description'] ?? $existingProfile?->description,
                'introduction_line' => $profileData['introduction_line'] ?? $existingProfile?->introduction_line,
                'profile_text' => $profileData['profile_text'] ?? $existingProfile?->profile_text,
                'age_group_id' => $profileData['age_group_id'] ?? $existingProfile?->age_group_id,
                'hair_color_id' => $profileData['hair_color_id'] ?? $existingProfile?->hair_color_id,
                'hair_length_id' => $profileData['hair_length_id'] ?? $existingProfile?->hair_length_id,
                'ethnicity_id' => $profileData['ethnicity_id'] ?? $existingProfile?->ethnicity_id,
                'body_type_id' => $profileData['body_type_id'] ?? $existingProfile?->body_type_id,
                'bust_size_id' => $profileData['bust_size_id'] ?? $existingProfile?->bust_size_id,
                'your_length_id' => $profileData['your_length_id'] ?? $existingProfile?->your_length_id,
                'availability' => $profileData['availability'] ?? $existingProfile?->availability,
                'contact_method' => $profileData['contact_method'] ?? $existingProfile?->contact_method,
                'phone_contact_preference' => $profileData['phone_contact_preference'] ?? $existingProfile?->phone_contact_preference,
                'time_waster_shield' => $profileData['time_waster_shield'] ?? $existingProfile?->time_waster_shield,
                'primary_identity' => $profileData['primary_identity'] ?? $existingProfile?->primary_identity ?? [],
                'attributes' => $profileData['attributes'] ?? $existingProfile?->attributes ?? [],
                'services_style' => $profileData['services_style'] ?? $existingProfile?->services_style ?? [],
                'services_provided' => $profileData['services_provided'] ?? $existingProfile?->services_provided ?? [],
                'twitter_handle' => $profileData['twitter_handle'] ?? $existingProfile?->twitter_handle,
                'website' => $profileData['website'] ?? $existingProfile?->website,
                'onlyfans_username' => $profileData['onlyfans_username'] ?? $existingProfile?->onlyfans_username,
                'phone' => $profileData['phone'] ?? $existingProfile?->phone,
                'whatsapp' => $profileData['whatsapp'] ?? $existingProfile?->whatsapp,
                'is_verified' => $profileData['is_verified'] ?? $existingProfile?->is_verified ?? false,
                'is_featured' => $profileData['is_featured'] ?? $existingProfile?->is_featured ?? false,
                'profile_status' => $profileData['profile_status'] ?? $existingProfile?->profile_status ?? 'pending',
            ],
        );

        $messageData = $data['profileMessage'] ?? [];

        if (array_key_exists('message', $messageData)) {
            ProfileMessage::query()->updateOrCreate(
                ['user_id' => $record->id],
                ['message' => $messageData['message'] ?? null],
            );
        }

        return $record->refresh();
    }
}
