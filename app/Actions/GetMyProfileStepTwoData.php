<?php

namespace App\Actions;

use App\Models\SiteSetting;
use App\Models\User;

class GetMyProfileStepTwoData
{
    public function __construct(
        private GetProfileCategoryOptions $getProfileCategoryOptions
    ) {
    }

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
            'availability' => $profile?->availability,
            'contact_method' => $profile?->contact_method,
            'phone_contact' => $profile?->phone_contact_preference,
            'time_waster' => $profile?->time_waster_shield,
            'primary_identity' => $profile?->primary_identity ?? [],
            'attributes' => $profile?->attributes ?? [],
            'services_style' => $profile?->services_style ?? [],
            'services_provided' => $profile?->services_provided ?? [],
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
}
