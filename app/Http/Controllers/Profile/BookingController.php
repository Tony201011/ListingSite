<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

use App\Actions\CreateBookingEnquiry;
use App\Http\Requests\SendBookingEnquiryRequest;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    public function __construct(
        private CreateBookingEnquiry $createBookingEnquiry
    ) {
    }

    public function send(SendBookingEnquiryRequest $request): RedirectResponse
    {
        $this->createBookingEnquiry->execute($request->validated());

        return back()->with('success', 'Enquiry sent successfully!');
    }
}
