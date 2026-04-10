<?php

namespace Tests\Feature\ContactUs;

use App\Actions\CreateContactInquiry;
use App\Actions\SendContactInquiryReplyEmail;
use App\Models\ContactInquiry;
use App\Models\ContactUsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class ContactUsTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_blocked' => false,
            'password' => Hash::make('AdminPass123!'),
        ], $overrides));
    }

    private function createActiveContactPage(array $overrides = []): ContactUsPage
    {
        return ContactUsPage::create(array_merge([
            'title' => 'Contact Us',
            'subtitle' => 'Send us a message.',
            'support_heading' => 'Support Info',
            'response_time' => 'within 24 hours',
            'support_email' => 'support@example.com',
            'category_label' => 'general',
            'enable_name_field' => true,
            'enable_email_field' => true,
            'enable_subject_field' => true,
            'enable_message_field' => true,
            'is_active' => true,
        ], $overrides));
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    // ---------------------------------------------------------------
    // Frontend – GET /contact-us
    // ---------------------------------------------------------------

    public function test_contact_us_page_loads_successfully(): void
    {
        $response = $this->get(route('contact-us'));

        $response->assertOk();
        $response->assertSee('Contact Us');
    }

    public function test_contact_us_page_shows_active_page_title(): void
    {
        $this->createActiveContactPage(['title' => 'Get In Touch']);

        $response = $this->get(route('contact-us'));

        $response->assertOk();
        $response->assertSee('Get In Touch');
    }

    public function test_contact_us_page_shows_form_fields_when_enabled(): void
    {
        $this->createActiveContactPage();

        $response = $this->get(route('contact-us'));

        $response->assertOk();
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="subject"', false);
        $response->assertSee('name="message"', false);
    }

    public function test_contact_us_page_hides_disabled_fields(): void
    {
        $this->createActiveContactPage([
            'enable_name_field' => false,
            'enable_subject_field' => false,
        ]);

        $response = $this->get(route('contact-us'));

        $response->assertOk();
        $response->assertDontSee('name="name"', false);
        $response->assertDontSee('name="subject"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="message"', false);
    }

    public function test_contact_us_page_shows_disabled_notice_when_all_fields_disabled(): void
    {
        $this->createActiveContactPage([
            'enable_name_field' => false,
            'enable_email_field' => false,
            'enable_subject_field' => false,
            'enable_message_field' => false,
        ]);

        $response = $this->get(route('contact-us'));

        $response->assertOk();
        $response->assertSee('Contact form is currently disabled');
    }

    // ---------------------------------------------------------------
    // Frontend – POST /contact-us
    // ---------------------------------------------------------------

    public function test_submitting_contact_form_creates_inquiry_and_redirects_with_success(): void
    {
        Queue::fake();
        $this->createActiveContactPage();

        $response = $this->post(route('contact-us.submit'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Test inquiry',
            'message' => 'This is a test message.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Test inquiry',
            'message' => 'This is a test message.',
            'status' => 'pending',
            'is_read' => false,
        ]);
    }

    public function test_submitting_contact_form_dispatches_email_notification_job(): void
    {
        Bus::fake();
        $this->createActiveContactPage();

        $this->post(route('contact-us.submit'), [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'subject' => 'Hello',
            'message' => 'Please contact me.',
        ]);

        Bus::assertDispatched(\App\Jobs\SendContactInquiryEmailJob::class);
    }

    public function test_contact_form_validation_requires_name_when_enabled(): void
    {
        $this->createActiveContactPage(['enable_name_field' => true]);

        $response = $this->post(route('contact-us.submit'), [
            'email' => 'jane@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_contact_form_validation_requires_email_when_enabled(): void
    {
        $this->createActiveContactPage(['enable_email_field' => true]);

        $response = $this->post(route('contact-us.submit'), [
            'name' => 'Jane',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_contact_form_validation_rejects_invalid_email(): void
    {
        $this->createActiveContactPage(['enable_email_field' => true]);

        $response = $this->post(route('contact-us.submit'), [
            'name' => 'Jane',
            'email' => 'not-an-email',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_contact_form_validation_requires_message_when_enabled(): void
    {
        $this->createActiveContactPage(['enable_message_field' => true]);

        $response = $this->post(route('contact-us.submit'), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Test',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_contact_form_validation_enforces_name_max_length(): void
    {
        $this->createActiveContactPage(['enable_name_field' => true]);

        $response = $this->post(route('contact-us.submit'), [
            'name' => str_repeat('a', 101),
            'email' => 'jane@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_contact_form_validation_enforces_message_max_length(): void
    {
        $this->createActiveContactPage(['enable_message_field' => true]);

        $response = $this->post(route('contact-us.submit'), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Test',
            'message' => str_repeat('a', 3001),
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_create_contact_inquiry_action_stores_record(): void
    {
        Queue::fake();

        $action = app(CreateContactInquiry::class);

        $inquiry = $action->execute([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'subject' => 'Question',
            'message' => 'A question for you.',
        ]);

        $this->assertInstanceOf(ContactInquiry::class, $inquiry);
        $this->assertDatabaseHas('contact_inquiries', [
            'id' => $inquiry->id,
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'status' => 'pending',
            'is_read' => false,
        ]);
    }

    // ---------------------------------------------------------------
    // Admin panel – Contact Inquiries listing
    // ---------------------------------------------------------------

    public function test_admin_can_view_contact_inquiries_in_admin_panel(): void
    {
        $admin = $this->createAdmin();

        ContactInquiry::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'subject' => 'Help needed',
            'message' => 'I need help.',
            'status' => 'pending',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin/pages/contact-inquiries');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_contact_inquiries_admin_page(): void
    {
        $provider = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $response = $this->actingAs($provider)->get('/admin/pages/contact-inquiries');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_contact_inquiries_admin_page(): void
    {
        $response = $this->get('/admin/pages/contact-inquiries');

        $response->assertRedirect('/admin/login');
    }

    // ---------------------------------------------------------------
    // ContactInquiry model
    // ---------------------------------------------------------------

    public function test_contact_inquiry_can_be_soft_deleted(): void
    {
        $inquiry = ContactInquiry::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Subject',
            'message' => 'Message',
            'status' => 'pending',
        ]);

        $inquiry->delete();

        $this->assertSoftDeleted('contact_inquiries', ['id' => $inquiry->id]);
        $this->assertNull(ContactInquiry::find($inquiry->id));
    }

    public function test_contact_inquiry_can_be_marked_as_read(): void
    {
        $inquiry = ContactInquiry::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Subject',
            'message' => 'Message',
            'status' => 'pending',
            'is_read' => false,
        ]);

        $inquiry->update(['is_read' => true]);

        $this->assertTrue($inquiry->fresh()->is_read);
    }

    public function test_contact_inquiry_status_transitions(): void
    {
        $inquiry = ContactInquiry::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Subject',
            'message' => 'Message',
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $inquiry->status);

        $inquiry->update(['status' => 'replied', 'admin_reply' => 'We replied!', 'replied_at' => now()]);
        $this->assertEquals('replied', $inquiry->fresh()->status);
        $this->assertEquals('We replied!', $inquiry->fresh()->admin_reply);

        $inquiry->update(['status' => 'closed']);
        $this->assertEquals('closed', $inquiry->fresh()->status);
    }

    public function test_contact_inquiry_reply_sets_replied_at_timestamp(): void
    {
        $inquiry = ContactInquiry::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Subject',
            'message' => 'Message',
            'status' => 'pending',
        ]);

        $this->assertNull($inquiry->replied_at);

        $inquiry->update([
            'admin_reply' => 'Hello! We have reviewed your inquiry.',
            'status' => 'replied',
            'is_read' => true,
            'replied_at' => now(),
        ]);

        $this->assertNotNull($inquiry->fresh()->replied_at);
    }

    // ---------------------------------------------------------------
    // SendContactInquiryReplyEmail action
    // ---------------------------------------------------------------

    public function test_send_reply_email_action_dispatches_reply_job(): void
    {
        Bus::fake();

        $inquiry = ContactInquiry::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Subject',
            'message' => 'Message',
            'admin_reply' => 'Our answer',
            'status' => 'replied',
            'replied_at' => now(),
        ]);

        $action = app(SendContactInquiryReplyEmail::class);
        $action->execute($inquiry);

        Bus::assertDispatched(\App\Jobs\SendContactInquiryReplyEmailJob::class, function ($job) use ($inquiry) {
            return $job->inquiryId === $inquiry->id;
        });
    }
}
