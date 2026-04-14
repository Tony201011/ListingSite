<?php

namespace App\Actions;

use App\Models\ShortUrl;
use App\Models\SiteSetting;
use App\Models\User;

class GetShortUrlPageData
{
    public function execute(?User $user): array
    {
        if (! $user) {
            return [
                'redirect' => '/signin',
            ];
        }

        $siteSetting = SiteSetting::query()
            ->latest('updated_at')
            ->value('short_url') ?? false;

        if (! $siteSetting) {
            return [
                'redirect' => '/profile-setting',
            ];
        }

        $shortUrlRecord = ShortUrl::query()
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['short_url' => $this->generateUniqueSlug($user)]
            );

        return [
            'slug' => $shortUrlRecord->short_url,
            'siteSetting' => $siteSetting,
        ];
    }

    private function generateUniqueSlug(User $user): string
    {
        $slug = md5($user->name.$user->id);

        while (ShortUrl::query()->where('short_url', $slug)->exists()) {
            $slug = md5($user->name.$user->id.random_int(1, 9999));
        }

        return $slug;
    }
}
