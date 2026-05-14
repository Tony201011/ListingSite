<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseFeatured
{
    public function execute(User $user, ProviderProfile $profile): ActionResult
    {
        $settings = SiteSetting::getFeaturedSettings();
        $creditCost = $settings['featured_credit_cost'];
        $durationDays = $settings['featured_duration_days'];

        if ($user->credits < $creditCost) {
            return new ActionResult(
                false,
                422,
                "You need {$creditCost} credits to activate Featured. You currently have {$user->credits} credits.",
                [
                    'is_featured' => (bool) $profile->is_featured,
                    'expires_at' => $profile->featured_expires_at?->toIso8601String(),
                    'credit_cost' => $creditCost,
                    'duration_days' => $durationDays,
                ],
                'domain'
            );
        }

        DB::transaction(function () use ($user, $profile, $creditCost, $durationDays): void {
            // If already featured, extend from current expiry; otherwise start fresh
            $baseDate = ($profile->is_featured && $profile->featured_expires_at && $profile->featured_expires_at->isFuture())
                ? $profile->featured_expires_at
                : now();

            $profile->is_featured = true;
            $profile->featured_expires_at = $baseDate->addDays($durationDays);
            $profile->save();

            $user->decrement('credits', $creditCost);

            CreditLog::create([
                'user_id' => $user->id,
                'amount' => -$creditCost,
                'type' => 'used',
                'description' => "Activated Featured listing for {$durationDays} days",
                'reference_type' => ProviderProfile::class,
                'reference_id' => $profile->id,
            ]);
        });

        $profile->refresh();

        return ActionResult::success([
            'is_featured' => true,
            'expires_at' => $profile->featured_expires_at?->toIso8601String(),
            'credit_cost' => $creditCost,
            'duration_days' => $durationDays,
        ], "Featured activated! Your listing is now featured for {$durationDays} days.");
    }
}
