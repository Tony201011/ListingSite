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

    public function test_reviewer_can_view_dashboard_pages(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->get(route('my-listings'));

        $response->assertOk();
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

    /**
     * A reviewer visiting /admin while only authenticated on the web guard (not the admin
     * guard used by the Filament panel) is redirected to the admin login page.
     * The admin panel is accessible to reviewer accounts only after authenticating via
     * /admin/login with the admin guard.
     */
    public function test_reviewer_without_admin_guard_session_is_redirected_to_admin_login(): void
    {
        $reviewer = $this->createReviewer();

        // actingAs uses the web guard by default — the admin panel requires the 'admin' guard.
        $response = $this->actingAs($reviewer)->get('/admin');

        // Filament's Authenticate middleware redirects unauthenticated (on admin guard) users
        // to the admin login page rather than serving the dashboard.
        $response->assertRedirect();
        $this->assertStringContainsString('/admin', $response->headers->get('Location'));
    }

    /**
     * A reviewer authenticated on the admin guard can access the admin panel dashboard.
     * canAccessPanel returns true for ROLE_REVIEWER on the 'admin' panel.
     */
    public function test_reviewer_authenticated_on_admin_guard_can_access_admin_panel(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer, 'admin')->get('/admin');

        // Filament renders the dashboard (or redirects internally to /admin/dashboard).
        $response->assertSuccessful();
    }

    /**
     * A reviewer authenticated on the admin guard can access user account management.
     */
    public function test_reviewer_can_access_account_management_in_admin_panel(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer, 'admin')->get('/admin/account-management/account');

        $response->assertSuccessful();
    }

    /**
     * A reviewer authenticated on the admin guard can access financial transaction data.
     */
    public function test_reviewer_can_access_purchase_transactions_in_admin_panel(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer, 'admin')->get('/admin/purchase-transactions');

        $response->assertSuccessful();
    }

    public function test_reviewer_can_still_logout(): void
    {
        $reviewer = $this->createReviewer();

        $response = $this->actingAs($reviewer)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
