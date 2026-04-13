<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\SiteSetting;
use App\Models\User;

class GetMyProfileStepTwoData
{
    public function __construct(
        private GetProfileCategoryOptions $getProfileCategoryOptions
    ) {}

    public function execute(?User $user): array
    {
        $profile = $user?->providerProfile;

        $contactEmail = SiteSetting::query()
            ->whereNotNull('contact_email')
            ->latest('id')
            ->value('contact_email') ?? 's8813w@gmail.com';

        $selected = [
            'age_group' => $profile?->age_group_id,
            'hair_color' => $profile?->hair_color_id,
            'hair_length' => $profile?->hair_length_id,
            'ethnicity' => $profile?->ethnicity_id,
            'body_type' => $profile?->body_type_id,
            'bust_size' => $profile?->bust_size_id,
            'your_length' => $profile?->your_length_id,
            'availability' => $this->resolveNameFromCategory($profile?->availability, 'availability'),
            'contact_method' => $this->resolveNameFromCategory($profile?->contact_method, 'contact-method'),
            'phone_contact' => $this->resolveNameFromCategory($profile?->phone_contact_preference, 'phone-contact-preferences'),
            'time_waster' => $this->resolveNameFromCategory($profile?->time_waster_shield, 'time-waster-shield'),
            'primary_identity' => $this->resolveNamesFromCategories($profile?->primary_identity ?? [], 'primary-identity'),
            'attributes' => $this->resolveNamesFromCategories($profile?->attributes ?? [], 'attributes'),
            'services_style' => $this->resolveNamesFromCategories($profile?->services_style ?? [], 'services-style'),
            'services_provided' => $this->resolveNamesFromCategories($profile?->services_provided ?? [], 'services-you-provide'),
        ];

        return [
            'user' => $user,
            'profile' => $profile,
            'contactEmail' => $contactEmail,
            'selected' => $selected,
            'ageGroupOptions' => $this->getProfileCategoryOptions->execute('age-group'),
            'hairColorOptions' => $this->getProfileCategoryOptions->execute('hair-color'),
            'hairLengthOptions' => $this->getProfileCategoryOptions->execute('hair-length'),
            'ethnicityOptions' => $this->getProfileCategoryOptions->execute('ethnicity'),
            'bodyTypeOptions' => $this->getProfileCategoryOptions->execute('body-type'),
            'bustSizeOptions' => $this->getProfileCategoryOptions->execute('bust-size'),
            'yourLengthOptions' => $this->getProfileCategoryOptions->execute('your-length'),
            'primaryTags' => $this->getProfileCategoryOptions->execute('primary-identity'),
            'attrTags' => $this->getProfileCategoryOptions->execute('attributes'),
            'styleTags' => $this->getProfileCategoryOptions->execute('services-style'),
            'services' => $this->getProfileCategoryOptions->execute('services-you-provide'),
            'availabilityOptions' => $this->getProfileCategoryOptions->execute('availability'),
            'contactMethodOptions' => $this->getProfileCategoryOptions->execute('contact-method'),
            'phoneContactOptions' => $this->getProfileCategoryOptions->execute('phone-contact-preferences'),
            'timeWasterOptions' => $this->getProfileCategoryOptions->execute('time-waster-shield'),
        ];
    }

    private function resolveNameFromCategory(mixed $value, string $parentSlug): ?string
    {
        if (blank($value)) {
            return null;
        }

        $strValue = (string) $value;

        $query = Category::query()
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($q) => $q->where('slug', $parentSlug));

        if ((clone $query)->where('name', $strValue)->exists()) {
            return $strValue;
        }

        if (is_numeric($value)) {
            $name = (clone $query)->whereKey((int) $value)->value('name');
            if ($name !== null) {
                return $name;
            }
        }

        return (clone $query)->where('slug', $strValue)->value('name');
    }

    private function resolveNamesFromCategories(mixed $values, string $parentSlug): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->flatten(1)
            ->map(fn ($val) => $this->resolveNameFromCategory($val, $parentSlug))
            ->filter()
            ->values()
            ->all();
    }
}
