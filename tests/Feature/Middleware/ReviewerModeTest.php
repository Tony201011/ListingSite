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
