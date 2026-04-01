<?php

namespace App\Actions;

use App\Models\BookingEnquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateBookingEnquiry
{
    public function __construct(
        private SendBookingEnquiryEmail $sendBookingEnquiryEmail
    ) {}

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

        try {
            $this->sendBookingEnquiryEmail->execute($enquiry);
        } catch (Throwable $e) {
            // Keep the saved enquiry so staff can still process it manually.
            Log::warning('Booking enquiry email dispatch failed after enquiry was saved.', [
                'booking_enquiry_id' => $enquiry->id,
                'user_id' => $enquiry->user_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $enquiry;
    }
}
