<?php

namespace Tests\Feature\Profile;

use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSpendingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_spending_history_only_shows_active_profile_entries(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 25,
        ]);

        $activeProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Active Profile',
            'slug' => 'active-profile-'.$user->id,
        ]);

        $otherProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Other Profile',
            'slug' => 'other-profile-'.$user->id,
        ]);

        $otherUser = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 10,
        ]);

        $otherUsersProfile = ProviderProfile::query()->create([
            'user_id' => $otherUser->id,
            'name' => 'Other User Profile',
            'slug' => 'other-user-profile-'.$otherUser->id,
        ]);

        CreditLog::query()->create([
            'user_id' => $user->id,
            'amount' => -5,
            'type' => 'used',
            'description' => 'Activated Featured Listing for 1 day',
            'reference_type' => ProviderProfile::class,
            'reference_id' => $activeProfile->id,
        ]);

        CreditLog::query()->create([
            'user_id' => $user->id,
            'amount' => -1,
            'type' => 'daily_deduction',
            'description' => 'Your current credits balance is 5. You are charged 1 credit per day while your profile is visible.',
            'reference_type' => ProviderProfile::class,
            'reference_id' => $activeProfile->id,
        ]);

        CreditLog::query()->create([
            'user_id' => $user->id,
            'amount' => -3,
            'type' => 'used',
            'description' => 'Activated Local Banner for 1 day',
            'reference_type' => ProviderProfile::class,
            'reference_id' => $otherProfile->id,
        ]);

        CreditLog::query()->create([
            'user_id' => $otherUser->id,
            'amount' => -2,
            'type' => 'used',
            'description' => 'Activated Home Page Banner for 1 day',
            'reference_type' => ProviderProfile::class,
            'reference_id' => $otherUsersProfile->id,
        ]);

        $response = $this->actingAs($user)->withSession([
            'active_provider_profile_id' => $activeProfile->id,
        ])->get(route('profile-spending-history'));

        $response->assertOk();
        $response->assertViewIs('profile.spending-history');
        $response->assertSeeText('Active Profile');
        $response->assertSeeText('Activated Featured Listing for 1 day');
        $response->assertSeeText('charged 1 credit per day while your profile is visible');
        $response->assertDontSeeText('Activated Local Banner for 1 day');
        $response->assertDontSeeText('Activated Home Page Banner for 1 day');
        $response->assertViewHas('totalSpent', 6);
        $response->assertViewHas('dailyFeesSpent', 1);
        $response->assertViewHas('boostsSpent', 5);
    }
}
