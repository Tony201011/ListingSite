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
        ], fn ($value): bool => $value !== null));

        $profileData = $data['providerProfile'] ?? [];

        // Load existing profile so we can fall back to its values for fields that
        // may be missing from $profileData.  This happens when the form contains
        // multiple Section components that all use ->relationship('providerProfile'):
        // Filament v5 may only populate $data['providerProfile'] with the fields
        // belonging to the *last* such section, leaving earlier sections' fields
        // (name, slug, introduction_line, profile_text …) absent.
        $existingProfile = $record->providerProfile;

        // Determine the profile name, falling back to existing values so the NOT
        // NULL constraint on provider_profiles.name is never violated.  The chain
        // is: submitted form value → existing profile name → user account name.
        $profileName = filled($profileData['name'] ?? null)
            ? $profileData['name']
            : ($existingProfile?->name ?? ($data['name'] ?? ''));

        $baseSlug = Str::slug(($profileData['slug'] ?? null) ?: $profileName);

        // If the slug resolves to an empty string (e.g. only non-ASCII characters),
        // fall back to the existing slug so we never attempt to save an empty value.
        if (! filled($baseSlug)) {
            $baseSlug = $existingProfile?->slug ?? '';
        }

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
                'name' => $profileName,
                'slug' => $slug,
                'description' => $profileData['description'] ?? $existingProfile?->description,
                'introduction_line' => $profileData['introduction_line'] ?? $existingProfile?->introduction_line,
                'profile_text' => $profileData['profile_text'] ?? $existingProfile?->profile_text,
                'age_group_id' => $profileData['age_group_id'] ?? null,
                'hair_color_id' => $profileData['hair_color_id'] ?? null,
                'hair_length_id' => $profileData['hair_length_id'] ?? null,
                'ethnicity_id' => $profileData['ethnicity_id'] ?? null,
                'body_type_id' => $profileData['body_type_id'] ?? null,
                'bust_size_id' => $profileData['bust_size_id'] ?? null,
                'your_length_id' => $profileData['your_length_id'] ?? null,
                'availability' => $profileData['availability'] ?? null,
                'contact_method' => $profileData['contact_method'] ?? null,
                'phone_contact_preference' => $profileData['phone_contact_preference'] ?? null,
                'time_waster_shield' => $profileData['time_waster_shield'] ?? null,
                'primary_identity' => $profileData['primary_identity'] ?? [],
                'attributes' => $profileData['attributes'] ?? [],
                'services_style' => $profileData['services_style'] ?? [],
                'services_provided' => $profileData['services_provided'] ?? [],
                'twitter_handle' => $profileData['twitter_handle'] ?? null,
                'website' => $profileData['website'] ?? null,
                'onlyfans_username' => $profileData['onlyfans_username'] ?? null,
                'phone' => $profileData['phone'] ?? null,
                'whatsapp' => $profileData['whatsapp'] ?? null,
                'is_verified' => $profileData['is_verified'] ?? false,
                'is_featured' => $profileData['is_featured'] ?? false,
                'profile_status' => $profileData['profile_status'] ?? 'pending',
            ],
        );

        $messageData = $data['profileMessage'] ?? [];

        ProfileMessage::query()->updateOrCreate(
            ['user_id' => $record->id],
            ['message' => $messageData['message'] ?? null],
        );

        return $record->refresh();
    }
}
