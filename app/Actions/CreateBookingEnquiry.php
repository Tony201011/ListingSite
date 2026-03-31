<?php

namespace App\Actions;

use App\Models\BookingEnquiry;
use Illuminate\Support\Facades\DB;

class CreateBookingEnquiry
{
    public function __construct(
        private SendBookingEnquiryEmail $sendBookingEnquiryEmail
    ) {
    }

    public function execute(array $validated): BookingEnquiry
    {
        $enquiry = DB::transaction(function () use ($validated) {
            return BookingEnquiry::create([
                'user_id' => $validated['user_id'],
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'booking_datetime' => $validated['datetime'] ?? null,
                'services' => $validated['services'] ?? null,
                'duration' => $validated['duration'] ?? null,
                'location' => $validated['location'] ?? null,
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
                'is_read' => false,
            ]);
        });

        $this->sendBookingEnquiryEmail->execute($enquiry);

        return $enquiry;
    }
}
