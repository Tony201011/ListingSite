<?php

namespace Tests\Feature\Profile;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_account_page_is_returned_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'user-'.$user->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('my-account'));

        $response->assertOk();
        $response->assertViewIs('my-account');
    }

    public function test_update_notification_preferences_updates_user_settings(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'email_notifications' => true,
            'message_alerts' => true,
            'marketing_emails' => true,
            'weekly_summary' => true,
        ]);
        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'user-'.$user->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->put(route('my-account.update'), [
                'form_section' => 'notification_preferences',
                'email_notifications' => '0',
                'message_alerts' => '1',
                'marketing_emails' => '0',
                'weekly_summary' => '1',
            ]);

        $response->assertRedirect(route('my-account'));
        $response->assertSessionHas('success', 'Notification preferences updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_notifications' => 0,
            'message_alerts' => 1,
            'marketing_emails' => 0,
            'weekly_summary' => 1,
        ]);
    }
}
