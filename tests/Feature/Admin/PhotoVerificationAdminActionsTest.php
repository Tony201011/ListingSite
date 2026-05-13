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

    public function test_summarize_photo_count_returns_human_readable_total(): void
    {
        $verification = $this->createVerification();

        $this->assertSame('2 photos', PhotoVerificationResource::summarizePhotoCount($verification->photo_urls));
    }

    public function test_get_submitted_photo_details_keeps_uploaded_metadata(): void
    {
        $verification = $this->createVerification();

        $this->assertSame([
            [
                'label' => 'Photo 1',
                'name' => 'front.jpg',
                'path' => null,
                'url' => 'https://example.com/photo-1.jpg',
            ],
            [
                'label' => 'Photo 2',
                'name' => 'side.jpg',
                'path' => null,
                'url' => 'https://example.com/photo-2.jpg',
            ],
        ], PhotoVerificationResource::getSubmittedPhotoDetails($verification));
    }

    public function test_render_photo_review_grid_outputs_clear_photo_cards(): void
    {
        $verification = $this->createVerification();

        $html = PhotoVerificationResource::renderPhotoReviewGrid($verification)->toHtml();

        $this->assertStringContainsString('Photo 1', $html);
        $this->assertStringContainsString('https://example.com/photo-1.jpg', $html);
        $this->assertStringContainsString('Open full size', $html);
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
                ['url' => 'https://example.com/photo-1.jpg', 'name' => 'front.jpg'],
                ['url' => 'https://example.com/photo-2.jpg', 'name' => 'side.jpg'],
            ],
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }
}
