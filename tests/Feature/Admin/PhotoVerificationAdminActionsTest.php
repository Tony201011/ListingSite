<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\PhotoVerifications\PhotoVerificationResource;
use App\Jobs\SendPhotoVerificationStatusEmailJob;
use App\Models\PhotoVerification;
use App\Models\ProviderProfile;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PhotoVerificationAdminActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_verification_saves_admin_note_and_dispatches_email(): void
    {
        Bus::fake();
        $this->createMailSetting();

        $verification = $this->createVerification();

        PhotoVerificationResource::approveVerification($verification, 'Looks good to us.');

        $verification->refresh();

        $this->assertSame('approved', $verification->status);
        $this->assertSame('Looks good to us.', $verification->admin_note);
        $this->assertTrue((bool) $verification->providerProfile?->fresh()?->is_verified);

        Bus::assertDispatched(SendPhotoVerificationStatusEmailJob::class, function (SendPhotoVerificationStatusEmailJob $job) use ($verification): bool {
            return $job->userId === $verification->user_id
                && $job->status === 'approved'
                && $job->adminNote === 'Looks good to us.'
                && $job->verificationStatus === null;
        });
    }

    public function test_save_admin_note_keeps_status_and_dispatches_note_email(): void
    {
        Bus::fake();
        $this->createMailSetting();

        $verification = $this->createVerification(status: 'pending');

        PhotoVerificationResource::saveAdminNote($verification, 'Please upload a clearer image.');

        $verification->refresh();

        $this->assertSame('pending', $verification->status);
        $this->assertSame('Please upload a clearer image.', $verification->admin_note);

        Bus::assertDispatched(SendPhotoVerificationStatusEmailJob::class, function (SendPhotoVerificationStatusEmailJob $job) use ($verification): bool {
            return $job->userId === $verification->user_id
                && $job->status === 'note_added'
                && $job->adminNote === 'Please upload a clearer image.'
                && $job->verificationStatus === 'pending';
        });
    }

    private function createMailSetting(): SmtpSetting
    {
        return SmtpSetting::create([
            'mail_mailer' => 'mailgun',
            'mailgun_domain' => 'mg.example.com',
            'mailgun_sandbox_domain' => 'sandbox.example.com',
            'mailgun_live_domain' => 'mg.example.com',
            'use_mailgun_sandbox' => true,
            'mailgun_secret' => 'test-secret',
            'mailgun_endpoint' => 'api.mailgun.net',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Listing Site',
            'is_enabled' => true,
        ]);
    }

    private function createVerification(string $status = 'pending'): PhotoVerification
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Test Provider',
            'slug' => 'test-provider-'.$user->id,
            'is_verified' => false,
        ]);

        return PhotoVerification::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'photos' => [
                ['url' => 'https://example.com/photo-1.jpg'],
                ['url' => 'https://example.com/photo-2.jpg'],
            ],
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }
}
