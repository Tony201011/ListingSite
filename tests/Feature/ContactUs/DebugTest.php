<?php

namespace Tests\Feature\ContactUs;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_admin_auth(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin/pages/contact-inquiries');
        echo "Admin response status: " . $response->getStatusCode() . "\n";
        echo "Admin redirect: " . ($response->headers->get('Location') ?? 'none') . "\n";
    }

    public function test_debug_provider_guard(): void
    {
        $provider = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::create([
            'user_id' => $provider->id,
            'name' => $provider->name,
            'slug' => 'provider-'.$provider->id,
        ]);

        // Try actingAs with 'admin' guard specifically
        $response = $this->actingAs($provider, 'admin')
            ->get('/admin/pages/contact-inquiries');
        
        echo "Provider (admin guard) response status: " . $response->getStatusCode() . "\n";
        echo "Provider redirect: " . ($response->headers->get('Location') ?? 'none') . "\n";
    }
}
