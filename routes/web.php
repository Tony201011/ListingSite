<?php

use App\Models\AntiSpamPolicy;
use App\Models\Faq;
use App\Models\PrivacyPolicy;
use App\Models\RefundPolicy;
use App\Models\TermCondition;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderRegisterController;

use App\Models\Category;
Route::get('/', function () {
    $categories = Category::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    return view('home', compact('categories'));
});

Route::get('/signup', function () {
    return view('signup');
});


Route::get('/signin', function () {
    return view('signin');
});

Route::get('/otp-verification', function () {
    return view('otp-verification');
});


Route::get('/terms-and-conditions', function () {
    $terms = TermCondition::query()
        ->where('is_active', true)
        ->latest('updated_at')
        ->first();

    return view('terms-and-conditions', [
        'terms' => $terms,
    ]);
})->name('terms-and-conditions');

Route::get('/privacy-policy', function () {
    $policy = PrivacyPolicy::query()
        ->where('is_active', true)
        ->latest('updated_at')
        ->first();

    return view('privacy-policy', [
        'policy' => $policy,
    ]);
})->name('privacy-policy');

Route::get('/refund-policy', function () {
    $policy = RefundPolicy::query()
        ->where('is_active', true)
        ->latest('updated_at')
        ->first();

    return view('refund-policy', [
        'policy' => $policy,
    ]);
})->name('refund-policy');

Route::get('/faq', function () {
    $faqs = Faq::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    return view('faq', [
        'faqs' => $faqs,
    ]);
})->name('faq');

Route::get('/anti-spam-policy', function () {
    $policy = AntiSpamPolicy::query()
        ->where('is_active', true)
        ->latest('updated_at')
        ->first();

    return view('anti-spam-policy', [
        'policy' => $policy,
    ]);
})->name('anti-spam-policy');



Route::get('/contact-us', function () {
    return view('contact-us', [
        'contact' => 'contact-us',
    ]);
})->name('contact-us');

Route::get('/login', [SocialAuthController::class, 'showLogin'])->name('login');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
Route::post('/provider/register', [ProviderRegisterController::class, 'store']);
