<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\FrontendPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\PurchaseCreditController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Http\Request;
use App\Http\Controllers\MyRateController;
use App\Http\Controllers\MyVideosController;
use App\Http\Controllers\MyToursController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProviderRegisterController;
use Illuminate\Support\Facades\Crypt;
use App\Models\SiteSetting;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Storage;


Route::get('/site-password', function () {

    return view('site-password');
})->name('site-password.form');


/** frontend pages */
Route::get('/terms-and-conditions', [FrontendPageController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('/privacy-policy', [FrontendPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/refund-policy', [FrontendPageController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/faq', [FrontendPageController::class, 'faq'])->name('faq');
Route::get('/faq/load-more', [FrontendPageController::class, 'faqLoadMore'])->name('faq.load-more');
Route::get('/anti-spam-policy', [FrontendPageController::class, 'antiSpamPolicy'])->name('anti-spam-policy');



Route::get('/contact-us', [FrontendPageController::class, 'contactUs'])->name('contact-us');
Route::post('/contact-us', [FrontendPageController::class, 'submitContactUs'])->name('contact-us.submit');


Route::get('/', [HomeController::class, 'index']);
Route::get('/advanced-search', [HomeController::class, 'advancedSearch'])->name('advanced-search');
Route::get('/profile/{slug}', [HomeController::class, 'showProfile'])->name('profile.show');


Route::get('/about-us', [FrontendPageController::class, 'aboutUs'])->name('about-us');
Route::get('/help', [FrontendPageController::class, 'help'])->name('help');
Route::get('/naughty-corner', [FrontendPageController::class, 'naughtyCorner'])->name('naughty-corner');

Route::get('/membership', [FrontendPageController::class, 'membership'])->name('membership');

Route::get('/blog', [BlogController::class, 'index'])->name('blog');

Route::get('/blog/load-more', [BlogController::class, 'loadMore'])->name('blog.load-more');

Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/pricing', [FrontendPageController::class, 'pricing'])->name('pricing');

Route::get('/403', function () {
    abort(403);
});



/** frontend page end */


/** auth routes start */

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

/** auth routes end */


/** site password start */

Route::post('/site-password', function (Request $request) {
    $request->validate(['password' => 'required|string']);

    // Prefer DB-configured site password (encrypted cast), fallback to env
    $dbPassword = null;
    if (Schema::hasTable('site_settings')) {
        $setting = SiteSetting::query()->latest('updated_at')->first();
        if ($setting && $setting->site_password) {
            $dbPassword = $setting->site_password;
        }
    }

    $expected = $dbPassword ?? env('SITE_PASSWORD');

    if ($expected && hash_equals((string) $expected, (string) $request->password)) {
        $request->session()->regenerate();
        $request->session()->put('site_access', true);

        return redirect()->intended('/');
    }

    return back()->with('error', 'Wrong password');
})->name('site-password.submit');


/**site password end */



Route::middleware('guest')->group(function (): void {
    Route::get('/signup', [ProviderRegisterController::class, 'showSignupForm'])->name('signup');
    Route::post('/signup', [ProviderRegisterController::class, 'signup'])->name('signup.submit');
    Route::get('/signin', [ProviderRegisterController::class, 'showSigninForm'])->name('signin');
    Route::post('/signin', [ProviderRegisterController::class, 'signin'])->name('signin.submit');

    Route::get('/otp-verification', [ProviderRegisterController::class, 'otpVerficationForm'])->name('otp-verfication');
    Route::post('/verify-otp', [ProviderRegisterController::class, 'verifyOtp'])
        ->name('verify.otp');

    Route::post('/resend-otp', [ProviderRegisterController::class, 'resendOtp'])->name('resend.otp');

    Route::get('/reset-password', [PasswordResetController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('/reset-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('reset-password.submit');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password/update', [PasswordResetController::class, 'reset'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/add-photos', [PhotoController::class, 'index'])
        ->name('add-photos');

    Route::get('/add-photo', [PhotoController::class, 'index'])->name('photos.index');
    Route::get('/photos', [PhotoController::class, 'getPhotos'])->name('photos.list');
    Route::post('/upload-photos', [PhotoController::class, 'uploadPhotos'])->name('photos.upload');

    Route::post('/photos/{photo}/set-cover', [PhotoController::class, 'setCover'])->name('photos.setCover');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

    Route::get('/upload-video', [MyVideosController::class, 'uploadVideo'])
        ->name('upload-video');

    Route::get('/my-tours', [MyToursController::class, 'index'])->name('my-tours');
    Route::post('/my-tours', [MyToursController::class, 'store'])->name('my-tours.store');
    Route::put('/my-tours/{tour}', [MyToursController::class, 'update'])->name('my-tours.update');
    Route::delete('/my-tours/{tour}', [MyToursController::class, 'destroy'])->name('my-tours.destroy');
    Route::get('/search-cities', [MyToursController::class, 'search'])->name('search-cities');
    Route::get('/set-availability', [AvailabilityController::class, 'edit'])->name('availability.edit');
    Route::post('/set-availability', [AvailabilityController::class, 'update'])->name('availability.update');
    Route::get('/my-availability', [AvailabilityController::class, 'show'])->name('availability.show');
    Route::get('/change-password', [ProviderRegisterController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [ProviderRegisterController::class, 'updatePassword'])
    ->name('change-password.update');
    Route::get('/my-profile', [MyProfileController::class, 'myProfile'])
    ->name('my-profile-1');
    Route::get('/edit-profile', [MyProfileController::class, 'stepTwo'])
    ->name('edit-profile');
    Route::post('/edit-profile', [MyProfileController::class, 'save'])
    ->name('edit-profile.save');
    Route::get('/my-rate', [MyRateController::class, 'index'])
    ->name('my-rate');
    Route::post('/my-rate', [MyRateController::class, 'store'])
    ->name('my-rate.store');
    Route::delete('/my-rate/{rate}', [MyRateController::class, 'destroy'])
    ->name('my-rate.destroy');
    Route::put('/my-rate/{rate}', [MyRateController::class, 'update'])
    ->name('my-rate.update');
    Route::post('/groups', [MyRateController::class, 'storeGroup'])->name('my-rate.groups.store');
    Route::put('/groups/{group}', [MyRateController::class, 'updateGroup'])->name('my-rate.groups.update');
    Route::delete('/groups/{group}', [MyRateController::class, 'destroyGroup'])->name('my-rate.groups.destroy');
    });



Route::get('/delete-account', [ProviderRegisterController::class, 'deleteAccount'])->name('delete-account')->middleware('auth');

Route::get('/short-url', [ProviderRegisterController::class, 'shortUrl'])->name('short-url')->middleware('auth');

Route::post('/short-url/update', [ProviderRegisterController::class, 'updateShortUrl'])->name('short-url-update')->middleware('auth');


Route::get('/online-now', [ProviderRegisterController::class, 'onlineNow'])->name('online-now')->middleware('auth');
Route::post('/online-status', [ProviderRegisterController::class, 'onlineUpdateStatus'])->name('onlineUpdateStatus')->middleware('auth');



Route::get('/available-now', [ProviderRegisterController::class, 'availableNow'])->name('available-now')->middleware('auth');

Route::post('/available-status', [ProviderRegisterController::class, 'availableUpdateStatus'])->name('availableUpdateStatus')->middleware('auth');
Route::get('/set-forget', [ProviderRegisterController::class, 'setForget'])->name('set-forget')->middleware('auth');
Route::get('/my-babe-rank', [ProviderRegisterController::class, 'myBabeRank'])->name('my-babe-rank')->middleware('auth');
Route::get('/profile-message', [ProviderRegisterController::class, 'profileMessage'])->name('profile-message')->middleware('auth');

Route::post('/profile-message', [ProviderRegisterController::class, 'storeProfileMessage'])->name('storeProfileMessage')->middleware('auth');

Route::get('/hide-show-profile', [ProviderRegisterController::class, 'hideShowProfile'])->name('hide-show-profile')->middleware('auth');

Route::post('/hide-show-profile', [ProviderRegisterController::class, 'updateHideShowProfile'])->name('update-hide-show-profile')->middleware('auth');

Route::get('/click-here-to-verify', [ProviderRegisterController::class, 'clickHereToVerify'])->name('click-here-to-verify')->middleware('auth');

Route::get('/view-profile-setting', [ProviderRegisterController::class, 'viewProfileSetting'])->name('view-profile-setting')->middleware('auth');





Route::get('/after-image-upload', [ProviderRegisterController::class, 'afterImageUpload'])->name('after-image-upload')->middleware('auth');


Route::get('/babe-rank', [ProviderRegisterController::class, 'babeRank'])->name('babe-rank')->middleware('auth');







Route::get('/purchase-credit', [PurchaseCreditController::class, 'purchaseCredit'])->name('purchase-credit')->middleware('auth');


Route::post('/purchase-credit/checkout', [PurchaseCreditController::class, 'checkout'])->name('purchase-credit.checkout')->middleware('auth');


Route::get('/credit-history', [PurchaseCreditController::class, 'creditHistory'])->name('credit-history')->middleware('auth');






Route::get('/credit-history-last-month', [PurchaseCreditController::class, 'creditHistoryLastMonth'])->name('credit-history-last-month')->middleware('auth');


Route::get('/credit-history-last-month', [PurchaseCreditController::class, 'creditHistoryLastMonth'])->name('credit-history-last-month')->middleware('auth');



Route::get('/purchase-history', [PurchaseCreditController::class, 'purchaseHistory'])->name('purchase-history')->middleware('auth');







// social auth routes  enabled providers: google, facebook, apple (if needed, add more in SocialAuthController and config/services.php) when admin enables in settings
Route::get('/login', [SocialAuthController::class, 'showLogin'])->name('login');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

require __DIR__.'/escort-review.php';
