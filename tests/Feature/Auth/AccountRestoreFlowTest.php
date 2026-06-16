<?php

namespace Tests\Feature\Auth;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountRestoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_user_login_shows_restore_prompt(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => Hash::make('secret123'),
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now()->addDays(10),
        ]);

        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'user-'.$user->id,
        ]);

        $user->delete();

        $signinResponse = $this->post(route('signin.submit'), [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $signinResponse->assertRedirect();
        $signinResponse->assertSessionHasErrors([
            'email' => 'This account has been deleted and is currently within the restoration period.',
        ]);
        $signinResponse->assertSessionHas('show_restore_account', true);
    }

    public function test_soft_deleted_user_can_self_restore_immediately(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'password' => Hash::make('secret123'),
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now()->addDays(10),
        ]);

        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'user-'.$user->id,
        ]);

        $user->delete();

        $restoreResponse = $this->withSession([
            'restore_candidate_user_id' => $user->id,
        ])->post(route('account.restore.request'));

        $restoreResponse->assertRedirect('/my-profiles');
        $restoreResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertNull($user->deleted_at);
        $this->assertSame('active', $user->account_status);
        $this->assertNull($user->scheduled_purge_at);

        $this->assertDatabaseHas('account_lifecycle_audits', [
            'user_id' => $user->id,
            'action_type' => 'account_restored',
        ]);
    }

    public function test_restore_is_rejected_after_retention_period_expires(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now()->subDay(),
        ]);

        $user->delete();

        $response = $this->withSession([
            'restore_candidate_user_id' => $user->id,
        ])->post(route('account.restore.request'));

        $response->assertRedirect(route('signin'));
        $response->assertSessionHas('error', 'The restoration period has expired for this account.');

        $user->refresh();
        $this->assertNotNull($user->deleted_at);
    }
}
