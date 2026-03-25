<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FrontendPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\MyRateController;
use App\Http\Controllers\MyToursController;
use App\Http\Controllers\MyVideosController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PhotoVerificationController;
use App\Http\Controllers\ProviderRegisterController;
use App\Http\Controllers\PurchaseCreditController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\SuburbController;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/site-password', function () {
    return view('site-password');
})->name('site-password.form');


Route::redirect('/login', '/');

/** frontend pages */
Route::get('/terms-and-conditions', [FrontendPageController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('/privacy-policy', [FrontendPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/refund-policy', [FrontendPageController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/faq', [FrontendPageController::class, 'faq'])->name('faq');
Route::get('/faq/load-more', [FrontendPageController::class, 'faqLoadMore'])->name('faq.load-more');
Route::get('/anti-spam-policy', [FrontendPageController::class, 'antiSpamPolicy'])->name('anti-spam-policy');

Route::get('api/suburbs/search', [SuburbController::class, 'search'])->name('api.suburbs.search');

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
/** frontend pages end */

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
/** site password end */

Route::middleware('guest')->group(function (): void {
    Route::get('/signup', [ProviderRegisterController::class, 'showSignupForm'])->name('signup');
    Route::post('/signup', [ProviderRegisterController::class, 'signup'])->name('signup.submit');

    Route::get('/signin', [ProviderRegisterController::class, 'showSigninForm'])->name('signin');
    Route::post('/signin', [ProviderRegisterController::class, 'signin'])->name('signin.submit');

    Route::get('/otp-verification', [ProviderRegisterController::class, 'otpVerficationForm'])->name('otp-verfication');
    Route::post('/verify-otp', [ProviderRegisterController::class, 'verifyOtp'])->name('verify.otp');
    Route::post('/resend-otp', [ProviderRegisterController::class, 'resendOtp'])->name('resend.otp');

    Route::get('/reset-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/reset-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('reset-password.submit');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password/update', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {

    Route::get('/my-profile', [MyProfileController::class, 'myProfile'])->name('my-profile');
    Route::get('/edit-profile', [MyProfileController::class, 'stepTwo'])->name('edit-profile');
    Route::post('/edit-profile', [MyProfileController::class, 'save'])->name('edit-profile.save');
    Route::get('/delete-account', [AccountController::class, 'deleteAccountPage'])->name('account.delete-page');
    Route::delete('/delete-account', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::get('/add-photos', [PhotoController::class, 'index'])->name('add-photos');
    Route::get('/add-photo', [PhotoController::class, 'index'])->name('photos.index');
    Route::get('/photos', [PhotoController::class, 'getPhotos'])->name('photos');
    Route::post('/upload-photos', [PhotoController::class, 'uploadPhotos'])->name('photos.upload');
    Route::post('/photos/{photo}/set-cover', [PhotoController::class, 'setCover'])->name('photos.setCover');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::get('/change-password', [ProviderRegisterController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [ProviderRegisterController::class, 'updatePassword'])->name('change-password.update');

    Route::middleware(['profile.steps'])->group(function () {

        Route::get('/upload-video', [MyVideosController::class, 'index'])->name('upload-video');
        Route::get('/my-videos', [MyVideosController::class, 'getVideos'])->name('my-videos');
        Route::post('/videos/upload', [MyVideosController::class, 'uploadVideos'])->name('videos.upload');
        Route::delete('/videos/{video}', [MyVideosController::class, 'destroy'])->name('videos.destroy');
        Route::get('/my-tours', [MyToursController::class, 'index'])->name('my-tours');
        Route::post('/my-tours', [MyToursController::class, 'store'])->name('my-tours.store');
        Route::put('/my-tours/{tour}', [MyToursController::class, 'update'])->name('my-tours.update');
        Route::delete('/my-tours/{tour}', [MyToursController::class, 'destroy'])->name('my-tours.destroy');
        Route::get('/search-cities', [MyToursController::class, 'search'])->name('search-cities');
        Route::get('/set-availability', [AvailabilityController::class, 'edit'])->name('availability.edit');
        Route::post('/set-availability', [AvailabilityController::class, 'update'])->name('availability.update');
        Route::get('/my-availability', [AvailabilityController::class, 'show'])->name('availability.show');
        Route::get('/my-rate', [MyRateController::class, 'index'])->name('my-rate');
        Route::post('/my-rate', [MyRateController::class, 'store'])->name('my-rate.store');
        Route::delete('/my-rate/{rate}', [MyRateController::class, 'destroy'])->name('my-rate.destroy');
        Route::put('/my-rate/{rate}', [MyRateController::class, 'update'])->name('my-rate.update');
        Route::post('/groups', [MyRateController::class, 'storeGroup'])->name('my-rate.groups.store');
        Route::put('/groups/{group}', [MyRateController::class, 'updateGroup'])->name('my-rate.groups.update');
        Route::delete('/groups/{group}', [MyRateController::class, 'destroyGroup'])->name('my-rate.groups.destroy');
        Route::get('/click-here-to-verify', [PhotoVerificationController::class, 'index'])->name('verify.photos');
        Route::post('/verify-profile-photos/upload', [PhotoVerificationController::class, 'upload'])->name('photo-verification.upload');
        Route::post('/photo-verification/delete-photo', [PhotoVerificationController::class, 'deletePhoto'])->name('photo-verification.delete-photo');
        Route::post('/booking-enquiry', [BookingController::class, 'send'])->name('booking.enquiry');
        Route::get('/short-url', [ProviderRegisterController::class, 'shortUrl'])->name('short-url');
        Route::post('/short-url/update', [ProviderRegisterController::class, 'updateShortUrl'])->name('short-url-update');
        Route::get('/referrals', [ProviderRegisterController::class, 'referrals'])->name('referrals');
        Route::get('/online-now', [ProviderRegisterController::class, 'onlineNow'])->name('online-now');
        Route::post('/online-status', [ProviderRegisterController::class, 'onlineUpdateStatus'])->name('onlineUpdateStatus');
        Route::get('/available-now', [ProviderRegisterController::class, 'availableNow'])->name('available-now');
        Route::post('/available-status', [ProviderRegisterController::class, 'availableUpdateStatus'])->name('availableUpdateStatus');
        Route::get('/set-and-forget', [ProviderRegisterController::class, 'setForget'])->name('set-and-forget');
        Route::get('/my-babe-rank', [ProviderRegisterController::class, 'myBabeRank'])->name('my-babe-rank');
        Route::get('/profile-message', [ProviderRegisterController::class, 'profileMessage'])->name('profile-message');
        Route::post('/profile-message', [ProviderRegisterController::class, 'storeProfileMessage'])->name('storeProfileMessage');
        Route::get('/hide-show-profile', [ProviderRegisterController::class, 'hideShowProfile'])->name('hide-show-profile');
        Route::post('/hide-show-profile', [ProviderRegisterController::class, 'updateHideShowProfile'])->name('update-hide-show-profile');
        Route::get('/view-profile-setting', [ProviderRegisterController::class, 'viewProfileSetting'])->name('view-profile-setting');
        Route::get('/babe-rank', [ProviderRegisterController::class, 'babeRank'])->name('babe-rank');
        Route::get('/purchase-credit', [PurchaseCreditController::class, 'purchaseCredit'])->name('purchase-credit');
        Route::post('/purchase-credit/checkout', [PurchaseCreditController::class, 'checkout'])->name('purchase-credit.checkout');
        Route::get('/credit-history', [PurchaseCreditController::class, 'creditHistory'])->name('credit-history');
        Route::get('/credit-history-last-month', [PurchaseCreditController::class, 'creditHistoryLastMonth'])->name('credit-history-last-month');
        Route::get('/purchase-history', [PurchaseCreditController::class, 'purchaseHistory'])->name('purchase-history');
        });
    });
/** social auth routes */
// Route::get('/login', [SocialAuthController::class, 'showLogin'])->name('login');
// Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
// Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

require __DIR__ . '/escort-review.php';
