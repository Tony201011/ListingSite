<?php

namespace App\Actions;

use App\Models\LoginLog;
use App\Models\Rate;
use App\Models\User;
use Illuminate\Support\Carbon;

class CalculateBabeRank
{
    /**
     * Calculate the babe rank data for the given user.
     *
     * Returns an array with:
     *   - rank         (int 1–100)
     *   - profileScore (int 0–100, percentage)
     *   - viewsToday   (int)
     *   - shortCode    (string|null)
     */
    public function execute(?User $user): array
    {
        if (! $user) {
            return [
                'rank' => 0,
                'profileScore' => 0,
                'viewsToday' => 0,
                'shortCode' => null,
            ];
        }

        $user->loadMissing([
            'providerProfile',
            'profileImages',
            'availabilities',
            'shortUrl',
            'hideShowProfile',
            'availableNow',
            'onlineUser',
        ]);

        $profile = $user->providerProfile;

        // ── Profile score (completeness %) ───────────────────────────────────
        $scorePoints = 0;
        $scoreMax = 100;

        // Basic profile fields
        if (! empty($profile?->introduction_line)) {
            $scorePoints += 10;
        }
        if (! empty($profile?->profile_text)) {
            $scorePoints += 10;
        }
        if ($profile?->age_group_id) {
            $scorePoints += 5;
        }
        // Physical attributes (hair colour, hair length, ethnicity, body type, bust size, length)
        $physicalFilled = array_filter([
            $profile?->hair_color_id,
            $profile?->hair_length_id,
            $profile?->ethnicity_id,
            $profile?->body_type_id,
            $profile?->bust_size_id,
            $profile?->your_length_id,
        ]);
        if (count($physicalFilled) >= 4) {
            $scorePoints += 5;
        }
        if (! empty($profile?->primary_identity)) {
            $scorePoints += 5;
        }
        if (! empty($profile?->services_provided)) {
            $scorePoints += 5;
        }

        $photoCount = $user->profileImages->count();
        if ($photoCount >= 1) {
            $scorePoints += 15;
        }
        if ($photoCount >= 5) {
            $scorePoints += 10;
        }

        $hasRates = Rate::query()->where('user_id', $user->id)->exists();
        if ($hasRates) {
            $scorePoints += 10;
        }

        if ($user->availabilities->count() > 0) {
            $scorePoints += 10;
        }
        if (! empty($profile?->phone) || ! empty($profile?->contact_method)) {
            $scorePoints += 5;
        }

        $shortUrlRecord = $user->shortUrl;
        if ($shortUrlRecord?->short_url) {
            $scorePoints += 5;
        }
        if (! empty($profile?->website) || ! empty($profile?->twitter_handle)) {
            $scorePoints += 5;
        }

        $profileScore = (int) round(min(100, ($scorePoints / $scoreMax) * 100));

        // ── Babe rank (1–100) ────────────────────────────────────────────────
        $rankPoints = (int) round($profileScore * 0.7);

        // Activity bonuses (up to 30 extra points)
        if ($shortUrlRecord?->short_url) {
            $rankPoints += 10;
        }

        $recentLogin = LoginLog::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->exists();
        if ($recentLogin) {
            $rankPoints += 8;
        }

        $profileVisible = $user->hideShowProfile?->status !== 'hide';
        if ($profileVisible) {
            $rankPoints += 7;
        }

        if ($user->availableNow !== null) {
            $rankPoints += 5;
        }

        $rank = max(1, min(100, $rankPoints));

        // ── Views today ──────────────────────────────────────────────────────
        $viewsToday = $user->profileViews()
            ->whereDate('created_at', Carbon::today())
            ->count();

        // ── Short code ───────────────────────────────────────────────────────
        $shortCode = $shortUrlRecord?->short_url;

        return [
            'rank' => $rank,
            'profileScore' => $profileScore,
            'viewsToday' => $viewsToday,
            'shortCode' => $shortCode,
        ];
    }
}
