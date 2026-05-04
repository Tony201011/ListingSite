<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\LoadsProviderMediaBeforeFill;
use App\Filament\Resources\Users\UserResource;
use App\Models\ProfileMessage;
use App\Models\ProviderProfile;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    use LoadsProviderMediaBeforeFill;

    protected static string $resource = UserResource::class;

    /**
     * Add a "Switch Profile" header action when the provider has more than one
     * profile, so the admin can navigate directly to a specific profile's edit view.
     * Each ProviderProfile has its own URL, so switching jumps to the selected
     * profile's edit page directly.
     */
    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $profiles = $record->user?->providerProfiles ?? collect();

        if ($profiles->count() <= 1) {
            return [];
        }

        return [
            Action::make('switchProfile')
                ->label('Switch Profile')
                ->icon('heroicon-o-arrows-right-left')
                ->color('gray')
                ->modalHeading('Select Profile to Edit')
                ->modalDescription('Changes are saved to the selected profile only.')
                ->form([
                    Select::make('profile_id')
                        ->label('Profile')
                        ->options(
                            $profiles->mapWithKeys(fn ($p) => [
                                $p->id => "#{$p->id}: {$p->name} ({$p->profile_status})",
                            ])->all()
                        )
                        ->default($record->id)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    redirect()->to(
                        static::getResource()::getUrl('edit', ['record' => (int) $data['profile_id']])
                    );
                }),
        ];
    }

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
        $tab = (string) request()->query('tab', '0');

        // Filament 5 persists tabs as "{label}::{statePath}::{type}" (e.g. "availability::data::tab").
        // Extract only the label portion so it matches the switch cases below.
        if (str_contains($tab, '::')) {
            return Str::before($tab, '::');
        }

        return $tab;
    }

    protected function updateOverviewTab(Model $record, array $data): void
    {
        // User account fields come from the 'user' relationship section in the form.
        $userData = $data['user'] ?? [];

        $userUpdates = array_filter([
            'name' => $userData['name'] ?? null,
            'email' => $userData['email'] ?? null,
            'mobile' => $userData['mobile'] ?? null,
            'password' => filled($userData['password'] ?? null) ? $userData['password'] : null,
        ], fn ($value): bool => $value !== null);

        if (! empty($userUpdates) && $record->user) {
            $record->user->update($userUpdates);
        }

        // Profile fields are at the top level of $data (no relationship wrapper).
        $profileName = filled($data['name'] ?? null) ? $data['name'] : $record->name;
        $requestedSlug = filled($data['slug'] ?? null) ? $data['slug'] : $profileName;
        $baseSlug = Str::slug($requestedSlug);

        if (! filled($baseSlug)) {
            $baseSlug = $record->slug ?: 'provider-'.$record->id;
        }

        $slug = $baseSlug;
        $index = 2;

        while (
            ProviderProfile::query()
                ->where('slug', $slug)
                ->where('id', '!=', $record->id)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$index;
            $index++;
        }

        $record->update([
            'name' => $profileName,
            'slug' => $slug,
            'suburb' => $data['suburb'] ?? $record->suburb,
            'description' => array_key_exists('description', $data)
                ? ($data['description'] ?? '')
                : ($record->description ?? ''),
            'introduction_line' => array_key_exists('introduction_line', $data)
                ? ($data['introduction_line'] ?? '')
                : ($record->introduction_line ?? ''),
            'profile_text' => array_key_exists('profile_text', $data)
                ? ($data['profile_text'] ?? '')
                : ($record->profile_text ?? ''),
            'is_verified' => $data['is_verified'] ?? $record->is_verified ?? false,
            'is_featured' => $data['is_featured'] ?? $record->is_featured ?? false,
            'profile_status' => $data['profile_status'] ?? $record->profile_status ?? 'pending',
        ]);
    }

    protected function updateAttributesTab(Model $record, array $data): void
    {
        // Attribute fields are at the top level of $data (no relationship wrapper).
        $record->update([
            'age_group_id' => $data['age_group_id'] ?? $record->age_group_id,
            'hair_color_id' => $data['hair_color_id'] ?? $record->hair_color_id,
            'hair_length_id' => $data['hair_length_id'] ?? $record->hair_length_id,
            'ethnicity_id' => $data['ethnicity_id'] ?? $record->ethnicity_id,
            'body_type_id' => $data['body_type_id'] ?? $record->body_type_id,
            'bust_size_id' => $data['bust_size_id'] ?? $record->bust_size_id,
            'your_length_id' => $data['your_length_id'] ?? $record->your_length_id,
            'availability' => $data['availability'] ?? $record->availability,
            'contact_method' => $data['contact_method'] ?? $record->contact_method,
            'phone_contact_preference' => $data['phone_contact_preference'] ?? $record->phone_contact_preference,
            'time_waster_shield' => $data['time_waster_shield'] ?? $record->time_waster_shield,
            'primary_identity' => $data['primary_identity'] ?? $record->primary_identity ?? [],
            'attributes' => $data['attributes'] ?? $record->attributes ?? [],
            'services_style' => $data['services_style'] ?? $record->services_style ?? [],
            'services_provided' => $data['services_provided'] ?? $record->services_provided ?? [],
        ]);
    }

    protected function updateContactTab(Model $record, array $data): void
    {
        // Contact fields are at the top level of $data (no relationship wrapper).
        $record->update([
            'twitter_handle' => $data['twitter_handle'] ?? $record->twitter_handle,
            'website' => $data['website'] ?? $record->website,
            'onlyfans_username' => $data['onlyfans_username'] ?? $record->onlyfans_username,
            'phone' => $data['phone'] ?? $record->phone,
            'whatsapp' => $data['whatsapp'] ?? $record->whatsapp,
        ]);
    }

    protected function updateImagesTab(Model $record, array $data): void
    {
        if (! array_key_exists('profileImages', $data)) {
            return;
        }

        $this->syncHasManyRelation(
            $record->profileImages(),
            $this->addUserIdToItems($data['profileImages'], $record->user_id),
            ['image_path', 'thumbnail_path', 'is_primary', 'user_id']
        );
    }

    protected function updateVideosTab(Model $record, array $data): void
    {
        if (! array_key_exists('userVideos', $data)) {
            return;
        }

        $this->syncHasManyRelation(
            $record->userVideos(),
            $this->addUserIdToItems($data['userVideos'], $record->user_id),
            ['original_name', 'video_path', 'user_id']
        );
    }

    private function addUserIdToItems(array $items, int $userId): array
    {
        return array_map(
            fn (array $item) => array_merge($item, ['user_id' => $userId]),
            $items
        );
    }

    protected function updateRatesTab(Model $record, array $data): void
    {
        if (! array_key_exists('rates', $data)) {
            return;
        }

        $this->syncHasManyRelation(
            $record->rates(),
            $data['rates'],
            ['description', 'incall', 'outcall', 'extra']
        );
    }

    protected function updateAvailabilityTab(Model $record, array $data): void
    {
        if (! array_key_exists('availabilities', $data)) {
            return;
        }

        $this->syncHasManyRelation(
            $record->availabilities(),
            $data['availabilities'],
            ['day', 'enabled', 'from_time', 'to_time', 'till_late', 'all_day', 'by_appointment']
        );
    }

    protected function updateProfileMessageTab(Model $record, array $data): void
    {
        $messageData = $data['profileMessage'] ?? [];

        if (array_key_exists('message', $messageData)) {
            ProfileMessage::query()->updateOrCreate(
                ['provider_profile_id' => $record->id],
                [
                    'user_id' => $record->user_id,
                    'message' => $messageData['message'] ?? '',
                ],
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
