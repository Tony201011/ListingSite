<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\CheckProfileSteps;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createUser(): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        // Create a default profile for the user
        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'test-'.$user->id,
        ]);

        return $user;
    }

    private function completeProfileStepOne(ProviderProfile $profile): void
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category']
        );

        $profile->update([
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
    }

    public function test_password_change_with_valid_data_succeeds(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->from(route('change-password'))
            ->post(route('change-password.update'), [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewPassword123!',
                'new_password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertRedirect(route('change-password'));
        $response->assertSessionHas('success', 'Your password has been changed successfully.');
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_password_change_with_wrong_current_password_fails(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson(route('change-password.update'), [
            'current_password' => 'WrongPassword123',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_password');
        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    public function test_password_change_requires_confirmation(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson(route('change-password.update'), [
            'current_password' => 'OldPassword123',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('new_password');
    }

    public function test_password_change_requires_all_fields(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson(route('change-password.update'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password', 'new_password']);
    }

    public function test_guest_cannot_change_password(): void
    {
        $response = $this->post(route('change-password.update'), [
            'current_password' => 'OldPassword123',
            'new_password' => 'NewPassword123!',
            'new_password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('signin'));
    }

    public function test_password_change_page_is_accessible_to_authenticated_user(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('change-password'));

        $response->assertOk();
        $response->assertViewIs('auth.change-password');
    }

    public function test_password_change_is_blocked_when_profile_steps_are_incomplete(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('change-password'));

        $response->assertRedirect(route('my-profile'));
        $response->assertSessionHas('error', 'Please complete your profile first.');
    }

    public function test_change_email_page_is_blocked_when_profile_steps_are_incomplete(): void
    {
        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $this->completeProfileStepOne($profile);

        $response = $this->actingAs($user)->get(route('change-email'));

        $response->assertRedirect(route('my-profile'));
        $response->assertSessionHas('error', 'Please upload at least one photo.');
    }
}
