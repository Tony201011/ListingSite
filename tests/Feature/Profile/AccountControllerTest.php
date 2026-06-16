<?php

namespace Tests\Feature\Profile;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_account_page_is_returned_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'user-'.$user->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get(route('account.delete-page'));

        $response->assertOk();
        $response->assertViewIs('auth.delete-account');
    }

    public function test_delete_account_soft_deletes_account_and_profiles_and_logs_user_out(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'user-'.$user->id,
        ]);

        DB::table('sessions')->insert([
            'id' => 'session-'.$user->id,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => base64_encode('payload'),
            'last_activity' => now()->timestamp,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->delete(route('account.destroy'), [
                'confirmation_text' => 'DELETE',
            ]);

        $response->assertRedirect('/signin');
        $response->assertSessionHas('success');

        $this->assertNull(auth()->user());

        $deletedUser = User::withTrashed()->find($user->id);
        $this->assertNotNull($deletedUser?->deleted_at);
        $this->assertSame('soft_deleted', $deletedUser?->account_status);
        $this->assertNotNull($deletedUser?->scheduled_purge_at);

        $this->assertDatabaseHas('provider_profiles', [
            'id' => $profile->id,
        ]);
        $this->assertNotNull(ProviderProfile::withTrashed()->find($profile->id)?->deleted_at);

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertDatabaseHas('account_lifecycle_audits', [
            'user_id' => $user->id,
            'action_type' => 'account_soft_deleted',
        ]);
    }

    public function test_destroy_returns_validation_error_when_confirmation_text_is_wrong(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
        ]);

        $response = $this->actingAs($user)->from('/delete-account')->delete(route('account.destroy'), [
            'confirmation_text' => 'delete',
        ]);

        $response->assertRedirect('/delete-account');
        $response->assertSessionHasErrors(['confirmation_text']);
    }

    public function test_guest_cannot_delete_account(): void
    {
        $response = $this->delete(route('account.destroy'), [
            'confirmation_text' => 'DELETE',
        ]);

        $response->assertRedirect();
    }
}
