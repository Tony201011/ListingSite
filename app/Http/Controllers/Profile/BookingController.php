<?php

namespace App\Http\Controllers\Profile;

use App\Actions\CreateBookingEnquiry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendBookingEnquiryRequest;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private CreateBookingEnquiry $createBookingEnquiry
    ) {}

    public function send(SendBookingEnquiryRequest $request): JsonResponse
    {
        $this->createBookingEnquiry->execute($request->validated());

        return response()->json(['message' => 'Enquiry sent successfully!']);
    }
}
