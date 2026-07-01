<?php

namespace Tests\Feature\Auth;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_facebook_social_login_creates_account_and_logs_user_in(): void
    {
        Http::fake([
            'https://graph.facebook.com/v19.0/oauth/access_token' => Http::response([
                'access_token' => 'facebook-token',
                'token_type' => 'bearer',
            ], 200),
            'https://graph.facebook.com/v19.0/me*' => Http::response([
                'id' => 'facebook-user-123',
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'picture' => ['data' => ['url' => 'https://example.com/avatar.jpg']],
            ], 200),
        ]);

        $state = Str::random(40);

        $response = $this->withSession([
            'social_oauth_state' => [
                'facebook' => $state,
            ],
        ])->get('/auth/facebook/callback?code=abc123&state='.$state);

        $response->assertRedirect('/my-profiles');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'provider' => 'facebook',
            'provider_id' => 'facebook-user-123',
            'email' => 'jane@example.com',
        ]);

        $user = User::query()->where('provider', 'facebook')->where('provider_id', 'facebook-user-123')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_authenticated_user_can_connect_a_social_provider_to_their_account(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'provider' => null,
            'provider_id' => null,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/oauth/access_token' => Http::response([
                'access_token' => 'facebook-token',
                'token_type' => 'bearer',
            ], 200),
            'https://graph.facebook.com/v19.0/me*' => Http::response([
                'id' => 'facebook-user-123',
                'name' => 'Connected User',
                'email' => 'connected@example.com',
                'picture' => ['data' => ['url' => 'https://example.com/avatar.jpg']],
            ], 200),
        ]);

        $state = Str::random(40);

        $response = $this->actingAs($user)
            ->withSession([
                'social_oauth_state' => [
                    'facebook' => $state,
                ],
                'social_oauth_intent' => [
                    'facebook' => 'connect',
                ],
            ])
            ->get('/auth/facebook/callback?code=abc123&state='.$state);

        $response->assertRedirect('/my-account');

        $user->refresh();
        $this->assertSame('facebook', $user->provider);
        $this->assertSame('facebook-user-123', $user->provider_id);
        $this->assertSame('https://example.com/avatar.jpg', $user->avatar_url);
    }

    public function test_social_auth_redirect_uses_admin_configured_credentials_when_present(): void
    {
        SiteSetting::create([
            'facebook_client_id' => 'admin-facebook-id',
            'facebook_client_secret' => 'admin-facebook-secret',
            'facebook_redirect_uri' => 'https://example.com/auth/facebook/callback',
        ]);

        $redirectUrl = app(\App\Services\SocialAuthService::class)->redirectUrl('facebook')['url'];

        $this->assertStringContainsString('client_id=admin-facebook-id', $redirectUrl);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fauth%2Ffacebook%2Fcallback', $redirectUrl);
    }
}
