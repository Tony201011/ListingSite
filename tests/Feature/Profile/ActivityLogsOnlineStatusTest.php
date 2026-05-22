<?php

namespace Tests\Feature\Profile;

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
            ->assertSee('Total Sessions')
            ->assertDontSee('Other Profile');
    }

    private function createCompleteProfile(): array
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Profile One',
            'slug' => 'profile-one',
            'introduction_line' => 'Intro',
            'profile_text' => 'Complete profile text',
            'age_group_id' => 1,
            'hair_color_id' => 1,
            'hair_length_id' => 1,
            'ethnicity_id' => 1,
            'body_type_id' => 1,
            'bust_size_id' => 1,
            'your_length_id' => 1,
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
