<?php

namespace Tests\Feature;

use App\Models\OnlineUser;
use App\Models\ProviderOnlineLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolloverOnlineSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_closes_open_session_at_midnight_and_opens_new_one(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Profile',
            'slug' => 'test-profile',
        ]);

        // Simulate: profile went online at 11 PM yesterday.
        Carbon::setTestNow('2026-05-22 23:00:00');

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now(),
        ]);

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => now(),
        ]);

        // Advance time to just past midnight.
        Carbon::setTestNow('2026-05-23 00:00:01');

        $this->artisan('online-sessions:rollover')->assertSuccessful();

        $logs = ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->orderBy('went_online_at')
            ->get();

        $this->assertCount(2, $logs);

        // First log: closed at midnight.
        $midnight = Carbon::parse('2026-05-23 00:00:00', 'UTC');
        $this->assertNotNull($logs[0]->went_offline_at);
        $this->assertTrue($logs[0]->went_offline_at->equalTo($midnight));
        $this->assertSame(3600, $logs[0]->duration_seconds);

        // Second log: opened at midnight, still open.
        $this->assertTrue($logs[1]->went_online_at->equalTo($midnight));
        $this->assertNull($logs[1]->went_offline_at);
    }

    public function test_it_creates_new_session_when_profile_is_online_but_has_no_open_log(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Profile 2',
            'slug' => 'test-profile-2',
        ]);

        Carbon::setTestNow('2026-05-23 00:00:01');

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subHour(),
        ]);

        // No ProviderOnlineLog record exists.
        $this->artisan('online-sessions:rollover')->assertSuccessful();

        $log = ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->sole();

        $midnight = Carbon::parse('2026-05-23 00:00:00', 'UTC');
        $this->assertTrue($log->went_online_at->equalTo($midnight));
        $this->assertNull($log->went_offline_at);
    }

    public function test_it_does_nothing_for_offline_profiles(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Offline Profile',
            'slug' => 'offline-profile',
        ]);

        Carbon::setTestNow('2026-05-23 00:00:01');

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 0,
        ]);

        $this->artisan('online-sessions:rollover')->assertSuccessful();

        $this->assertDatabaseCount('provider_online_logs', 0);
    }

    public function test_it_rolls_over_multiple_online_profiles_independently(): void
    {
        Carbon::setTestNow('2026-05-23 00:00:00');
        $midnight = now()->utc();

        Carbon::setTestNow('2026-05-22 22:00:00');

        $user1 = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile1 = ProviderProfile::query()->create([
            'user_id' => $user1->id,
            'name' => 'Profile A',
            'slug' => 'profile-a',
        ]);

        $user2 = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile2 = ProviderProfile::query()->create([
            'user_id' => $user2->id,
            'name' => 'Profile B',
            'slug' => 'profile-b',
        ]);

        foreach ([$user1 => $profile1, $user2 => $profile2] as $user => $profile) {
            OnlineUser::query()->create([
                'user_id' => $user->id,
                'provider_profile_id' => $profile->id,
                'status' => 'online',
                'usage_date' => today(),
                'usage_count' => 1,
                'online_started_at' => now(),
            ]);

            ProviderOnlineLog::query()->create([
                'user_id' => $user->id,
                'provider_profile_id' => $profile->id,
                'went_online_at' => now(),
            ]);
        }

        Carbon::setTestNow('2026-05-23 00:00:01');

        $this->artisan('online-sessions:rollover')->assertSuccessful();

        foreach ([$profile1, $profile2] as $profile) {
            $logs = ProviderOnlineLog::query()
                ->where('provider_profile_id', $profile->id)
                ->orderBy('went_online_at')
                ->get();

            $this->assertCount(2, $logs, "Profile {$profile->id} should have 2 logs after rollover");
            $this->assertNotNull($logs[0]->went_offline_at);
            $this->assertTrue($logs[0]->went_offline_at->equalTo($midnight));
            $this->assertTrue($logs[1]->went_online_at->equalTo($midnight));
            $this->assertNull($logs[1]->went_offline_at);
        }
    }
}
