<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveMyProfile
{
    public function __construct(
        private GenerateUniqueProviderProfileSlug $generateUniqueProviderProfileSlug
    ) {}

    public function execute(?User $user, array $validated, ?ProviderProfile $activeProfile = null): ActionResult
    {
        if (! $user) {
            return ActionResult::authorizationFailure('Unauthenticated.', 401);
        }

        $this->validateCategorySelections($validated);

        $profile = DB::transaction(function () use ($user, $validated, $activeProfile) {
            $user->update([
                'suburb' => $validated['suburb'] ?? null,
            ]);

            if ($activeProfile !== null) {
                $profile = $activeProfile;
            } else {
                $profile = new ProviderProfile(['user_id' => $user->id]);
            }

            $accountUserReferralCode = Str::substr(
                md5($user->id.$user->email),
                0,
                10
            );

            $profile->name = $user->name;

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

            return $profile;
        });

        return ActionResult::success(['profile_id' => $profile->id], 'Profile updated successfully.');
    }

    private function validateCategorySelections(array $validated): void
    {
        $idFields = [
            'age_group' => 'age-group',
            'hair_color' => 'hair-color',
            'hair_length' => 'hair-length',
            'ethnicity' => 'ethnicity',
            'body_type' => 'body-type',
            'bust_size' => 'bust-size',
            'your_length' => 'your-length',
        ];

        $nameFields = [
            'availability' => 'availability',
            'contact_method' => 'contact-method',
            'phone_contact' => 'phone-contact-preferences',
            'time_waster' => 'time-waster-shield',
        ];

        $multiValueFields = [
            'primary_identity' => 'primary-identity',
            'attributes' => 'attributes',
            'services_style' => 'services-style',
            'services_provided' => 'services-you-provide',
        ];

        $errors = [];

        foreach ($idFields as $field => $type) {
            $value = $validated[$field] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (! $this->categoryExistsForType((int) $value, $type)) {
                $errors[$field] = ["The selected {$field} is invalid."];
            }
        }

        foreach ($nameFields as $field => $type) {
            $value = $validated[$field] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (! $this->categoryNameExistsForType((string) $value, $type)) {
                $errors[$field] = ["The selected {$field} is invalid."];
            }
        }

        foreach ($multiValueFields as $field => $type) {
            $values = $validated[$field] ?? [];

            if ($values === null) {
                continue;
            }

            if (! is_array($values)) {
                $errors[$field] = ["The {$field} field must be an array."];

                continue;
            }

            foreach ($values as $index => $value) {
                if (! $this->categoryNameExistsForType((string) $value, $type)) {
                    $errors["{$field}.{$index}"] = ["The selected {$field} item is invalid."];
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function categoryExistsForType(int $id, string $parentSlug): bool
    {
        return Category::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($q) => $q->where('slug', $parentSlug))
            ->exists();
    }

    private function categoryNameExistsForType(string $name, string $parentSlug): bool
    {
        return Category::query()
            ->where('name', $name)
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($q) => $q->where('slug', $parentSlug))
            ->exists();
    }
}
