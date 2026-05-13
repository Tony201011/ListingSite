<?php

namespace Tests\Feature\Profile;

use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\PhotoVerification;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([EnsureProfileSelected::class, CheckProfileSteps::class]);
        Storage::fake(config('media.upload_disk'));
    }

    private function createProvider(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Test Provider',
            'slug' => 'test-provider-'.$user->id,
        ]);

        return $user;
    }

    private function actingAsProvider(User $user): static
    {
        $profile = $user->providerProfile;

        return $this->actingAs($user)->withSession([
            'active_provider_profile_id' => $profile?->id,
        ]);
    }

    // ---------------------------------------------------------------
    // index
    // ---------------------------------------------------------------

    public function test_provider_can_view_verify_photo_page(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->get(route('verify.photos'));

        $response->assertOk();
        $response->assertViewIs('profile.verify-photo');
        $response->assertViewHasAll(['latestVerification', 'lastTwoPhotos', 'exampleImages']);
        $response->assertSee('Upload verification photos');
        $response->assertSee('Drag and drop photos here');
    }

    public function test_guest_cannot_view_verify_photo_page(): void
    {
        $response = $this->get(route('verify.photos'));

        $response->assertRedirect();
    }

    // ---------------------------------------------------------------
    // upload
    // ---------------------------------------------------------------

    public function test_provider_can_upload_verification_photos(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.upload'), [
            'photos' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('back.jpg'),
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'verification' => ['id', 'status', 'submitted_at', 'photos'],
        ]);
        $response->assertJson(['verification' => ['status' => 'pending']]);

        $this->assertDatabaseHas('photo_verifications', [
            'provider_profile_id' => $user->providerProfile->id,
            'status' => 'pending',
        ]);
    }

    public function test_upload_is_blocked_when_an_active_verification_already_exists(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        PhotoVerification::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [['path' => 'verification/test/photo1.jpg', 'url' => 'http://example.com/photo1.jpg', 'name' => 'photo1.jpg']],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.upload'), [
            'photos' => [
                UploadedFile::fake()->image('new1.jpg'),
                UploadedFile::fake()->image('new2.jpg'),
            ],
        ]);

        $response->assertForbidden();
        $response->assertJsonFragment(['message' => 'You already have an active verification submission. Please delete your existing verification photos before uploading new ones.']);
    }

    public function test_upload_allowed_after_existing_record_is_soft_deleted(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $old = PhotoVerification::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        $old->delete(); // soft-delete it so a new submission is allowed

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.upload'), [
            'photos' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('back.jpg'),
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['verification' => ['status' => 'pending']]);
    }

    public function test_upload_requires_at_least_two_photos(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.upload'), [
            'photos' => [UploadedFile::fake()->image('one.jpg')],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['photos']);
    }

    public function test_upload_rejects_non_image_files(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.upload'), [
            'photos' => [
                UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['photos.0']);
    }

    public function test_upload_rejects_oversized_files(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.upload'), [
            'photos' => [
                UploadedFile::fake()->image('huge.jpg')->size(11000),
                UploadedFile::fake()->image('huge2.jpg')->size(11000),
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['photos.0']);
    }

    public function test_guest_cannot_upload_verification_photos(): void
    {
        $response = $this->postJson(route('photo-verification.upload'), []);

        $response->assertUnauthorized();
    }

    // ---------------------------------------------------------------
    // deletePhoto — partial deletion (photo removed, record kept)
    // ---------------------------------------------------------------

    public function test_deleting_one_photo_from_multi_photo_record_keeps_record_active(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $photos = [
            ['path' => 'verification/test/photo1.jpg', 'url' => 'http://example.com/photo1.jpg', 'name' => 'photo1.jpg'],
            ['path' => 'verification/test/photo2.jpg', 'url' => 'http://example.com/photo2.jpg', 'name' => 'photo2.jpg'],
        ];

        $verification = PhotoVerification::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => $photos,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.delete-photo'), [
            'path' => 'verification/test/photo1.jpg',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Photo deleted successfully.']);

        // Record must still exist (not soft-deleted)
        $verification->refresh();
        $this->assertNull($verification->deleted_at);

        // Remaining photos array should have only photo2
        $this->assertCount(1, $verification->photos);
        $this->assertSame('verification/test/photo2.jpg', $verification->photos[0]['path']);
    }

    // ---------------------------------------------------------------
    // deletePhoto — full deletion (record soft-deleted when empty)
    // ---------------------------------------------------------------

    public function test_deleting_last_photo_soft_deletes_the_record(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $verification = PhotoVerification::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [
                ['path' => 'verification/test/only.jpg', 'url' => 'http://example.com/only.jpg', 'name' => 'only.jpg'],
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.delete-photo'), [
            'path' => 'verification/test/only.jpg',
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Photo deleted successfully.']);

        $this->assertSoftDeleted('photo_verifications', ['id' => $verification->id]);
    }

    public function test_deleting_last_photo_allows_new_upload(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $verification = PhotoVerification::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [
                ['path' => 'verification/test/only.jpg', 'url' => 'http://example.com/only.jpg', 'name' => 'only.jpg'],
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAsProvider($user)->postJson(route('photo-verification.delete-photo'), [
            'path' => 'verification/test/only.jpg',
        ])->assertOk();

        // Should now be able to upload again
        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.upload'), [
            'photos' => [
                UploadedFile::fake()->image('new1.jpg'),
                UploadedFile::fake()->image('new2.jpg'),
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['verification' => ['status' => 'pending']]);
        // $verification was the first (now soft-deleted) record; the new upload creates a second record.
        // assertSoftDeleted only matches soft-deleted rows, so the new active record is separate.
        $this->assertSoftDeleted('photo_verifications', ['id' => $verification->id]);
        // 2 total rows: 1 soft-deleted (old) + 1 active (new)
        $this->assertDatabaseCount('photo_verifications', 2);
    }

    public function test_delete_photo_returns_404_for_unknown_path(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        PhotoVerification::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [
                ['path' => 'verification/test/real.jpg', 'url' => 'http://example.com/real.jpg', 'name' => 'real.jpg'],
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)->postJson(route('photo-verification.delete-photo'), [
            'path' => 'verification/test/does-not-exist.jpg',
        ]);

        $response->assertNotFound();
        $response->assertJson(['message' => 'Photo not found.']);
    }

    public function test_guest_cannot_delete_verification_photo(): void
    {
        $response = $this->postJson(route('photo-verification.delete-photo'), ['path' => 'some/path.jpg']);

        $response->assertUnauthorized();
    }
}
