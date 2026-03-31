<?php

namespace Tests\Feature;

use App\Actions\SetPrimaryProfilePhoto;
use App\Actions\DeleteProfilePhoto;
use App\Actions\UploadUserPhotos;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Services\UserPhotoStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PhotoManagementTest extends TestCase
{
    use RefreshDatabase;

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

    private function mockPhotoStorage(): void
    {
        $mock = Mockery::mock(UserPhotoStorageService::class);
        $mock->shouldReceive('store')->andReturnUsing(function ($user, $photo, $username) {
            $uuid = fake()->uuid();
            return [
                'image_path' => "images/{$username}/{$uuid}.jpg",
                'thumbnail_path' => "thumbnails/{$username}/{$uuid}.jpg",
                'image_url' => "https://cdn.example.com/images/{$uuid}.jpg",
                'thumbnail_url' => "https://cdn.example.com/thumbnails/{$uuid}.jpg",
            ];
        });
        $mock->shouldReceive('deletePaths')->andReturnNull();
        $this->app->instance(UserPhotoStorageService::class, $mock);
    }

    // ---------------------------------------------------------------
    // Upload — first photo becomes primary
    // ---------------------------------------------------------------

    public function test_first_uploaded_photo_is_set_as_primary(): void
    {
        $this->mockPhotoStorage();
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [UploadedFile::fake()->image('photo1.jpg')],
        ]);

        $response->assertOk();

        $photo = ProfileImage::where('user_id', $user->id)->first();
        $this->assertTrue($photo->is_primary);
    }

    public function test_subsequent_photos_are_not_primary(): void
    {
        $this->mockPhotoStorage();
        $user = $this->providerWithProfile();

        // Upload first photo (becomes primary)
        $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [UploadedFile::fake()->image('photo1.jpg')],
        ]);

        // Upload second photo (should not be primary)
        $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [UploadedFile::fake()->image('photo2.jpg')],
        ]);

        $photos = ProfileImage::where('user_id', $user->id)->orderBy('id')->get();
        $this->assertCount(2, $photos);
        $this->assertTrue($photos[0]->is_primary);
        $this->assertFalse($photos[1]->is_primary);
    }

    public function test_upload_multiple_photos_at_once_only_first_is_primary(): void
    {
        $this->mockPhotoStorage();
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg'),
                UploadedFile::fake()->image('photo3.jpg'),
            ],
        ]);

        $response->assertOk();

        $photos = ProfileImage::where('user_id', $user->id)->orderBy('id')->get();
        $this->assertCount(3, $photos);
        $this->assertTrue($photos[0]->is_primary);
        $this->assertFalse($photos[1]->is_primary);
        $this->assertFalse($photos[2]->is_primary);
    }

    // ---------------------------------------------------------------
    // Set cover — changes primary photo
    // ---------------------------------------------------------------

    public function test_set_cover_changes_primary_photo(): void
    {
        $user = $this->providerWithProfile();

        $photo1 = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/photo1.jpg',
            'thumbnail_path' => 'thumbnails/photo1.jpg',
            'is_primary' => true,
        ]);

        $photo2 = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/photo2.jpg',
            'thumbnail_path' => 'thumbnails/photo2.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user)->postJson("/photos/{$photo2->id}/set-cover");

        $response->assertOk();
        $response->assertJson(['message' => 'Cover photo updated successfully.']);

        $photo1->refresh();
        $photo2->refresh();

        $this->assertFalse($photo1->is_primary);
        $this->assertTrue($photo2->is_primary);
    }

    public function test_set_cover_ensures_only_one_primary(): void
    {
        $user = $this->providerWithProfile();

        $photos = [];
        for ($i = 1; $i <= 5; $i++) {
            $photos[] = ProfileImage::create([
                'user_id' => $user->id,
                'image_path' => "images/photo{$i}.jpg",
                'thumbnail_path' => "thumbnails/photo{$i}.jpg",
                'is_primary' => $i === 1,
            ]);
        }

        // Set the 3rd photo as cover
        $this->actingAs($user)->postJson("/photos/{$photos[2]->id}/set-cover");

        $primaryCount = ProfileImage::where('user_id', $user->id)
            ->where('is_primary', true)
            ->count();

        $this->assertSame(1, $primaryCount);
        $this->assertTrue($photos[2]->fresh()->is_primary);
    }

    // ---------------------------------------------------------------
    // Delete — primary promotion
    // ---------------------------------------------------------------

    public function test_delete_primary_photo_promotes_next(): void
    {
        Storage::fake('s3');
        $user = $this->providerWithProfile();

        $primary = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/primary.jpg',
            'thumbnail_path' => 'thumbnails/primary.jpg',
            'is_primary' => true,
        ]);

        $next = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/next.jpg',
            'thumbnail_path' => 'thumbnails/next.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user)->deleteJson("/photos/{$primary->id}");

        $response->assertOk();
        $response->assertJson(['message' => 'Photo deleted successfully.']);

        $this->assertSoftDeleted('profile_images', ['id' => $primary->id]);
        $this->assertTrue($next->fresh()->is_primary);
    }

    public function test_delete_non_primary_photo_keeps_current_primary(): void
    {
        Storage::fake('s3');
        $user = $this->providerWithProfile();

        $primary = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/primary.jpg',
            'thumbnail_path' => 'thumbnails/primary.jpg',
            'is_primary' => true,
        ]);

        $other = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/other.jpg',
            'thumbnail_path' => 'thumbnails/other.jpg',
            'is_primary' => false,
        ]);

        $this->actingAs($user)->deleteJson("/photos/{$other->id}");

        $this->assertSoftDeleted('profile_images', ['id' => $other->id]);
        $this->assertTrue($primary->fresh()->is_primary);
    }

    public function test_delete_last_photo_leaves_no_primary(): void
    {
        Storage::fake('s3');
        $user = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/only.jpg',
            'thumbnail_path' => 'thumbnails/only.jpg',
            'is_primary' => true,
        ]);

        $this->actingAs($user)->deleteJson("/photos/{$photo->id}");

        $this->assertSoftDeleted('profile_images', ['id' => $photo->id]);
        $this->assertSame(
            0,
            ProfileImage::where('user_id', $user->id)->where('is_primary', true)->count()
        );
    }

    // ---------------------------------------------------------------
    // Upload validation
    // ---------------------------------------------------------------

    public function test_upload_rejects_non_image_files(): void
    {
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['photos.0']);
    }

    public function test_upload_rejects_oversized_files(): void
    {
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [UploadedFile::fake()->image('huge.jpg')->size(11000)],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['photos.0']);
    }

    public function test_upload_requires_at_least_one_photo(): void
    {
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['photos']);
    }

    // ---------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------

    public function test_guest_cannot_upload_photos(): void
    {
        $response = $this->postJson('/upload-photos', [
            'photos' => [UploadedFile::fake()->image('test.jpg')],
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_set_cover_on_another_users_photo(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/photo.jpg',
            'thumbnail_path' => 'thumbnails/photo.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($attacker)->postJson("/photos/{$photo->id}/set-cover");
        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_photo(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/photo.jpg',
            'thumbnail_path' => 'thumbnails/photo.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($attacker)->deleteJson("/photos/{$photo->id}");
        $response->assertForbidden();
    }
}
