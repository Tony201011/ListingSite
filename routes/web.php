<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\FrontendPageController;
use App\Http\Controllers\PurchaseCreditController;
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

Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::post('/signup', function () {
    return redirect('/otp-verification')->with('success', 'Signup submitted. Please verify your mobile number.');
})->name('signup.submit');


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


Route::get('/my-profile-2', function () {
    return view('my-profile-2');
});


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
Route::get('/anti-spam-policy', [FrontendPageController::class, 'antiSpamPolicy'])->name('anti-spam-policy');



Route::get('/contact-us', function () {
    return view('contact-us', [
        'contact' => 'contact-us',
    ]);
})->name('contact-us');

Route::post('/contact-us', function () {
    return back()->with('success', 'Your message has been sent successfully.');
})->name('contact-us.submit');

Route::get('/403', function () {
    abort(403);
});

Route::get('/login', [SocialAuthController::class, 'showLogin'])->name('login');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
Route::post('/provider/register', [ProviderRegisterController::class, 'store']);
