<?php

namespace App\Filament\Agent\Resources\ProviderListingResource\Pages;

use App\Filament\Agent\Resources\ProviderListingResource;
use App\Filament\Concerns\ChecksEmailSendingOutcome;
use App\Jobs\SendAdminProviderEmailJob;
use App\Models\ProfileMessage;
use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateProviderListing extends CreateRecord
{
    use ChecksEmailSendingOutcome;

    protected static string $resource = ProviderListingResource::class;

    protected string $plainPassword = '';

    protected string $creatorName = '';

    protected function handleRecordCreation(array $data): Model
    {
        $this->plainPassword = $data['password'] ?? '';
        $this->creatorName = $this->resolveCreatorName();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'suburb' => $data['suburb'] ?? null,
            'password' => $data['password'],
            'role' => User::ROLE_PROVIDER,
            'is_blocked' => false,
            'email_verified_at' => now(),
        ]);

        $agentId = Filament::auth()->user()?->role === User::ROLE_ADMIN
            ? null
            : Filament::auth()->id();

        $profileData = $data['providerProfile'] ?? [];

        $profileName = filled($profileData['name'] ?? null)
            ? $profileData['name']
            : ($data['name'] ?? '');

        $baseSlug = filled($profileData['slug'] ?? null)
            ? Str::slug($profileData['slug'])
            : Str::slug($profileName);

        if (! filled($baseSlug)) {
            $baseSlug = 'provider-'.$user->id;
        }

        $slug = $baseSlug;
        $index = 2;

        while (ProviderProfile::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$index;
            $index++;
        }

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $profileName,
                'slug' => $slug,
                'agent_id' => $agentId,
                'description' => $profileData['description'] ?? '',
                'introduction_line' => $profileData['introduction_line'] ?? '',
                'profile_text' => $profileData['profile_text'] ?? '',
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

        if (array_key_exists('message', $messageData)) {
            ProfileMessage::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['message' => $messageData['message'] ?? ''],
            );
        }

        return $user->refresh();
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if (! $this->plainPassword) {
            return;
        }

        $plainPassword = $this->plainPassword;
        $this->plainPassword = '';

        $agentName = filled($this->creatorName) ? $this->creatorName : null;

        $dispatchedAt = now();

        try {
            SendAdminProviderEmailJob::dispatchSync($user->id, 'created', $plainPassword, $agentName);
        } catch (\Throwable) {
            // Job already logged the error and created an email log entry
        }

        if ($this->hasRecentEmailFailure($user->email, $dispatchedAt)) {
            Notification::make()
                ->title('Email sending failed')
                ->body('Provider was created but the account email failed to send. Check Email Logs for details.')
                ->warning()
                ->send();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Filament::auth()->user()?->role !== User::ROLE_ADMIN) {
            if (! isset($data['providerProfile'])) {
                $data['providerProfile'] = [];
            }

            $data['providerProfile']['profile_status'] = 'pending';
        }

        return $data;
    }

    /**
     * Returns the authenticated user's name when they are an agent (not admin).
     * Admins creating providers on behalf of themselves should not appear as "agents".
     */
    private function resolveCreatorName(): string
    {
        $authUser = Filament::auth()->user();

        if (! $authUser || $authUser->role === User::ROLE_ADMIN) {
            return '';
        }

        return $authUser->name ?? '';
    }
}
