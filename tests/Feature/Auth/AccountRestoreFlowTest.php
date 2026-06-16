<?php

namespace Tests\Feature\Auth;

use App\Models\AccountRestoreRequest;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountRestoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_user_login_is_blocked_and_restore_request_can_be_submitted(): void
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

        $restoreResponse = $this->withSession([
            'restore_candidate_user_id' => $user->id,
        ])->post(route('account.restore.request'), [
            'request_reason' => 'Need access back',
        ]);

        $restoreResponse->assertRedirect(route('signin'));
        $restoreResponse->assertSessionHas('success');

        $this->assertDatabaseHas('account_restore_requests', [
            'user_id' => $user->id,
            'status' => AccountRestoreRequest::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('account_lifecycle_audits', [
            'user_id' => $user->id,
            'action_type' => 'restore_request_submitted',
        ]);
    }

    public function test_restore_request_is_rejected_after_retention_period_expires(): void
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

        $this->assertDatabaseCount('account_restore_requests', 0);
    }
}
