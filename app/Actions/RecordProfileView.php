<?php

namespace App\Actions;

use App\Models\ProfileView;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;

class RecordProfileView
{
    /**
     * Record a profile view for the given profile slug.
     * Silently does nothing if the profile is not found.
     */
    public function execute(string $slug, Request $request): void
    {
        $profile = ProviderProfile::query()
            ->where('slug', $slug)
            ->first(['id', 'user_id']);

        if (! $profile) {
            return;
        }

        ProfileView::query()->create([
            'user_id' => $profile->user_id,
            'provider_profile_id' => $profile->id,
            'viewer_ip' => $request->ip(),
        ]);
    }
}
