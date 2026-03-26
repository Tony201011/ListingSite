<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaveMyProfile
{
    public function __construct(
        private GenerateUniqueProviderProfileSlug $generateUniqueProviderProfileSlug
    ) {
    }

    public function execute(?User $user, array $validated): void
    {
        if (! $user) {
            abort(403);
        }

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
                'mobile' => $validated['mobile'] ?? null,
                'suburb' => $validated['suburb'] ?? null,
            ]);

            $profile = $user->providerProfile()->firstOrNew([
                'user_id' => $user->id,
            ]);

            $accountUserReferralCode = Str::substr(
                md5($user->id . $user->email),
                0,
                10
            );

            $profile->name = $validated['name'] ?? $user->name;

            if (! $profile->slug) {
                $profile->slug = $this->generateUniqueProviderProfileSlug->execute($profile->name);
            }

            $profile->fill([
                'introduction_line' => $validated['introduction_line'] ?? null,
                'profile_text' => $validated['profile_text'] ?? null,
                'primary_identity' => $validated['primary_identity'] ?? [],
                'attributes' => $validated['attributes'] ?? [],
                'services_style' => $validated['services_style'] ?? [],
                'services_provided' => $validated['services_provided'] ?? [],
                'age_group_id' => $validated['age_group'] ?? null,
                'hair_color_id' => $validated['hair_color'] ?? null,
                'hair_length_id' => $validated['hair_length'] ?? null,
                'ethnicity_id' => $validated['ethnicity'] ?? null,
                'body_type_id' => $validated['body_type'] ?? null,
                'bust_size_id' => $validated['bust_size'] ?? null,
                'your_length_id' => $validated['your_length'] ?? null,
                'availability' => $validated['availability'] ?? null,
                'contact_method' => $validated['contact_method'] ?? null,
                'phone_contact_preference' => $validated['phone_contact'] ?? null,
                'time_waster_shield' => $validated['time_waster'] ?? null,
                'twitter_handle' => $validated['twitter_handle'] ?? null,
                'website' => $validated['website'] ?? null,
                'onlyfans_username' => $validated['onlyfans_username'] ?? null,
                'account_user_referral_code' => $accountUserReferralCode,
            ]);

            $profile->save();
        });
    }
}
