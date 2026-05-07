<?php

namespace Tests\Feature\Profile;

use App\Models\AvailableNow;
use App\Models\OnlineUser;
use App\Models\ProviderProfile;
use App\Models\SetAndForget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessSetAndForgetTest extends TestCase
{
    use RefreshDatabase;

    // Monday 2025-01-06 at 08:00 UTC
    private const MONDAY = '2025-01-06 08:00:00';

    private function createProviderWithSchedule(array $scheduleOverrides = []): array
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        $schedule = SetAndForget::query()->create(array_merge([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'online_now_enabled' => false,
            'online_now_days' => [],
            'online_now_time' => null,
            'available_now_enabled' => false,
            'available_now_days' => [],
            'available_now_time' => null,
        ], $scheduleOverrides));

        return [$user, $profile, $schedule];
    }

    public function test_online_now_triggers_when_day_and_time_match(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'online_now_enabled' => true,
            'online_now_days' => ['Monday'],
            'online_now_time' => '08:00',
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $onlineUser = OnlineUser::where('provider_profile_id', $profile->id)->first();
        $this->assertNotNull($onlineUser);
        $this->assertSame('online', $onlineUser->status);
    }

    public function test_online_now_does_not_trigger_when_day_does_not_match(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'online_now_enabled' => true,
            'online_now_days' => ['Tuesday'],
            'online_now_time' => '08:00',
        ]);

        // Travel to Monday — no match
        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $onlineUser = OnlineUser::where('provider_profile_id', $profile->id)->first();
        $this->assertNull($onlineUser);
    }

    public function test_online_now_does_not_trigger_when_time_does_not_match(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'online_now_enabled' => true,
            'online_now_days' => ['Monday'],
            'online_now_time' => '09:00',
        ]);

        // Travel to Monday 08:00 — time mismatch
        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $onlineUser = OnlineUser::where('provider_profile_id', $profile->id)->first();
        $this->assertNull($onlineUser);
    }

    public function test_online_now_does_not_trigger_when_disabled(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'online_now_enabled' => false,
            'online_now_days' => ['Monday'],
            'online_now_time' => '08:00',
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $onlineUser = OnlineUser::where('provider_profile_id', $profile->id)->first();
        $this->assertNull($onlineUser);
    }

    public function test_online_now_does_not_trigger_when_days_empty(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'online_now_enabled' => true,
            'online_now_days' => [],
            'online_now_time' => '08:00',
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $onlineUser = OnlineUser::where('provider_profile_id', $profile->id)->first();
        $this->assertNull($onlineUser);
    }

    public function test_online_now_does_not_trigger_when_time_is_null(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'online_now_enabled' => true,
            'online_now_days' => ['Monday'],
            'online_now_time' => null,
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $onlineUser = OnlineUser::where('provider_profile_id', $profile->id)->first();
        $this->assertNull($onlineUser);
    }

    public function test_available_now_triggers_when_day_and_time_match(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'available_now_enabled' => true,
            'available_now_days' => ['Monday'],
            'available_now_time' => '08:00',
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $available = AvailableNow::where('provider_profile_id', $profile->id)->first();
        $this->assertNotNull($available);
        $this->assertSame('online', $available->status);
    }

    public function test_available_now_does_not_trigger_when_disabled(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'available_now_enabled' => false,
            'available_now_days' => ['Monday'],
            'available_now_time' => '08:00',
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $available = AvailableNow::where('provider_profile_id', $profile->id)->first();
        $this->assertNull($available);
    }

    public function test_both_online_now_and_available_now_trigger_when_both_enabled(): void
    {
        [, $profile] = $this->createProviderWithSchedule([
            'online_now_enabled' => true,
            'online_now_days' => ['Monday'],
            'online_now_time' => '08:00',
            'available_now_enabled' => true,
            'available_now_days' => ['Monday'],
            'available_now_time' => '08:00',
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $onlineUser = OnlineUser::where('provider_profile_id', $profile->id)->first();
        $this->assertSame('online', $onlineUser->status);

        $available = AvailableNow::where('provider_profile_id', $profile->id)->first();
        $this->assertSame('online', $available->status);
    }

    public function test_command_skips_records_with_no_profile(): void
    {
        // Create a SetAndForget without a linked profile (orphaned record)
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        SetAndForget::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => null,
            'online_now_enabled' => true,
            'online_now_days' => ['Monday'],
            'online_now_time' => '08:00',
            'available_now_enabled' => false,
            'available_now_days' => [],
            'available_now_time' => null,
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        // Should complete without throwing an exception
        $this->artisan('set-and-forget:process')->assertSuccessful();
    }

    public function test_command_does_not_process_records_with_both_features_disabled(): void
    {
        $this->createProviderWithSchedule([
            'online_now_enabled' => false,
            'available_now_enabled' => false,
        ]);

        $this->travelTo(Carbon::parse(self::MONDAY));

        $this->artisan('set-and-forget:process')->assertSuccessful();

        $this->assertDatabaseCount('online_users', 0);
        $this->assertDatabaseCount('available_nows', 0);
    }
}
