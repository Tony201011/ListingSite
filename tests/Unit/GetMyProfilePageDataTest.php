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
}
