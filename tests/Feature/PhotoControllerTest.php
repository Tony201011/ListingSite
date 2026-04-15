<?php

namespace Tests\Feature\Profile;

use App\Actions\DeleteProfilePhoto;
use App\Actions\GetUserPhotos;
use App\Actions\SetPrimaryProfilePhoto;
use App\Actions\Support\ActionResult;
use App\Actions\UploadUserPhotos;
use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class PhotoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_index_returns_add_photo_view_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('add-photos'));

        $response->assertOk();
        $response->assertViewIs('profile.add-photo');
    }

    public function test_get_photos_returns_photos_view_with_action_data(): void
    {
        $user = User::factory()->create();
        $photo = ProfileImage::factory()->create(['user_id' => $user->id]);

        $getUserPhotos = Mockery::mock(GetUserPhotos::class);
        $getUserPhotos->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($user)))
            ->andReturn(ActionResult::success(['photos' => collect([$photo])]));

        $this->app->instance(GetUserPhotos::class, $getUserPhotos);

        $response = $this->actingAs($user)->get(route('photos'));

        $response->assertOk();
        $response->assertViewIs('profile.photos');
        $response->assertViewHas('photos');
    }

    public function test_upload_photos_returns_json_response_from_action(): void
    {
        $user = User::factory()->create();

        $uploadUserPhotos = Mockery::mock(UploadUserPhotos::class);
        $uploadUserPhotos->shouldReceive('execute')
            ->once()
            ->with($user, Mockery::type('array'))
            ->andReturn(ActionResult::success([
                'photos' => [
                    [
                        'id' => 1,
                        'image_path' => 'images/test/photo.jpg',
                        'thumbnail_path' => 'thumbnails/test/photo-thumb.jpg',
                        'image_url' => 'https://example.com/photo.jpg',
                        'thumbnail_url' => 'https://example.com/photo-thumb.jpg',
                        'is_primary' => true,
                    ],
                ],
            ], 'Photos uploaded successfully.'));

        $this->app->instance(UploadUserPhotos::class, $uploadUserPhotos);

        $response = $this->actingAs($user)->postJson(route('photos.upload'), [
            'photos' => [UploadedFile::fake()->image('test.jpg')],
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Photos uploaded successfully.',
        ]);
        $response->assertJsonStructure([
            'message',
            'photos',
        ]);
    }

    public function test_upload_photos_returns_500_json_when_action_throws_exception(): void
    {
        $user = User::factory()->create();

        $uploadUserPhotos = Mockery::mock(UploadUserPhotos::class);
        $uploadUserPhotos->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('S3 upload failed'));

        $this->app->instance(UploadUserPhotos::class, $uploadUserPhotos);

        $response = $this->actingAs($user)->postJson(route('photos.upload'), [
            'photos' => [UploadedFile::fake()->image('test.jpg')],
        ]);

        $response->assertStatus(500);
        $response->assertJsonFragment([
            'message' => 'Upload failed.',
        ]);
    }

    public function test_upload_photos_returns_debug_error_message_when_app_debug_is_true(): void
    {
        config(['app.debug' => true]);

        $user = User::factory()->create();

        $uploadUserPhotos = Mockery::mock(UploadUserPhotos::class);
        $uploadUserPhotos->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('Detailed upload error'));

        $this->app->instance(UploadUserPhotos::class, $uploadUserPhotos);

        $response = $this->actingAs($user)->postJson(route('photos.upload'), [
            'photos' => [UploadedFile::fake()->image('test.jpg')],
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Upload failed.',
            'error' => 'Detailed upload error',
        ]);
    }

    public function test_upload_photos_returns_generic_error_message_when_app_debug_is_false(): void
    {
        config(['app.debug' => false]);

        $user = User::factory()->create();

        $uploadUserPhotos = Mockery::mock(UploadUserPhotos::class);
        $uploadUserPhotos->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('Detailed upload error'));

        $this->app->instance(UploadUserPhotos::class, $uploadUserPhotos);

        $response = $this->actingAs($user)->postJson(route('photos.upload'), [
            'photos' => [UploadedFile::fake()->image('test.jpg')],
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Upload failed.',
            'error' => 'Something went wrong while uploading photos.',
        ]);
    }

    public function test_set_cover_returns_json_response_from_action(): void
    {
        $user = User::factory()->create();
        $photo = ProfileImage::factory()->create(['user_id' => $user->id]);

        $setPrimaryProfilePhoto = Mockery::mock(SetPrimaryProfilePhoto::class);
        $setPrimaryProfilePhoto->shouldReceive('execute')
            ->once()
            ->with($user, Mockery::on(fn ($arg) => $arg->is($photo)))
            ->andReturn(ActionResult::success([], 'Profile photo updated successfully.'));

        $this->app->instance(SetPrimaryProfilePhoto::class, $setPrimaryProfilePhoto);

        $response = $this->actingAs($user)->postJson(route('photos.setCover', $photo));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Profile photo updated successfully.',
        ]);
    }

    public function test_destroy_returns_json_response_from_action(): void
    {
        $user = User::factory()->create();
        $photo = ProfileImage::factory()->create(['user_id' => $user->id]);

        $deleteProfilePhoto = Mockery::mock(DeleteProfilePhoto::class);
        $deleteProfilePhoto->shouldReceive('execute')
            ->once()
            ->with($user, Mockery::on(fn ($arg) => $arg->is($photo)))
            ->andReturn(ActionResult::success([], 'Photo deleted successfully.'));

        $this->app->instance(DeleteProfilePhoto::class, $deleteProfilePhoto);

        $response = $this->actingAs($user)->deleteJson(route('photos.destroy', $photo));

        $response->assertOk();
        $response->assertJson([
            'message' => 'Photo deleted successfully.',
        ]);
    }

    public function test_guest_cannot_access_index(): void
    {
        $response = $this->get(route('add-photos'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_access_get_photos(): void
    {
        $response = $this->get(route('photos'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_upload_photos(): void
    {
        $response = $this->postJson(route('photos.upload'), [
            'photos' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_set_cover(): void
    {
        $photo = ProfileImage::factory()->create();

        $response = $this->postJson(route('photos.setCover', $photo));

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_photo(): void
    {
        $photo = ProfileImage::factory()->create();

        $response = $this->deleteJson(route('photos.destroy', $photo));

        $response->assertStatus(401);
    }
}
