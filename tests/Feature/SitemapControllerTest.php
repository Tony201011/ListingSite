<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createProfile(string $slug, string $status = 'approved'): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Escort '.str_replace('-', ' ', $slug),
            'slug' => $slug,
            'profile_status' => $status,
            'age' => 24,
        ]);
    }

    public function test_sitemap_index_returns_xml_and_links_child_sitemaps(): void
    {
        $this->createProfile('ruby-escort');

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('/sitemaps/static.xml', false);
        $response->assertSee('/sitemaps/profiles-1.xml', false);
    }

    public function test_static_sitemap_contains_core_public_pages(): void
    {
        $response = $this->get('/sitemaps/static.xml');

        $response->assertOk();
        $response->assertSee(url('/'), false);
        $response->assertSee(route('advanced-search'), false);
        $response->assertSee(route('blog'), false);
    }

    public function test_profile_sitemap_contains_only_approved_profiles(): void
    {
        $this->createProfile('approved-one', 'approved');
        $this->createProfile('pending-one', 'pending');

        $response = $this->get('/sitemaps/profiles-1.xml');

        $response->assertOk();
        $response->assertSee(route('profile.show', ['slug' => 'approved-one']), false);
        $response->assertDontSee(route('profile.show', ['slug' => 'pending-one']), false);
    }

    public function test_profile_sitemap_auto_updates_when_new_profile_is_added(): void
    {
        $this->createProfile('first-approved');
        $initial = $this->get('/sitemaps/profiles-1.xml');
        $initial->assertOk();
        $initial->assertDontSee(route('profile.show', ['slug' => 'new-approved']), false);

        $this->createProfile('new-approved');
        $updated = $this->get('/sitemaps/profiles-1.xml');

        $updated->assertOk();
        $updated->assertSee(route('profile.show', ['slug' => 'new-approved']), false);
    }

    public function test_sitemap_is_accessible_when_site_password_is_enabled(): void
    {
        SiteSetting::query()->create([
            'site_password' => 'secret123',
            'site_password_enabled' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
    }
}
