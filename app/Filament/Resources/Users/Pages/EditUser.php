<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\ProfileMessage;
use App\Models\ProviderProfile;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $tab = $this->getCurrentTab();

        switch ($tab) {
            case '0':
            case 'overview':
            case 'Overview':
                $this->updateOverviewTab($record, $data);
                break;

            case '1':
            case 'attributes':
            case 'Attributes':
                $this->updateAttributesTab($record, $data);
                break;

            case '2':
            case 'contact':
            case 'Contact':
                $this->updateContactTab($record, $data);
                break;

            case '3':
            case 'images':
            case 'Images':
                $this->updateImagesTab($record, $data);
                break;

            case '4':
            case 'videos':
            case 'Videos':
                $this->updateVideosTab($record, $data);
                break;

            case '5':
            case 'rates':
            case 'Rates':
                $this->updateRatesTab($record, $data);
                break;

            case '6':
            case 'availability':
            case 'Availability':
                $this->updateAvailabilityTab($record, $data);
                break;

            case '7':
            case 'profile-message':
            case 'Profile Message':
                $this->updateProfileMessageTab($record, $data);
                break;

            default:
                $this->updateOverviewTab($record, $data);
                break;
        }

        return $record->refresh();
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Save Current Tab');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Current tab updated successfully.');
    }

    protected function getCurrentTab(): string
    {
        return (string) request()->query('tab', '0');
    }

    protected function updateOverviewTab(Model $record, array $data): void
    {
        $record->update(array_filter([
            'name' => $data['name'] ?? $record->name,
            'email' => $data['email'] ?? $record->email,
            'mobile' => $data['mobile'] ?? $record->mobile,
            'suburb' => $data['suburb'] ?? $record->suburb,
            'password' => filled($data['password'] ?? null) ? $data['password'] : null,
        ], fn ($value): bool => $value !== null));

        $profileData = $data['providerProfile'] ?? [];
        $existingProfile = $record->providerProfile;

        $profileName = filled($profileData['name'] ?? null)
            ? $profileData['name']
            : ($existingProfile?->name ?? ($data['name'] ?? $record->name));

        $requestedSlug = filled($profileData['slug'] ?? null)
            ? $profileData['slug']
            : $profileName;

        $baseSlug = Str::slug($requestedSlug);

        if (! filled($baseSlug)) {
            $baseSlug = $existingProfile?->slug ?: 'provider-'.$record->id;
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
            $slug = $baseSlug.'-'.$index;
            $index++;
        }

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $record->id],
            [
                'name' => $profileName,
                'slug' => $slug,
                'description' => array_key_exists('description', $profileData)
                    ? ($profileData['description'] ?? '')
                    : ($existingProfile?->description ?? ''),
                'introduction_line' => array_key_exists('introduction_line', $profileData)
                    ? ($profileData['introduction_line'] ?? '')
                    : ($existingProfile?->introduction_line ?? ''),
                'profile_text' => array_key_exists('profile_text', $profileData)
                    ? ($profileData['profile_text'] ?? '')
                    : ($existingProfile?->profile_text ?? ''),
                'is_verified' => $profileData['is_verified'] ?? $existingProfile?->is_verified ?? false,
                'is_featured' => $profileData['is_featured'] ?? $existingProfile?->is_featured ?? false,
                'profile_status' => $profileData['profile_status'] ?? $existingProfile?->profile_status ?? 'pending',
            ],
        );
    }

    protected function updateAttributesTab(Model $record, array $data): void
    {
        $profileData = $data['providerProfile'] ?? [];
        $existingProfile = $record->providerProfile;

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $record->id],
            [
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
            ],
        );
    }

    protected function updateContactTab(Model $record, array $data): void
    {
        $profileData = $data['providerProfile'] ?? [];
        $existingProfile = $record->providerProfile;

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $record->id],
            [
                'twitter_handle' => $profileData['twitter_handle'] ?? $existingProfile?->twitter_handle,
                'website' => $profileData['website'] ?? $existingProfile?->website,
                'onlyfans_username' => $profileData['onlyfans_username'] ?? $existingProfile?->onlyfans_username,
                'phone' => $profileData['phone'] ?? $existingProfile?->phone,
                'whatsapp' => $profileData['whatsapp'] ?? $existingProfile?->whatsapp,
            ],
        );
    }

    protected function updateImagesTab(Model $record, array $data): void
    {
        $this->syncHasManyRelation(
            $record->profileImages(),
            $data['profileImages'] ?? [],
            ['image_path', 'thumbnail_path', 'is_primary']
        );
    }

    protected function updateVideosTab(Model $record, array $data): void
    {
        $this->syncHasManyRelation(
            $record->userVideos(),
            $data['userVideos'] ?? [],
            ['original_name', 'video_path']
        );
    }

    protected function updateRatesTab(Model $record, array $data): void
    {
        $this->syncHasManyRelation(
            $record->rates(),
            $data['rates'] ?? [],
            ['description', 'incall', 'outcall', 'extra']
        );
    }

    protected function updateAvailabilityTab(Model $record, array $data): void
    {
        $this->syncHasManyRelation(
            $record->availabilities(),
            $data['availabilities'] ?? [],
            ['day', 'enabled', 'from_time', 'to_time', 'till_late', 'all_day', 'by_appointment']
        );
    }

    protected function updateProfileMessageTab(Model $record, array $data): void
    {
        $messageData = $data['profileMessage'] ?? [];

        if (array_key_exists('message', $messageData)) {
            ProfileMessage::query()->updateOrCreate(
                ['user_id' => $record->id],
                ['message' => $messageData['message'] ?? ''],
            );
        }
    }

    protected function syncHasManyRelation(HasMany $relation, array $items, array $allowedFields): void
    {
        $related = $relation->getRelated();
        $keyName = $related->getKeyName();

        $existingIds = $relation->pluck($keyName)->map(fn ($id) => (int) $id)->all();

        $incomingIds = collect($items)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $idsToDelete = array_diff($existingIds, $incomingIds);

        if (! empty($idsToDelete)) {
            $relation->whereIn($keyName, $idsToDelete)->delete();
        }

        foreach ($items as $item) {
            $attributes = Arr::only($item, $allowedFields);

            if (! empty($item['id'])) {
                $relation->updateOrCreate(
                    [$keyName => $item['id']],
                    $attributes,
                );
            } else {
                $relation->create($attributes);
            }
        }
    }
}
