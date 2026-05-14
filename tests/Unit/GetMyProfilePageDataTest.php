<?php

namespace Tests\Unit;

use App\Actions\CalculateBabeRank;
use App\Actions\GetMyProfilePageData;
use App\Models\PhotoVerification;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GetMyProfilePageDataTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_photo_verification_step_is_completed_with_single_approved_submission(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider',
            'slug' => 'provider-'.$user->id,
        ]);

        PhotoVerification::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [['path' => 'verification/test.jpg']],
            'status' => 'approved',
            'submitted_at' => now(),
        ]);

        $calculateBabeRank = Mockery::mock(CalculateBabeRank::class);
        $calculateBabeRank->shouldReceive('execute')->once()->andReturn(['rank' => 0]);

        $result = (new GetMyProfilePageData($calculateBabeRank))->execute($user, $profile);

        $this->assertTrue($result['stepPhotoVerificationCompleted']);
    }

    public function test_photo_verification_step_is_not_completed_without_approved_submission(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider',
            'slug' => 'provider-'.$user->id,
        ]);

        PhotoVerification::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [['path' => 'verification/test.jpg']],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $calculateBabeRank = Mockery::mock(CalculateBabeRank::class);
        $calculateBabeRank->shouldReceive('execute')->once()->andReturn(['rank' => 0]);

        $result = (new GetMyProfilePageData($calculateBabeRank))->execute($user, $profile);

        $this->assertFalse($result['stepPhotoVerificationCompleted']);
    }

    public function test_listing_boost_statuses_include_expected_placeholders_and_active_dates(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider',
            'slug' => 'provider-'.$user->id,
            'featured_expires_at' => now()->addDay(),
            'free_listing_expires_at' => now()->subDay(),
            'home_featured_expires_at' => now()->addDays(2),
        ]);

        $calculateBabeRank = Mockery::mock(CalculateBabeRank::class);
        $calculateBabeRank->shouldReceive('execute')->once()->andReturn(['rank' => 0]);

        $result = (new GetMyProfilePageData($calculateBabeRank))->execute($user, $profile);
        $statuses = collect($result['listingBoostStatuses'])->pluck('value', 'label');

        $this->assertSame(
            $profile->featured_expires_at->timezone(config('app.timezone'))->format('d M Y, h:i A'),
            $statuses->get('Featured Expires')
        );
        $this->assertSame('Expired / Not set', $statuses->get('Free Listing Until'));
        $this->assertSame(
            $profile->home_featured_expires_at->timezone(config('app.timezone'))->format('d M Y, h:i A'),
            $statuses->get('Home Page Featured Until')
        );
        $this->assertSame('Not active', $statuses->get('Local Banner Until'));
        $this->assertSame('Not active', $statuses->get('Home Banner Until'));
    }
}
