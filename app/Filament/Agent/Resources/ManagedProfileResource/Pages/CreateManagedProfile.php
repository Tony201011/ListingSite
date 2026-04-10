<?php

namespace App\Filament\Agent\Resources\ManagedProfileResource\Pages;

use App\Filament\Agent\Resources\ManagedProfileResource;
use App\Jobs\SendAdminProviderEmailJob;
use App\Models\ProfileMessage;
use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateManagedProfile extends CreateRecord
{
    protected static string $resource = ManagedProfileResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $providerPassword = (string) $data['provider_password'];
        $providerEmail = (string) $data['provider_email'];
        $agentId = $this->resolveAgentId($data);
        $agentName = User::query()->whereKey($agentId)->value('name');

        unset($data['provider_password'], $data['provider_password_confirmation'], $data['provider_email']);

        $mobile = $data['mobile'] ?? null;
        $suburb = $data['suburb'] ?? null;
        unset($data['mobile'], $data['suburb']);

        $messageData = $data['profileMessage'] ?? [];
        unset($data['profileMessage']);

        $profile = DB::transaction(function () use ($agentId, $providerEmail, $providerPassword, $mobile, $suburb, $data, $messageData): ProviderProfile {
            $providerUser = User::query()->create([
                'name' => $data['name'],
                'email' => $providerEmail,
                'password' => $providerPassword,
                'role' => User::ROLE_PROVIDER,
                'is_blocked' => false,
                'email_verified_at' => now(),
                'mobile' => $mobile,
                'suburb' => $suburb,
            ]);

            $profileName = filled($data['name'] ?? null) ? $data['name'] : '';
            $baseSlug = filled($data['slug'] ?? null)
                ? Str::slug($data['slug'])
                : Str::slug($profileName);

            if (blank($baseSlug)) {
                $baseSlug = 'provider-'.$providerUser->id;
            }

            $slug = $baseSlug;
            $index = 2;

            while (ProviderProfile::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$index;
                $index++;
            }

            $data['slug'] = $slug;

            $profile = ProviderProfile::query()->create([
                ...$data,
                'agent_id' => $agentId,
                'user_id' => $providerUser->id,
            ]);

            if (array_key_exists('message', $messageData)) {
                ProfileMessage::query()->updateOrCreate(
                    ['user_id' => $providerUser->id],
                    ['message' => $messageData['message'] ?? ''],
                );
            }

            return $profile;
        });

        SendAdminProviderEmailJob::dispatch($profile->user_id, 'created', $providerPassword, $agentName);

        return $profile;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Filament::auth()->user()?->role !== User::ROLE_ADMIN) {
            $data['profile_status'] = 'pending';
        }

        return $data;
    }

    private function resolveAgentId(array $data): int
    {
        if (Filament::auth()->user()?->role === User::ROLE_ADMIN) {
            if (blank($data['agent_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'agent_id' => 'Please select an agent account.',
                ]);
            }

            return (int) $data['agent_id'];
        }

        return (int) Filament::auth()->id();
    }
}
