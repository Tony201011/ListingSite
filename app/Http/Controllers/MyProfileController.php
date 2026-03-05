<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;

class MyProfileController extends Controller
{
    private const WEBSITE_TYPE = 'adult';

    public function stepTwo()
    {
        $contactEmail = SiteSetting::query()
            ->whereNotNull('contact_email')
            ->latest('id')
            ->value('contact_email') ?? 's8813w@gmail.com';

        return view('my-profile-2', [
            'contactEmail' => $contactEmail,
            'ageGroupOptions' => $this->getCategoryOptions('age-group'),
            'hairColorOptions' => $this->getCategoryOptions('hair-color'),
            'hairLengthOptions' => $this->getCategoryOptions('hair-length'),
            'ethnicityOptions' => $this->getCategoryOptions('ethnicity'),
            'bodyTypeOptions' => $this->getCategoryOptions('body-type'),
            'bustSizeOptions' => $this->getCategoryOptions('bust-size'),
            'yourLengthOptions' => $this->getCategoryOptions('your-length'),
            'primaryTags' => $this->getCategoryOptions('primary-identity'),
            'attrTags' => $this->getCategoryOptions('attributes'),
            'styleTags' => $this->getCategoryOptions('services-style'),
            'services' => $this->getCategoryOptions('services-you-provide'),
            'availabilityOptions' => $this->getCategoryOptions('availability'),
            'contactMethodOptions' => $this->getCategoryOptions('contact-method'),
            'phoneContactOptions' => $this->getCategoryOptions('phone-contact-preferences'),
            'timeWasterOptions' => $this->getCategoryOptions('time-waster-shield'),
        ]);
    }

    private function getCategoryOptions(string $slug): array
    {
        $parentId = Category::query()
            ->where('slug', $slug)
            ->where('website_type', self::WEBSITE_TYPE)
            ->where('is_active', true)
            ->value('id');

        if (! $parentId) {
            Log::warning('Profile category parent slug not found or inactive.', [
                'slug' => $slug,
                'route' => request()->path(),
            ]);

            return [];
        }

        $options = Category::query()
            ->where('parent_id', $parentId)
            ->where('website_type', self::WEBSITE_TYPE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('name')
            ->all();

        if (empty($options)) {
            Log::warning('Profile category options are empty for active parent.', [
                'slug' => $slug,
                'parent_id' => $parentId,
                'route' => request()->path(),
            ]);
        }

        return $options;
    }
}
