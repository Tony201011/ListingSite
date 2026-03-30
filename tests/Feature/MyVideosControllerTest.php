<?php

namespace Tests\Feature\Profile;

use App\Actions\DeleteUserVideo;
use App\Actions\GetUserVideos;
use App\Actions\UploadUserVideos;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MyVideosControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_index_returns_upload_video_view_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.videos.index'));

        $response->assertOk();
        $response->assertViewIs('profile.upload-video');
    }

    public function test_get_videos_returns_my_videos_view_with_action_data(): void
    {
        $user = User::factory()->create();

        $getUserVideos = Mockery::mock(GetUserVideos::class);
        $getUserVideos->shouldReceive('execute')
            ->once()
            ->with($user->id)
            ->andReturn([
                'videos' => [
                    [
                        'id' => 1,
                        'video_url' => 'https://example.com/video.mp4',
                        'original_name' => 'video.mp4',
                    ],
                ],
            ]);

        $this->app->instance(GetUserVideos::class, $getUserVideos);

        $response = $this->actingAs($user)->get(route('profile.videos.list'));

        $response->assertOk();
        $response->assertViewIs('profile.my-videos');
        $response->assertViewHas('videos');
    }

    public function test_upload_videos_returns_json_response_from_action(): void
    {
        $user = User::factory()->create();

        $uploadUserVideos = Mockery::mock(UploadUserVideos::class);
        $uploadUserVideos->shouldReceive('execute')
            ->once()
            ->with($user, Mockery::type('array'))
            ->andReturn([
                'status' => 200,
                'data' => [
                    'message' => 'Videos uploaded successfully.',
                    'videos' => [
                        [
                            'id' => 1,
                            'video_path' => 'videos/test/video.mp4',
                            'video_url' => 'https://example.com/video.mp4',
                            'original_name' => 'video.mp4',
                        ],
                    ],
                ],
            ]);

        $this->app->instance(UploadUserVideos::class, $uploadUserVideos);

        $response = $this->actingAs($user)->postJson(route('profile.videos.upload'), [
            'videos' => [],
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Videos uploaded successfully.',
        ]);
        $response->assertJsonStructure([
            'message',
            'videos',
        ]);
    }

    public function test_upload_videos_returns_500_json_when_action_throws_exception(): void
    {
        $user = User::factory()->create();

        $uploadUserVideos = Mockery::mock(UploadUserVideos::class);
        $uploadUserVideos->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('S3 upload failed'));

        $this->app->instance(UploadUserVideos::class, $uploadUserVideos);

        $response = $this->actingAs($user)->postJson(route('profile.videos.upload'), [
            'videos' => [],
        ]);

        $response->assertStatus(500);
        $response->assertJsonFragment([
            'message' => 'Upload failed.',
        ]);
    }

    public function test_upload_videos_returns_debug_error_message_when_app_debug_is_true(): void
    {
        config(['app.debug' => true]);

        $user = User::factory()->create();

        $uploadUserVideos = Mockery::mock(UploadUserVideos::class);
        $uploadUserVideos->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('Detailed upload error'));

        $this->app->instance(UploadUserVideos::class, $uploadUserVideos);

        $response = $this->actingAs($user)->postJson(route('profile.videos.upload'), [
            'videos' => [],
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Upload failed.',
            'error' => 'Detailed upload error',
        ]);
    }

    public function test_upload_videos_returns_generic_error_message_when_app_debug_is_false(): void
    {
        config(['app.debug' => false]);

        $user = User::factory()->create();

        $uploadUserVideos = Mockery::mock(UploadUserVideos::class);
        $uploadUserVideos->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('Detailed upload error'));

        $this->app->instance(UploadUserVideos::class, $uploadUserVideos);

        $response = $this->actingAs($user)->postJson(route('profile.videos.upload'), [
            'videos' => [],
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Upload failed.',
            'error' => 'Something went wrong while uploading videos.',
        ]);
    }

    public function test_destroy_returns_json_response_from_action(): void
    {
        $user = User::factory()->create();
        $video = UserVideo::factory()->create();

        $deleteUserVideo = Mockery::mock(DeleteUserVideo::class);
        $deleteUserVideo->shouldReceive('execute')
            ->once()
            ->with($user, Mockery::on(fn ($arg) => $arg->is($video)))
            ->andReturn([
                'status' => 200,
                'data' => [
                    'message' => 'Video deleted successfully.',
                ],
            ]);

        $this->app->instance(DeleteUserVideo::class, $deleteUserVideo);

        $response = $this->actingAs($user)->deleteJson(route('profile.videos.destroy', $video));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Video deleted successfully.',
        ]);
    }

    public function test_guest_cannot_access_index(): void
    {
        $response = $this->get(route('profile.videos.index'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_access_get_videos(): void
    {
        $response = $this->get(route('profile.videos.list'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_upload_videos(): void
    {
        $response = $this->postJson(route('profile.videos.upload'), [
            'videos' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_video(): void
    {
        $video = UserVideo::factory()->create();

        $response = $this->deleteJson(route('profile.videos.destroy', $video));

        $response->assertStatus(401);
    }
}
