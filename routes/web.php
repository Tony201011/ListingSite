<?php

/**frontend controller start*** */
use App\Http\Controllers\Frontend\FrontendPageController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\HomeController;
/**frontend controller end*** */

/*******auth Controllers start */
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProviderRegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\AuthController;
/*******auth Controllers end */

/***profile Controllers start*/
use App\Http\Controllers\Profile\AccountController;
use App\Http\Controllers\Profile\MyProfileController;
use App\Http\Controllers\Profile\MyRateController;
use App\Http\Controllers\Profile\MyToursController;
use App\Http\Controllers\Profile\MyVideosController;
use App\Http\Controllers\Profile\OnlineController;
use App\Http\Controllers\Profile\PhotoController;
use App\Http\Controllers\Profile\PhotoVerificationController;
use App\Http\Controllers\Profile\AvailabilityController;
use App\Http\Controllers\Profile\BookingController;
use App\Http\Controllers\Profile\ShowHideProfileController;
use App\Http\Controllers\Profile\ReferralsController;
use App\Http\Controllers\Profile\AvailableController;
use App\Http\Controllers\Profile\ProfileMessageController;
use App\Http\Controllers\Profile\BabeRankController;
use App\Http\Controllers\Profile\ForgetController;
use App\Http\Controllers\Profile\ProfileSettingController;
use App\Http\Controllers\Profile\SuburbController;
use App\Http\Controllers\Profile\UrlController;
/***profile Controllers start*/

/**subscription controller start */
use App\Http\Controllers\Subscription\PurchaseCreditController;
use App\Http\Controllers\Subscription\MemberShipController;
use App\Http\Controllers\Subscription\PaymentSubscriptionController;
/**subscription controller end */

/*******site access controller start here  */
use App\Http\Controllers\SiteAccess\SitePasswordController;
/*******site access controller end here  */

use Illuminate\Support\Facades\Route;

Route::get('/site-password', [SitePasswordController::class, 'showForm'])
    ->name('site-password.form');

Route::post('/site-password', [SitePasswordController::class, 'submit'])
    ->name('site-password.submit');

Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');


Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

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
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');
/** auth routes end */

Route::middleware('guest')->group(function (): void {
    Route::get('/signup', [ProviderRegisterController::class, 'showSignupForm'])->middleware('throttle:5,1')->name('signup');
    Route::post('/signup', [ProviderRegisterController::class, 'signup'])->name('signup.submit');

    Route::get('/signin', [ProviderRegisterController::class, 'showSigninForm'])->name('signin');
    Route::post('/signin', [ProviderRegisterController::class, 'signin'])->name('signin.submit');

    Route::get('/otp-verification', [ProviderRegisterController::class, 'otpVerficationForm'])->name('otp-verfication');
    Route::post('/verify-otp', [ProviderRegisterController::class, 'verifyOtp'])->middleware('throttle:5,1')->name('verify.otp');
    Route::post('/resend-otp', [ProviderRegisterController::class, 'resendOtp'])->middleware('throttle:3,1')->name('resend.otp');

    Route::get('/reset-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/reset-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('reset-password.submit');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password/update', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/my-profile', [MyProfileController::class, 'myProfile'])->name('my-profile');
    Route::get('/edit-profile', [MyProfileController::class, 'editProfie'])->name('edit-profile');
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
        /**** video route start here */
        Route::get('/upload-video', [MyVideosController::class, 'index'])->name('upload-video');
        Route::get('/my-videos', [MyVideosController::class, 'getVideos'])->name('my-videos');
        Route::post('/videos/upload', [MyVideosController::class, 'uploadVideos'])->name('videos.upload');
        Route::delete('/videos/{video}', [MyVideosController::class, 'destroy'])->name('videos.destroy');
        /**** video route end here */

        /*** tour route start here */
        Route::get('/my-tours', [MyToursController::class, 'index'])->name('my-tours');
        Route::post('/my-tours', [MyToursController::class, 'store'])->name('my-tours.store');
        Route::put('/my-tours/{tour}', [MyToursController::class, 'update'])->name('my-tours.update');
        Route::delete('/my-tours/{tour}', [MyToursController::class, 'destroy'])->name('my-tours.destroy');
        Route::get('/search-cities', [MyToursController::class, 'search'])->name('search-cities');
        /*** tour route end here */


        /*** availability route start here */
        Route::get('/set-availability', [AvailabilityController::class, 'edit'])->name('availability.edit');
        Route::post('/set-availability', [AvailabilityController::class, 'update'])->name('availability.update');
        Route::get('/my-availability', [AvailabilityController::class, 'show'])->name('availability.show');
        /*** availability route end here */

        /************* rate and group route start here */
        Route::get('/my-rate', [MyRateController::class, 'index'])->name('my-rate');
        Route::post('/my-rate', [MyRateController::class, 'store'])->name('my-rate.store');
        Route::delete('/my-rate/{rate}', [MyRateController::class, 'destroy'])->name('my-rate.destroy');
        Route::put('/my-rate/{rate}', [MyRateController::class, 'update'])->name('my-rate.update');
        Route::post('/groups', [MyRateController::class, 'storeGroup'])->name('my-rate.groups.store');
        Route::put('/groups/{group}', [MyRateController::class, 'updateGroup'])->name('my-rate.groups.update');
        Route::delete('/groups/{group}', [MyRateController::class, 'destroyGroup'])->name('my-rate.groups.destroy');
        /************* rate and group route end here */

        /*** photo route start here */
        Route::get('/verify-photo', [PhotoVerificationController::class, 'index'])->name('verify.photos');
        Route::post('/verify-profile-photos/upload', [PhotoVerificationController::class, 'upload'])->name('photo-verification.upload');
        Route::post('/photo-verification/delete-photo', [PhotoVerificationController::class, 'deletePhoto'])->name('photo-verification.delete-photo');
        /****** photo route end here */

        Route::post('/booking-enquiry', [BookingController::class, 'send'])->name('booking.enquiry');

        /********** short url start */
        Route::get('/short-url', [UrlController::class, 'shortUrl'])->name('short-url');
        Route::post('/short-url/update', [UrlController::class, 'updateShortUrl'])->name('short-url-update');
        /********* short url end */

        /***** referral route start here */
        Route::get('/referral', [ReferralsController::class, 'referral'])->name('referral');
        /***** referral route end here */

        /***** Online route start here */
        Route::get('/online-now', [OnlineController::class, 'onlineNow'])->name('online-now');
        Route::post('/online-status', [OnlineController::class, 'onlineUpdateStatus'])->name('onlineUpdateStatus');
        /****** Online route end here */

        /***** Online available start here */
        Route::get('/available-now', [AvailableController::class, 'availableNow'])->name('available-now');
        Route::post('/available-status', [AvailableController::class, 'availableUpdateStatus'])->name('availableUpdateStatus');
        /***** Online available end here */

        /*** forget end here */
        Route::get('/set-and-forget', [ForgetController::class, 'setForget'])->name('set-and-forget');
        /*** forget end here */

        /**** babe rank start here */
        Route::get('/my-babe-rank', [BabeRankController::class, 'myBabeRank'])->name('my-babe-rank');
        Route::get('/babe-rank', [BabeRankController::class, 'babeRank'])->name('babe-rank');
        /**** babe rank end here */

        /**** profile message route start here */
        Route::get('/profile-message', [ProfileMessageController::class, 'profileMessage'])->name('profile-message');
        Route::post('/profile-message', [ProfileMessageController::class, 'storeProfileMessage'])->name('storeProfileMessage');
        /**** profile message route end here */

        /**** hide show profile start here */
        Route::get('/hide-show-profile', [ShowHideProfileController::class, 'hideShowProfile'])->name('hide-show-profile');
        Route::post('/hide-show-profile', [ShowHideProfileController::class, 'updateHideShowProfile'])->name('update-hide-show-profile');
        /*** hide show profile end here */

        /********** profile route start here */
        Route::get('/profile-setting', [ProfileSettingController::class, 'viewProfileSetting'])->name('profile-setting');
        /********** profile route end here */

        /*** credit route start here */
        Route::get('/purchase-credit', [PurchaseCreditController::class, 'purchaseCredit'])->name('purchase-credit');
        Route::post('/purchase-credit/checkout', [PurchaseCreditController::class, 'checkout'])->name('purchase-credit.checkout');
        Route::get('/credit-history', [PurchaseCreditController::class, 'creditHistory'])->name('credit-history');
        Route::get('/credit-history-last-month', [PurchaseCreditController::class, 'creditHistoryLastMonth'])->name('credit-history-last-month');
        Route::get('/purchase-history', [PurchaseCreditController::class, 'purchaseHistory'])->name('purchase-history');
        Route::get('/membership', [MemberShipController::class, 'membership'])->name('membership');
        Route::get('/payment-subscription', [PaymentSubscriptionController::class, 'index'])->name('payment-subscription');
        /*** credit route end here */
    });
});


require __DIR__ . '/escort-review.php';
