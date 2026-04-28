<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use App\Models\ShortUrl;
use App\Models\SiteSetting;

class GetShortUrlPageData
{
    public function execute(?ProviderProfile $profile): array
    {
        $siteSetting = SiteSetting::query()
            ->latest('updated_at')
            ->value('short_url') ?? false;

        if (! $profile) {
            return [
                'redirect' => '/signin',
            ];
        }

        $shortUrlRecord = ShortUrl::query()
            ->firstOrCreate(
                ['provider_profile_id' => $profile->id],
                ['short_url' => $this->generateUniqueSlug($profile)]
            );

        return [
            'slug' => $shortUrlRecord->short_url,
            'siteSetting' => $siteSetting,
        ];
    }

    private function generateUniqueSlug(ProviderProfile $profile): string
    {
        $slug = md5($profile->name.$profile->id);

        while (ShortUrl::query()->where('short_url', $slug)->exists()) {
            $slug = md5($profile->name.$profile->id.random_int(1, 9999));
        }

        return $slug;
    }
}
