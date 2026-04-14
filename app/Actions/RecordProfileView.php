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
        $userId = ProviderProfile::query()
            ->where('slug', $slug)
            ->value('user_id');

        if (! $userId) {
            return;
        }

        ProfileView::query()->create([
            'user_id' => $userId,
            'viewer_ip' => $request->ip(),
        ]);
    }
}
