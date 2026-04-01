<?php

namespace Tests\Feature;

use App\Actions\SendBookingEnquiryEmail;
use App\Models\BookingEnquiry;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BookingEnquiryFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createBookableProvider(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        return $user;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockBookingEmail(): void
    {
        $mock = Mockery::mock(SendBookingEnquiryEmail::class);
        $mock->shouldReceive('execute')->andReturnNull();
        $this->app->instance(SendBookingEnquiryEmail::class, $mock);
    }

    private function validPayload(int $userId, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $userId,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0400000000',
            'datetime' => '2026-04-15 14:00:00',
            'services' => 'Massage',
            'duration' => '60 mins',
            'location' => 'Sydney',
            'message' => 'Looking forward to it.',
        ], $overrides);
    }

    // ---------------------------------------------------------------
    // Successful submission
    // ---------------------------------------------------------------

    public function test_booking_enquiry_creates_database_record(): void
    {
        $this->mockBookingEmail();
        $provider = $this->createBookableProvider();

        $response = $this->from('/profile/test')->post('/booking-enquiry', $this->validPayload($provider->id));

        $response->assertRedirect('/profile/test');
        $response->assertSessionHas('success', 'Enquiry sent successfully!');

        $this->assertDatabaseHas('booking_enquiries', [
            'user_id' => $provider->id,
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'status' => 'pending',
            'is_read' => false,
        ]);
    }

    public function test_booking_enquiry_stores_all_optional_fields(): void
    {
        $this->mockBookingEmail();
        $provider = $this->createBookableProvider();

        $this->from('/booking')->post('/booking-enquiry', $this->validPayload($provider->id));

        $enquiry = BookingEnquiry::first();

        $this->assertSame('John Doe', $enquiry->name);
        $this->assertSame('john@example.com', $enquiry->email);
        $this->assertSame('0400000000', $enquiry->phone);
        $this->assertSame('Massage', $enquiry->services);
        $this->assertSame('60 mins', $enquiry->duration);
        $this->assertSame('Sydney', $enquiry->location);
        $this->assertSame('Looking forward to it.', $enquiry->message);
    }

    // ---------------------------------------------------------------
    // Validation failures
    // ---------------------------------------------------------------

    public function test_booking_enquiry_requires_valid_email(): void
    {
        $provider = $this->createBookableProvider();

        $response = $this->from('/booking')->post('/booking-enquiry', $this->validPayload($provider->id, [
            'email' => 'not-an-email',
        ]));

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('booking_enquiries', 0);
    }

    public function test_booking_enquiry_requires_email(): void
    {
        $provider = $this->createBookableProvider();

        $response = $this->from('/booking')->post('/booking-enquiry', $this->validPayload($provider->id, [
            'email' => '',
        ]));

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_booking_enquiry_requires_existing_user_id(): void
    {
        $response = $this->from('/booking')->post('/booking-enquiry', $this->validPayload(99999));

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['user_id']);
    }

    public function test_booking_enquiry_rejects_past_datetime(): void
    {
        $provider = $this->createBookableProvider();

        $response = $this->from('/booking')->post('/booking-enquiry', $this->validPayload($provider->id, [
            'datetime' => '2020-01-01 10:00:00',
        ]));

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['datetime']);
    }

    public function test_booking_enquiry_rejects_message_over_2000_chars(): void
    {
        $provider = $this->createBookableProvider();

        $response = $this->from('/booking')->post('/booking-enquiry', $this->validPayload($provider->id, [
            'message' => str_repeat('a', 2001),
        ]));

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['message']);
    }

    public function test_booking_enquiry_accepts_minimal_required_fields(): void
    {
        $this->mockBookingEmail();
        $provider = $this->createBookableProvider();

        $response = $this->from('/booking')->post('/booking-enquiry', [
            'user_id' => $provider->id,
            'email' => 'minimal@example.com',
        ]);

        $response->assertRedirect('/booking');
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('booking_enquiries', 1);
    }

    public function test_booking_enquiry_strips_unexpected_fields(): void
    {
        $this->mockBookingEmail();
        $provider = $this->createBookableProvider();

        $this->from('/booking')->post('/booking-enquiry', $this->validPayload($provider->id, [
            'admin_notes' => 'injected',
            'status' => 'approved',
        ]));

        $enquiry = BookingEnquiry::first();
        $this->assertSame('pending', $enquiry->status);
    }

    public function test_booking_enquiry_rejects_non_provider_target_without_listing(): void
    {
        $nonProvider = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->from('/booking')->post('/booking-enquiry', $this->validPayload($nonProvider->id));

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['user_id']);
        $this->assertDatabaseCount('booking_enquiries', 0);
    }

    public function test_booking_enquiry_is_stored_even_if_email_delivery_fails(): void
    {
        $provider = $this->createBookableProvider();

        $mock = Mockery::mock(SendBookingEnquiryEmail::class);
        $mock->shouldReceive('execute')
            ->once()
            ->andThrow(new \RuntimeException('SMTP timeout'));
        $this->app->instance(SendBookingEnquiryEmail::class, $mock);

        $response = $this->from('/booking')->post('/booking-enquiry', $this->validPayload($provider->id));

        $response->assertRedirect('/booking');
        $response->assertSessionHas('success', 'Enquiry sent successfully!');
        $this->assertDatabaseHas('booking_enquiries', [
            'user_id' => $provider->id,
            'email' => 'john@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_booking_enquiry_route_throttles_repeated_abuse_attempts(): void
    {
        $this->mockBookingEmail();
        $provider = $this->createBookableProvider();
        $payload = $this->validPayload($provider->id);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/booking-enquiry', $payload)->assertRedirect();
        }

        $this->post('/booking-enquiry', $payload)->assertStatus(429);
    }
}
