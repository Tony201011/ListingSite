<?php

namespace Tests\Feature;

use App\Models\CreditLog;
use App\Models\HideShowProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeductDailyCreditsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_daily_deduction_transaction_with_balance_message_for_visible_profile(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 243,
        ]);

        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Visible User',
            'slug' => 'visible-user-'.$user->id,
            'profile_status' => 'approved',
        ]);

        HideShowProfile::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'show',
        ]);

        $this->artisan('credits:deduct-daily')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'credits' => 242,
        ]);

        $this->assertDatabaseHas('credit_logs', [
            'user_id' => $user->id,
            'amount' => -1,
            'type' => 'daily_deduction',
            'description' => 'Your current credits balance is 243. You are charged 1 credit per day while your profile is visible.',
        ]);
    }

    public function test_it_does_not_deduct_when_profile_is_hidden(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 243,
        ]);

        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Hidden User',
            'slug' => 'hidden-user-'.$user->id,
            'profile_status' => 'approved',
        ]);

        HideShowProfile::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'hide',
        ]);

        $this->artisan('credits:deduct-daily')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'credits' => 243,
        ]);

        $this->assertDatabaseMissing('credit_logs', [
            'user_id' => $user->id,
            'type' => 'daily_deduction',
        ]);
    }

    public function test_it_does_not_deduct_when_credits_are_zero(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 0,
        ]);

        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'No Credits User',
            'slug' => 'no-credits-user-'.$user->id,
            'profile_status' => 'approved',
        ]);

        HideShowProfile::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'show',
        ]);

        $this->artisan('credits:deduct-daily')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'credits' => 0,
        ]);

        $this->assertSame(0, CreditLog::query()->where('user_id', $user->id)->count());

        $this->assertDatabaseHas('hide_show_profiles', [
            'provider_profile_id' => $profile->id,
            'status' => 'hide',
        ]);
    }

    public function test_it_does_not_deduct_during_free_listing_period(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 100,
        ]);

        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Free Trial User',
            'slug' => 'free-trial-user-'.$user->id,
            'profile_status' => 'approved',
            'free_listing_expires_at' => now()->addDays(10),
        ]);

        HideShowProfile::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'show',
        ]);

        $this->artisan('credits:deduct-daily')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'credits' => 100,
        ]);

        $this->assertDatabaseMissing('credit_logs', [
            'user_id' => $user->id,
            'type' => 'daily_deduction',
        ]);
    }

    public function test_it_deducts_after_free_listing_period_expires(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 100,
        ]);

        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Post Trial User',
            'slug' => 'post-trial-user-'.$user->id,
            'profile_status' => 'approved',
            'free_listing_expires_at' => now()->subDay(),
        ]);

        HideShowProfile::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'show',
        ]);

        $this->artisan('credits:deduct-daily')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'credits' => 99,
        ]);
    }

    public function test_it_deducts_one_credit_per_visible_profile_after_free_period(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 10,
        ]);

        $profileOne = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Multi Profile User One',
            'slug' => 'multi-profile-user-one-'.$user->id,
            'profile_status' => 'approved',
            'free_listing_expires_at' => now()->subDay(),
        ]);

        $profileTwo = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Multi Profile User Two',
            'slug' => 'multi-profile-user-two-'.$user->id,
            'profile_status' => 'approved',
            'free_listing_expires_at' => now()->subDay(),
        ]);

        HideShowProfile::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profileOne->id,
            'status' => 'show',
        ]);

        HideShowProfile::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profileTwo->id,
            'status' => 'show',
        ]);

        $this->artisan('credits:deduct-daily')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'credits' => 8,
        ]);

        $this->assertSame(
            2,
            CreditLog::query()
                ->where('user_id', $user->id)
                ->where('type', 'daily_deduction')
                ->count()
        );
    }
}
