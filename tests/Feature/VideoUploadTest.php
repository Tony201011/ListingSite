<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\UserVideo;
use App\Services\UserVideoStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckProfileSteps::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function providerWithProfile(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'test-' . $user->id,
        ]);
        return $user;
    }

    private function mockVideoStorage(): void
    {
        $mock = Mockery::mock(UserVideoStorageService::class);
        $mock->shouldReceive('store')->andReturnUsing(function ($user, $video, $username) {
            $uuid = fake()->uuid();
            return [
                'video_path' => "videos/{$username}/{$uuid}.mp4",
                'video_url' => "https://cdn.example.com/videos/{$uuid}.mp4",
            ];
        });
        $mock->shouldReceive('deletePaths')->andReturnNull();
        $this->app->instance(UserVideoStorageService::class, $mock);
    }

    // ---------------------------------------------------------------
    // Successful upload
    // ---------------------------------------------------------------

    public function test_video_upload_creates_database_record(): void
    {
        $this->mockVideoStorage();
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/videos/upload', [
            'videos' => [UploadedFile::fake()->create('myvideo.mp4', 1024, 'video/mp4')],
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Videos uploaded successfully.']);

        $this->assertDatabaseHas('user_videos', [
            'user_id' => $user->id,
            'original_name' => 'myvideo.mp4',
        ]);
    }

    // ---------------------------------------------------------------
    // Upload failure handling
    // ---------------------------------------------------------------

    public function test_video_upload_failure_returns_500_with_generic_message(): void
    {
        $user = $this->providerWithProfile();

        $mock = Mockery::mock(UserVideoStorageService::class);
        $mock->shouldReceive('store')->andThrow(new \RuntimeException('S3 connection failed'));
        $mock->shouldReceive('deletePath')->andReturnNull();
        $this->app->instance(UserVideoStorageService::class, $mock);

        $response = $this->actingAs($user)->postJson('/videos/upload', [
            'videos' => [UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4')],
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Failed to upload videos. No changes were saved.',
        ]);
    }

    public function test_video_upload_failure_returns_500_regardless_of_debug_mode(): void
    {
        config(['app.debug' => true]);
        $user = $this->providerWithProfile();

        $mock = Mockery::mock(UserVideoStorageService::class);
        $mock->shouldReceive('store')->andThrow(new \RuntimeException('Detailed S3 error'));
        $mock->shouldReceive('deletePath')->andReturnNull();
        $this->app->instance(UserVideoStorageService::class, $mock);

        $response = $this->actingAs($user)->postJson('/videos/upload', [
            'videos' => [UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4')],
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Failed to upload videos. No changes were saved.',
        ]);
        // Unlike photo uploads, the action catches exceptions internally
        // and returns a generic message (no debug details leaked)
        $response->assertJsonMissing(['error' => 'Detailed S3 error']);
    }

    public function test_video_upload_failure_does_not_leave_partial_db_records(): void
    {
        $user = $this->providerWithProfile();

        $callCount = 0;
        $mock = Mockery::mock(UserVideoStorageService::class);
        $mock->shouldReceive('store')->andReturnUsing(function () use (&$callCount) {
            $callCount++;
            if ($callCount === 2) {
                throw new \RuntimeException('Second upload failed');
            }
            return [
                'video_path' => 'videos/test/video1.mp4',
                'video_url' => 'https://cdn.example.com/videos/video1.mp4',
            ];
        });
        $mock->shouldReceive('deletePath')->andReturnNull();
        $this->app->instance(UserVideoStorageService::class, $mock);

        $response = $this->actingAs($user)->postJson('/videos/upload', [
            'videos' => [
                UploadedFile::fake()->create('video1.mp4', 1024, 'video/mp4'),
                UploadedFile::fake()->create('video2.mp4', 1024, 'video/mp4'),
            ],
        ]);

        $response->assertStatus(500);
        $this->assertDatabaseCount('user_videos', 0);
    }

    // ---------------------------------------------------------------
    // Validation
    // ---------------------------------------------------------------

    public function test_video_upload_rejects_non_video_files(): void
    {
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/videos/upload', [
            'videos' => [UploadedFile::fake()->image('photo.jpg')],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['videos.0']);
    }

    public function test_video_upload_requires_at_least_one_file(): void
    {
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/videos/upload', [
            'videos' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['videos']);
    }

    // ---------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------

    public function test_guest_cannot_upload_videos(): void
    {
        $response = $this->postJson('/videos/upload', [
            'videos' => [UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4')],
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_delete_another_users_video(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $video = UserVideo::create([
            'user_id' => $owner->id,
            'video_path' => 'videos/owner-video.mp4',
            'original_name' => 'owner-video.mp4',
        ]);

        $response = $this->actingAs($attacker)->deleteJson("/videos/{$video->id}");
        $response->assertForbidden();
    }
}
