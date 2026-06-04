<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeVerificationUrl(User $user, ?Carbon $expiry = null): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            $expiry ?? Carbon::now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    public function test_unauthenticated_user_can_verify_email_via_signed_link(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = $this->makeVerificationUrl($user);

        $response = $this->get($url);

        $response->assertRedirect('/my-profiles');
        $response->assertSessionHas('success');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_already_verified_user_visiting_link_is_redirected_to_profile_selection_and_logged_in(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $url = $this->makeVerificationUrl($user);

        $response = $this->get($url);

        $response->assertRedirect('/my-profiles');
        $response->assertSessionHas('success');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_hash_returns_403(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => 'badhash',
            ]
        );

        $response = $this->get($url);

        $response->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_nonexistent_user_id_returns_404(): void
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => 99999,
                'hash' => sha1('nonexistent@example.com'),
            ]
        );

        $response = $this->get($url);

        $response->assertNotFound();
    }

    public function test_expired_link_returns_403(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = $this->makeVerificationUrl($user, Carbon::now()->subMinutes(1));

        $response = $this->get($url);

        $response->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_authenticated_unverified_user_can_resend_verification_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->post(route('verification.send'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'A new verification email has been sent.');
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->assertDatabaseHas('email_logs', [
            'recipient' => $user->email,
            'subject' => 'Verify Your Email Address',
            'type' => 'verify_email',
            'status' => 'sent',
        ]);
    }
}
