<?php

namespace App\Http\Controllers\Profile;

use App\Actions\CreateBookingEnquiry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendBookingEnquiryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    public function __construct(
        private CreateBookingEnquiry $createBookingEnquiry
    ) {}

    public function send(SendBookingEnquiryRequest $request): JsonResponse|RedirectResponse
    {
        $this->createBookingEnquiry->execute($request->validated());

        if (! $request->expectsJson()) {
            return back()->with('success', 'Enquiry sent successfully!');
        }

        return response()->json(['message' => 'Enquiry sent successfully!']);
    }
}
