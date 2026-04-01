<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Jobs\SendAdminProviderEmailJob;
use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'suburb' => $data['suburb'] ?? null,
            'password' => $data['password'],
            'role' => User::ROLE_PROVIDER,
            'is_blocked' => false,
        ]);

        ProviderProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $data['profile_name'],
                'slug' => filled($data['profile_slug'] ?? null) ? $data['profile_slug'] : Str::slug($data['profile_name']),
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
            ],
        );

        SendAdminProviderEmailJob::dispatch($user->id, 'created');

        return $user;
    }
}