<?php

/**frontend controller start*** */
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProviderRegisterController;
/**frontend controller end*** */

/*******auth Controllers start */
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\FavouriteBookmarkController;
use App\Http\Controllers\Frontend\FrontendPageController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ReportUserController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\MediaController;
/*******auth Controllers end */

/***profile Controllers start*/
use App\Http\Controllers\Profile\AccountController;
use App\Http\Controllers\Profile\AvailabilityController;
use App\Http\Controllers\Profile\AvailableController;
use App\Http\Controllers\Profile\BabeRankController;
use App\Http\Controllers\Profile\BookingController;
use App\Http\Controllers\Profile\FeaturedController;
use App\Http\Controllers\Profile\ForgetController;
use App\Http\Controllers\Profile\MyProfileController;
use App\Http\Controllers\Profile\MyRateController;
use App\Http\Controllers\Profile\MyToursController;
use App\Http\Controllers\Profile\MyVideosController;
use App\Http\Controllers\Profile\OnlineController;
use App\Http\Controllers\Profile\PhotoController;
use App\Http\Controllers\Profile\PhotoVerificationController;
use App\Http\Controllers\Profile\ProfileMessageController;
use App\Http\Controllers\Profile\ProfileSettingController;
use App\Http\Controllers\Profile\ProfileSwitchController;
use App\Http\Controllers\Profile\ReferralsController;
use App\Http\Controllers\Profile\ShowHideProfileController;
use App\Http\Controllers\Profile\StatusTabsController;
use App\Http\Controllers\Profile\SuburbController;
use App\Http\Controllers\Profile\UrlController;
use App\Http\Controllers\SiteAccess\SitePasswordController;
/***profile Controllers start*/

/**subscription controller start */
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Subscription\MemberShipController;
use App\Http\Controllers\Subscription\PaymentSubscriptionController;
/**subscription controller end */

/*******site access controller start here  */
use App\Http\Controllers\Subscription\PurchaseCreditController;
/*******site access controller end here  */

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

// Stripe webhook — must be outside all auth/session middleware and CSRF-exempt
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/site-password', [SitePasswordController::class, 'showForm'])
    ->name('site-password.form');

Route::post('/site-password', [SitePasswordController::class, 'submit'])
    ->name('site-password.submit');

Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
    ->middleware('auth:web')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/delete-account/confirm/{id}/{hash}', [AccountController::class, 'confirmDestroy'])
    ->middleware(['signed'])
    ->name('account.confirm-destroy');

Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth:web', 'throttle:'.config('security.throttles.verification_send', '6,1')])
    ->name('verification.send');

Route::redirect('/login', '/signin');

/** frontend pages */
Route::get('/terms-and-conditions', [FrontendPageController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('/privacy-policy', [FrontendPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/refund-policy', [FrontendPageController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/faq', [FrontendPageController::class, 'faq'])->name('faq');
Route::get('/faq/load-more', [FrontendPageController::class, 'faqLoadMore'])->name('faq.load-more');
Route::get('/anti-spam-policy', [FrontendPageController::class, 'antiSpamPolicy'])->name('anti-spam-policy');
Route::get('/content-moderation-policy', [FrontendPageController::class, 'contentModerationPolicy'])->name('content-moderation-policy');
Route::get('/report-a-listing', [FrontendPageController::class, 'reportAListing'])->name('report-a-listing');
Route::get('/age-and-consent-policy', [FrontendPageController::class, 'ageAndConsentPolicy'])->name('age-and-consent-policy');
Route::get('/prohibited-content-policy', [FrontendPageController::class, 'prohibitedContentPolicy'])->name('prohibited-content-policy');

Route::get('api/suburbs/search', [SuburbController::class, 'search'])->name('api.suburbs.search');
Route::get('api/search/suggestions', [SearchController::class, 'suggestions'])->name('api.search.suggestions');
Route::get('api/listings/online-count', [HomeController::class, 'listingsOnlineCount'])->name('api.listings.online-count');

Route::get('/contact-us', [FrontendPageController::class, 'contactUs'])->name('contact-us');
Route::post('/contact-us', [FrontendPageController::class, 'submitContactUs'])->name('contact-us.submit');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/girls/{type}/page/{page}', [HomeController::class, 'index'])
    ->whereIn('type', ['all', 'new', 'popular'])
    ->whereNumber('page')
    ->name('girls.page');
Route::get('/girls/{type}', [HomeController::class, 'index'])
    ->whereIn('type', ['all', 'new', 'popular'])
    ->name('girls.index');
Route::get('/search/page/{page}', [HomeController::class, 'advancedSearch'])
    ->whereNumber('page')
    ->name('advanced-search.page');
Route::get('/search/{location_slug}/page/{page}', [HomeController::class, 'advancedSearch'])
    ->where('location_slug', '(?!page$)[a-z0-9-]+')
    ->whereNumber('page')
    ->name('search.location.page');
Route::get('/search/{location_slug}', [HomeController::class, 'advancedSearch'])
    ->where('location_slug', '(?!page$)[a-z0-9-]+')
    ->name('search.location');
Route::get('/search', [HomeController::class, 'advancedSearch'])->name('advanced-search');
Route::get('/escorts/search', [HomeController::class, 'index'])->name('escorts.search');
Route::get('/escorts/search/page/{page}', [HomeController::class, 'index'])
    ->whereNumber('page')
    ->name('escorts.search.page');
Route::get('/escorts/search/name/{search_name}', [HomeController::class, 'index'])
    ->name('escorts.search.name');
Route::get('/escorts/search/name/{search_name}/page/{page}', [HomeController::class, 'index'])
    ->whereNumber('page')
    ->name('escorts.search.name.page');
Route::get('/escorts/search/{location_slug}', [HomeController::class, 'index'])
    ->where(['location_slug' => '(?!name$|location$|page$)[a-z0-9-]+'])
    ->name('escorts.search.slug');
Route::get('/escorts/search/{location_slug}/page/{page}', [HomeController::class, 'index'])
    ->where(['location_slug' => '(?!name$|location$|page$)[a-z0-9-]+'])
    ->whereNumber('page')
    ->name('escorts.search.slug.page');
Route::get('/escorts/search/location/{legacy_location_slug}', [HomeController::class, 'index'])
    ->where(['legacy_location_slug' => '[a-z0-9-]+'])
    ->name('escorts.search.location.legacy');
Route::get('/escorts/search/location/{suburb}/{state}', [HomeController::class, 'index'])
    ->where(['suburb' => '[a-z0-9-]+', 'state' => '[a-z0-9-]+'])
    ->name('escorts.search.location');
Route::get('/escorts/search/location/{suburb}', [HomeController::class, 'index'])
    ->where(['suburb' => '[a-z0-9-]+'])
    ->name('escorts.search.location.no-state');
Route::get('/escorts/location/{location}', [HomeController::class, 'index'])
    ->name('escorts.location');
Route::get('/featured', [HomeController::class, 'featuredListings'])->name('featured.escorts');
Route::get('/advanced-search', [HomeController::class, 'advancedSearch'])->name('advanced-search.legacy');
Route::get('/favourites', [HomeController::class, 'favourites'])->name('favourites');
Route::get('/escorts/{state}/{suburb}/{slug}/{sequence_id}', [HomeController::class, 'showProfile'])
    ->where([
        'state' => '[a-z]{2,3}',
        'suburb' => '[a-z0-9-]+',
        'slug' => '[a-z0-9-]+',
        'sequence_id' => '[0-9]{3}',
    ])
    ->name('profile.show');
Route::get('/escorts/{state}/{suburb}/{slug}', [HomeController::class, 'showProfile'])
    ->where([
        'state' => '[a-z]{2,3}',
        'suburb' => '[a-z0-9-]+',
        'slug' => '[a-z0-9-]+',
    ])
    ->name('profile.show.no-sequence');

// Legacy redirect: old /profile/{slug} URLs → new SEO URL (301)
Route::get('/profile/{slug}', [HomeController::class, 'redirectOldProfile'])->name('profile.show.legacy');

Route::post('/favourite/{slug}', [FavouriteBookmarkController::class, 'toggleFavourite'])->name('favourite.toggle');

Route::post('/report-profile', [ReportUserController::class, 'store'])
    ->middleware('throttle:'.config('security.throttles.booking_enquiry', '5,1'))
    ->name('profile.report');

Route::post('/booking-enquiry', [BookingController::class, 'send'])
    ->middleware('throttle:'.config('security.throttles.booking_enquiry', '5,1'))
    ->name('booking.enquiry');

Route::get('/about-us', [FrontendPageController::class, 'aboutUs'])->name('about-us');
Route::get('/help', [FrontendPageController::class, 'help'])->name('help');
Route::get('/naughty-corner', [FrontendPageController::class, 'naughtyCorner'])->name('naughty-corner');
Route::get('/membership', [MemberShipController::class, 'membership'])->name('membership');

Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/load-more', [BlogController::class, 'loadMore'])->name('blog.load-more');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/pricing', [FrontendPageController::class, 'pricing'])->name('pricing');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemaps/static.xml', [SitemapController::class, 'static'])->name('sitemap.static');
Route::get('/sitemaps/profiles-{page}.xml', [SitemapController::class, 'profiles'])
    ->whereNumber('page')
    ->name('sitemap.profiles');

Route::get('/403', function () {
    abort(403);
});
/** frontend pages end */

/** auth routes start */
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');
/** auth routes end */
Route::middleware('guest')->group(function (): void {
    Route::get('/signup', [ProviderRegisterController::class, 'showSignupForm'])->middleware('throttle:'.config('security.throttles.signup_page', '5,1'))->name('signup');
    Route::post('/signup', [ProviderRegisterController::class, 'signup'])->name('signup.submit');

    Route::get('/signin', [ProviderRegisterController::class, 'showSigninForm'])->name('signin');
    Route::post('/signin', [ProviderRegisterController::class, 'signin'])->name('signin.submit');

    Route::get('/otp-verification', [ProviderRegisterController::class, 'otpVerificationForm'])->name('otp-verification');
    Route::post('/verify-otp', [ProviderRegisterController::class, 'verifyOtp'])->middleware('throttle:'.config('security.throttles.verify_otp', '5,1'))->name('verify.otp');
    Route::post('/resend-otp', [ProviderRegisterController::class, 'resendOtp'])->middleware('throttle:'.config('security.throttles.resend_otp', '3,1'))->name('resend.otp');

    Route::get('/reset-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/reset-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:'.config('security.throttles.password_reset_request', '5,1'))
        ->name('reset-password.submit');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password/update', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('provider.auth')->group(function () {
    require __DIR__.'/provider-account.php';
    require __DIR__.'/provider-profile.php';
});

require __DIR__.'/escort-review.php';

// Short URL redirect — must be last to avoid shadowing other routes
Route::get('/{short_url}', [UrlController::class, 'redirectShortUrl'])
    ->name('short-url.redirect')
    ->where('short_url', '[a-zA-Z0-9_-]+');
