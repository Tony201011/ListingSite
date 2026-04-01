<?php

return [
    'throttles' => [
        'booking_enquiry' => env('THROTTLE_BOOKING_ENQUIRY', '5,1'),
        'signup_page' => env('THROTTLE_SIGNUP_PAGE', '5,1'),
        'verify_otp' => env('THROTTLE_VERIFY_OTP', '5,1'),
        'resend_otp' => env('THROTTLE_RESEND_OTP', '3,1'),
        'password_reset_request' => env('THROTTLE_PASSWORD_RESET_REQUEST', '5,1'),
        'verification_send' => env('THROTTLE_VERIFICATION_SEND', '6,1'),
    ],
];
