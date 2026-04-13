<?php

namespace App\Actions;

use App\Concerns\ResolvesProfileCategoryValues;
use App\Models\SiteSetting;
use App\Models\User;

class GetMyProfileStepTwoData
{
    use ResolvesProfileCategoryValues;

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
            'availability' => self::resolveProfileCategoryName($profile?->availability, 'availability'),
            'contact_method' => self::resolveProfileCategoryName($profile?->contact_method, 'contact-method'),
            'phone_contact' => self::resolveProfileCategoryName($profile?->phone_contact_preference, 'phone-contact-preferences'),
            'time_waster' => self::resolveProfileCategoryName($profile?->time_waster_shield, 'time-waster-shield'),
            'primary_identity' => self::resolveProfileCategoryNames($profile?->primary_identity ?? [], 'primary-identity'),
            'attributes' => self::resolveProfileCategoryNames($profile?->attributes ?? [], 'attributes'),
            'services_style' => self::resolveProfileCategoryNames($profile?->services_style ?? [], 'services-style'),
            'services_provided' => self::resolveProfileCategoryNames($profile?->services_provided ?? [], 'services-you-provide'),
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
