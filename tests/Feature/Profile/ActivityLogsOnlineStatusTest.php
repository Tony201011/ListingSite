<?php

namespace Tests\Feature\Profile;

use App\Models\Category;
use App\Models\LoginLog;
use App\Models\ProfileImage;
use App\Models\ProviderOnlineLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogsOnlineStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_go_online_and_go_offline_create_profile_activity_log_entries(): void
    {
        [$user, $profile] = $this->createCompleteProfile();

        Carbon::setTestNow('2026-05-22 10:00:00');

        $this->actingAs($user)
            ->postJson(route('profiles.online-status', $profile), ['status' => 'online'])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'online']);

        $onlineLog = ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->latest('went_online_at')
            ->first();

        $this->assertNotNull($onlineLog);
        $this->assertNull($onlineLog->went_offline_at);
        $this->assertSame($user->id, $onlineLog->user_id);
        $this->assertTrue($onlineLog->went_online_at->equalTo(now()));

        Carbon::setTestNow('2026-05-22 11:15:00');

        $this->actingAs($user)
            ->postJson(route('profiles.online-status', $profile), ['status' => 'offline'])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'offline']);

        $onlineLog->refresh();

        $this->assertNotNull($onlineLog->went_offline_at);
        $this->assertSame(4500, $onlineLog->duration_seconds);
        $this->assertTrue($onlineLog->went_offline_at->equalTo(now()));
    }

    public function test_activity_logs_page_uses_selected_profiles_online_sessions(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $selectedProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        $otherProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Other Profile',
            'slug' => 'other-profile',
        ]);

        Carbon::setTestNow('2026-05-22 11:30:00');

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $selectedProfile->id,
            'went_online_at' => now()->copy()->subHours(2)->subMinutes(30),
            'went_offline_at' => now()->copy()->subHours(1)->subMinutes(30),
            'duration_seconds' => 3600,
        ]);

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $selectedProfile->id,
            'went_online_at' => now()->copy()->subMinutes(30),
        ]);

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $otherProfile->id,
            'went_online_at' => now()->copy()->subMinutes(20),
            'went_offline_at' => now()->copy()->subMinutes(10),
            'duration_seconds' => 600,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $selectedProfile->id])
            ->get(route('activity-logs'));

        $response->assertOk()
            ->assertViewHas('profile', fn (ProviderProfile $profile) => $profile->is($selectedProfile))
            ->assertViewHas('activity', function (array $activity): bool {
                return ($activity['total_sessions'] ?? null) === 2
                    && ($activity['current_session_seconds'] ?? null) === 1800
                    && ($activity['total_online_seconds'] ?? null) === 5400;
            })
            ->assertSee('Selected Profile')
            ->assertSee('01h 30m 00s')
            ->assertSee('00h 30m 00s')
            ->assertDontSee('Total Sessions')
            ->assertDontSee('Total Time Online')
            ->assertDontSee('Current Session')
            ->assertDontSee('Other Profile');
    }

    public function test_activity_logs_page_falls_back_to_legacy_login_logs_when_profile_logs_absent(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        Carbon::setTestNow('2026-05-22 11:30:00');

        LoginLog::query()->create([
            'user_id' => $user->id,
            'created_at' => now()->copy()->subHours(2)->subMinutes(45),
            'logged_out_at' => now()->copy()->subHours(1)->subMinutes(45),
            'duration_seconds' => 3600,
        ]);

        LoginLog::query()->create([
            'user_id' => $user->id,
            'created_at' => now()->copy()->subMinutes(45),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('activity-logs'));

        $response->assertOk()
            ->assertViewHas('activity', function (array $activity): bool {
                return ($activity['total_sessions'] ?? null) === 2
                    && ($activity['current_session_seconds'] ?? null) === 2700
                    && ($activity['total_online_seconds'] ?? null) === 6300;
            })
            ->assertSee('01h 45m 00s')
            ->assertSee('00h 45m 00s');
    }

    public function test_activity_logs_page_includes_legacy_history_before_profile_online_logs(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        Carbon::setTestNow('2026-05-22 11:30:00');

        LoginLog::query()->create([
            'user_id' => $user->id,
            'created_at' => now()->copy()->subDays(50)->subHour(),
            'logged_out_at' => now()->copy()->subDays(50),
            'duration_seconds' => 3600,
        ]);

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => now()->copy()->subDays(10)->subHours(2),
            'went_offline_at' => now()->copy()->subDays(10)->subHour(),
            'duration_seconds' => 3600,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('activity-logs'));

        $response->assertOk()
            ->assertViewHas('activity', function (array $activity): bool {
                return ($activity['total_sessions'] ?? null) === 2
                    && ($activity['total_online_seconds'] ?? null) === 7200;
            })
            ->assertSee(now()->copy()->subDays(50)->format('d M Y'))
            ->assertSee(now()->copy()->subDays(10)->format('d M Y'));
    }

    public function test_activity_logs_page_supports_last_30_days_filter(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        Carbon::setTestNow('2026-05-22 11:30:00');

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => now()->copy()->subDays(10)->subHour(),
            'went_offline_at' => now()->copy()->subDays(10),
            'duration_seconds' => 3600,
        ]);

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => now()->copy()->subDays(45)->subHour(),
            'went_offline_at' => now()->copy()->subDays(45),
            'duration_seconds' => 3600,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('activity-logs', ['range' => '30d']));

        $response->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => ($filters['range'] ?? null) === '30d')
            ->assertViewHas('activity', fn (array $activity): bool => ($activity['total_sessions'] ?? null) === 1)
            ->assertSee('during last 30 days')
            ->assertDontSee('07 Apr 2026');
    }

    public function test_activity_logs_page_supports_custom_date_range_filter(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        Carbon::setTestNow('2026-05-22 11:30:00');

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => Carbon::parse('2026-05-03 09:00:00'),
            'went_offline_at' => Carbon::parse('2026-05-03 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => Carbon::parse('2026-05-18 09:00:00'),
            'went_offline_at' => Carbon::parse('2026-05-18 10:30:00'),
            'duration_seconds' => 5400,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('activity-logs', [
                'range' => 'custom',
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-10',
            ]));

        $response->assertOk()
            ->assertViewHas('filters', function (array $filters): bool {
                return ($filters['range'] ?? null) === 'custom'
                    && ($filters['date_from_value'] ?? null) === '2026-05-01'
                    && ($filters['date_to_value'] ?? null) === '2026-05-10';
            })
            ->assertViewHas('activity', function (array $activity): bool {
                return ($activity['total_sessions'] ?? null) === 1
                    && ($activity['total_online_seconds'] ?? null) === 3600;
            })
            ->assertSee('during 01 May 2026 to 10 May 2026')
            ->assertDontSee('18 May 2026');
    }

    public function test_activity_logs_calculates_duration_from_timestamps_when_stored_duration_is_zero(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        Carbon::setTestNow('2026-05-22 11:30:00');

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => now()->copy()->subHours(2),
            'went_offline_at' => now()->copy()->subHour(),
            'duration_seconds' => 0,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('activity-logs'));

        $response->assertOk()
            ->assertViewHas('activity', function (array $activity): bool {
                return ($activity['total_sessions'] ?? null) === 1
                    && ($activity['total_online_seconds'] ?? null) === 3600;
            })
            ->assertSee('01h 00m 00s');
    }

    public function test_activity_logs_uses_stored_duration_when_timestamp_diff_is_zero(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        Carbon::setTestNow('2026-05-22 11:30:00');

        ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => Carbon::parse('2026-05-22 09:00:00'),
            'went_offline_at' => Carbon::parse('2026-05-22 09:00:00'),
            'duration_seconds' => 3600,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('activity-logs'));

        $response->assertOk()
            ->assertViewHas('activity', function (array $activity): bool {
                return ($activity['total_sessions'] ?? null) === 1
                    && ($activity['total_online_seconds'] ?? null) === 3600;
            })
            ->assertSee('01h 00m 00s');
    }

    public function test_activity_logs_groups_and_formats_session_times_using_app_timezone(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile',
        ]);

        $originalTimezone = config('app.timezone');
        config(['app.timezone' => 'Australia/Sydney']);

        try {
            Carbon::setTestNow('2026-05-23 12:00:00');

            ProviderOnlineLog::query()->create([
                'user_id' => $user->id,
                'provider_profile_id' => $profile->id,
                'went_online_at' => Carbon::parse('2026-05-22 23:30:00', 'UTC'),
                'went_offline_at' => Carbon::parse('2026-05-23 00:30:00', 'UTC'),
                'duration_seconds' => 3600,
            ]);

            $response = $this->actingAs($user)
                ->withSession(['active_provider_profile_id' => $profile->id])
                ->get(route('activity-logs'));

            $response->assertOk()
                ->assertSee('23 May 2026')
                ->assertSee('09:30 AM')
                ->assertSee('10:30 AM')
                ->assertDontSee('22 May 2026');
        } finally {
            config(['app.timezone' => $originalTimezone]);
        }
    }

    private function createCompleteProfile(): array
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $category = Category::query()->firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category']
        );

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Profile One',
            'slug' => 'profile-one',
            'introduction_line' => 'Intro',
            'profile_text' => 'Complete profile text',
            'age_group_id' => $category->id,
            'hair_color_id' => $category->id,
            'hair_length_id' => $category->id,
            'ethnicity_id' => $category->id,
            'body_type_id' => $category->id,
            'bust_size_id' => $category->id,
            'your_length_id' => $category->id,
            'availability' => 'available',
            'contact_method' => 'phone',
            'phone_contact_preference' => 'call',
            'time_waster_shield' => 'enabled',
            'primary_identity' => [1],
            'attributes' => [1],
            'services_style' => [1],
            'services_provided' => [1],
        ]);

        ProfileImage::factory()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
        ]);

        return [$user, $profile];
    }
}
