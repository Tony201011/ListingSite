<?php

namespace Tests\Feature\Profile;

use App\Actions\CreateBookingEnquiry;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BookingControllerTest extends TestCase
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

    public function test_send_creates_booking_enquiry_and_redirects_back_with_success_message(): void
    {
        $user = $this->createBookableProvider();

        $validatedPayload = [
            'user_id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0400000000',
            'datetime' => '2030-01-01 14:30:00',
            'services' => 'Massage, Facial',
            'duration' => '90 mins',
            'location' => 'Sydney',
            'message' => 'Looking to book an appointment.',
        ];

        $createBookingEnquiry = Mockery::mock(CreateBookingEnquiry::class);
        $createBookingEnquiry->shouldReceive('execute')
            ->once()
            ->with($validatedPayload);

        $this->app->instance(CreateBookingEnquiry::class, $createBookingEnquiry);

        $response = $this->from('/booking')->post(route('booking.enquiry'), $validatedPayload);

        $response->assertRedirect('/booking');
        $response->assertSessionHas('success', 'Enquiry sent successfully!');
    }

    public function test_send_does_not_call_action_when_email_is_missing(): void
    {
        $user = $this->createBookableProvider();

        $createBookingEnquiry = Mockery::mock(CreateBookingEnquiry::class);
        $createBookingEnquiry->shouldNotReceive('execute');

        $this->app->instance(CreateBookingEnquiry::class, $createBookingEnquiry);

        $response = $this->from('/booking')->post(route('booking.enquiry'), [
            'user_id' => $user->id,
            'name' => 'John Doe',
            'email' => '',
            'phone' => '0400000000',
        ]);

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_send_does_not_call_action_when_email_is_invalid(): void
    {
        $user = $this->createBookableProvider();

        $createBookingEnquiry = Mockery::mock(CreateBookingEnquiry::class);
        $createBookingEnquiry->shouldNotReceive('execute');

        $this->app->instance(CreateBookingEnquiry::class, $createBookingEnquiry);

        $response = $this->from('/booking')->post(route('booking.enquiry'), [
            'user_id' => $user->id,
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'phone' => '0400000000',
        ]);

        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_send_passes_only_validated_data_to_action(): void
    {
        $user = $this->createBookableProvider();

        $requestPayload = [
            'user_id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0412345678',
            'datetime' => '2030-01-02 10:00:00',
            'services' => 'Hair Styling',
            'duration' => '60 mins',
            'location' => 'Melbourne',
            'message' => 'Please confirm availability.',
            'unexpected_field' => 'should not be passed',
        ];

        $expectedValidatedPayload = [
            'user_id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0412345678',
            'datetime' => '2030-01-02 10:00:00',
            'services' => 'Hair Styling',
            'duration' => '60 mins',
            'location' => 'Melbourne',
            'message' => 'Please confirm availability.',
        ];

        $createBookingEnquiry = Mockery::mock(CreateBookingEnquiry::class);
        $createBookingEnquiry->shouldReceive('execute')
            ->once()
            ->with($expectedValidatedPayload);

        $this->app->instance(CreateBookingEnquiry::class, $createBookingEnquiry);

        $response = $this->from('/booking')->post(route('booking.enquiry'), $requestPayload);

        $response->assertRedirect('/booking');
        $response->assertSessionHas('success', 'Enquiry sent successfully!');
    }
}
