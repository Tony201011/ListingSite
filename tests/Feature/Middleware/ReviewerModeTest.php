<?php

namespace Tests\Feature\Middleware;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewerModeTest extends TestCase
{
    use RefreshDatabase;

    private function createReviewer(): User
    {
        $reviewer = User::factory()->create([
            'role' => User::ROLE_REVIEWER,
            'email_verified_at' => now(),
        ]);

        ProviderProfile::query()->create([
            'user_id' => $reviewer->id,
            'name' => 'Reviewer Demo Profile',
            'slug' => 'reviewer-demo-profile-'.$reviewer->id,
        ]);

        return $reviewer;
    }

    public function test_reviewer_can_view_dashboard_pages_in_read_only_mode(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->get(route('my-listings'));

        $response->assertOk();
        $response->assertSee('Read-Only Reviewer Mode');
    }

    public function test_reviewer_mutation_requests_are_blocked(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->post(route('profiles.store'), [
            'name' => 'Attempted New Profile',
            'age_and_ownership_confirm' => true,
            'content_policy_confirm' => true,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('provider_profiles', 1);
    }

    public function test_reviewer_cannot_update_account_details(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->put(route('my-account.update'), [
            'name' => 'Changed Name',
            'mobile' => '0400000000',
            'email_notifications' => true,
            'message_alerts' => true,
            'marketing_emails' => false,
            'weekly_summary' => false,
        ]);

        $response->assertStatus(403);
    }

    public function test_reviewer_cannot_initiate_credit_purchase_checkout(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->post(route('purchase-credit.checkout'), [
            'credit_package_id' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_reviewer_cannot_update_profile_settings(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->post(route('short-url.update'), [
            'short_url' => 'reviewer-attempt',
        ]);

        $response->assertStatus(403);
    }

    public function test_reviewer_direct_admin_url_access_is_blocked(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->get('/admin');

        $response->assertStatus(403);
        $response->assertSee('read-only access', false);
    }

    public function test_reviewer_can_still_logout(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
