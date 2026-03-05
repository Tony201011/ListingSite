<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\FrontendPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\PurchaseCreditController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderRegisterController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/advanced-search', [HomeController::class, 'advancedSearch'])->name('advanced-search');

Route::get('/signup', function () {
    return view('signup');
});

Route::get('/about-us', [FrontendPageController::class, 'aboutUs'])->name('about-us');
Route::get('/help', [FrontendPageController::class, 'help'])->name('help');

Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/load-more', [BlogController::class, 'loadMore'])->name('blog.load-more');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::post('/signup', function () {
    return redirect('/otp-verification')->with('success', 'Signup submitted. Please verify your mobile number.');
})->name('signup.submit');

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');


Route::get('/signin', function () {
    return view('signin');
});

Route::post('/signin', function () {
    return redirect('/after-image-upload')->with('success', 'Signed in successfully.');
})->name('signin.submit');


Route::get('/reset-password', function () {
    return view('reset-password');
});

Route::post('/reset-password', function () {
    return redirect('/signin')->with('success', 'Password reset link sent to your email.');
})->name('reset-password.submit');

Route::get('/change-password', function () {
    return view('change-password');
});

Route::get('/delete-account', function () {
    return view('delete-account');
});


//after sign in page profile pagge when user not fill any informatiom

Route::get('/my-profile-1', function () {
    return view('my-profile-1');
});

Route::get('/my-profile-2', [MyProfileController::class, 'stepTwo']);


Route::get('/my-rate', function () {
    return view('my-rate');
});



Route::get('/my-availability', function () {
    return view('my-availability');
});


Route::get('/set-your-availability', function () {
    return view('set-your-availability');
});

Route::get('/add-photo', function () {
    return view('add-photo');
})->name('add-photo');

Route::get('/photos', function () {
    return view('photos');
});

Route::get('/my-videos', function () {
    return view('my-videos');
});

Route::get('/upload-video', function () {
    return view('upload-video');
})->name('upload-video');

Route::get('/my-tours', function () {
    return view('my-tours');
});

Route::get('/short-url', function () {
    return view('short-url');
});

Route::get('/online-now', function () {
    return view('online-now');
});

Route::get('/available-now', function () {
    return view('available-now');
});

Route::get('/set-and-forget', function () {
    return view('set-and-forget');
});

Route::get('/my-babe-rank', function () {
    return view('my-babe-rank');
});

Route::get('/profile-message', function () {
    return view('profile-message');
});

Route::get('/hide-profile', function () {
    return view('hide-profile');
});



Route::get('/click-here-to-verify', function () {
    return view('click-here-to-verify');
});


Route::get('/view-profile-setting', function () {
    return view('view-profile-setting');
});









Route::get('/otp-verification', function () {
    return view('otp-verification');
});


Route::get('/purchase-credit', function () {
    return view('purchase-credit');
});

Route::get('/membership', function () {
    return view('membership');
});

Route::get('/pricing', [FrontendPageController::class, 'pricing'])->name('pricing');

Route::post('/purchase-credit/checkout', [PurchaseCreditController::class, 'checkout'])->name('purchase-credit.checkout');




Route::get('/credit-history', function () {
    return view('credit-history');
});


Route::get('/credit-history-last-month', function () {
    return view('credit-history-last-month');
});

Route::get('/after-image-upload', function () {
    return view('after-image-upload');
});


Route::get('/purchase-history', function () {
    return view('purchase-history');
});


Route::get('/babe-rank-read-more', function () {
    return view('babe-rank-read-more');
});


Route::get('/terms-and-conditions', [FrontendPageController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('/privacy-policy', [FrontendPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/refund-policy', [FrontendPageController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/faq', [FrontendPageController::class, 'faq'])->name('faq');
Route::get('/faq/load-more', [FrontendPageController::class, 'faqLoadMore'])->name('faq.load-more');
Route::get('/anti-spam-policy', [FrontendPageController::class, 'antiSpamPolicy'])->name('anti-spam-policy');



Route::get('/contact-us', [FrontendPageController::class, 'contactUs'])->name('contact-us');
Route::post('/contact-us', [FrontendPageController::class, 'submitContactUs'])->name('contact-us.submit');

Route::get('/403', function () {
    abort(403);
});

Route::get('/login', [SocialAuthController::class, 'showLogin'])->name('login');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
Route::post('/provider/register', [ProviderRegisterController::class, 'store']);
