<?php

use App\Http\Controllers\Profile\AvailableController;
use App\Http\Controllers\Profile\BabeRankController;
use App\Http\Controllers\Profile\FeaturedController;
use App\Http\Controllers\Profile\ForgetController;
use App\Http\Controllers\Profile\MyRateController;
use App\Http\Controllers\Profile\MyToursController;
use App\Http\Controllers\Profile\MyVideosController;
use App\Http\Controllers\Profile\OnlineController;
use App\Http\Controllers\Profile\PhotoController;
use App\Http\Controllers\Profile\PhotoVerificationController;
use App\Http\Controllers\Profile\ProfileMessageController;
use App\Http\Controllers\Profile\ReferralsController;
use App\Http\Controllers\Profile\ShowHideProfileController;
use App\Http\Controllers\Profile\StatusTabsController;
use App\Http\Controllers\Profile\UrlController;
use App\Http\Controllers\Profile\AvailabilityController;
use App\Http\Controllers\Profile\MyProfileController;
use App\Http\Controllers\Profile\ProfileSettingController;
use App\Http\Controllers\Profile\ProfileSwitchController;
use Illuminate\Support\Facades\Route;

// Profile picker — no active-profile session required
Route::get('/select-profile', [ProfileSwitchController::class, 'selectProfile'])->name('select-profile');

// Profile management — no active-profile session required
Route::get('/my-profiles', [ProfileSwitchController::class, 'index'])->name('profiles.index');
Route::post('/my-profiles', [ProfileSwitchController::class, 'store'])->name('profiles.store');
Route::delete('/my-profiles/delete-selected', [ProfileSwitchController::class, 'destroySelected'])->name('profiles.destroy-selected');
Route::match(['get', 'post'], '/my-profiles/{profile}/switch', [ProfileSwitchController::class, 'switchTo'])->name('profiles.switch');
Route::post('/my-profiles/{profile}/switch-edit', [ProfileSwitchController::class, 'switchToEdit'])->name('profiles.switch-edit');
Route::post('/my-profiles/{profile}/online-status', [ProfileSwitchController::class, 'updateOnlineStatus'])->name('profiles.online-status');
Route::delete('/my-profiles/{profile}', [ProfileSwitchController::class, 'destroy'])->name('profiles.destroy');

// All routes below require an active profile to be selected in session
Route::middleware('profile.selected')->group(function () {
    Route::get('/my-profile', [MyProfileController::class, 'myProfile'])->name('my-profile');
    Route::get('/activity-logs', [MyProfileController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/profile-spending-history', [MyProfileController::class, 'spendingHistory'])->name('profile-spending-history');
    Route::get('/edit-profile', [MyProfileController::class, 'editProfile'])->name('edit-profile');
    Route::post('/edit-profile', [MyProfileController::class, 'save'])->name('edit-profile.save');
    Route::get('/add-photos', [PhotoController::class, 'index'])->name('add-photos');
    Route::get('/add-photo', [PhotoController::class, 'index'])->name('photos.index');
    Route::get('/photos', [PhotoController::class, 'getPhotos'])->name('photos');
    Route::post('/upload-photos', [PhotoController::class, 'uploadPhotos'])->name('photos.upload');
    Route::post('/editor/upload-image', [PhotoController::class, 'uploadEditorImage'])->name('editor.upload-image');
    Route::post('/photos/{photo}/set-cover', [PhotoController::class, 'setCover'])->name('photos.setCover');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

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
        Route::patch('/my-tours/{tour}/toggle', [MyToursController::class, 'toggleEnabled'])->name('my-tours.toggle');
        Route::delete('/my-tours/{tour}', [MyToursController::class, 'destroy'])->name('my-tours.destroy');
        Route::get('/search-cities', [MyToursController::class, 'search'])->name('search-cities');
        /*** tour route end here */

        /*** photo route start here */
        Route::get('/verify-photo', [PhotoVerificationController::class, 'index'])->name('verify.photos');
        Route::post('/verify-profile-photos/upload', [PhotoVerificationController::class, 'upload'])->name('photo-verification.upload');
        Route::post('/photo-verification/delete-photo', [PhotoVerificationController::class, 'deletePhoto'])->name('photo-verification.delete-photo');
        /****** photo route end here */

        /********** short url start */
        Route::get('/short-url', [UrlController::class, 'shortUrl'])->name('short-url');
        Route::post('/short-url/update', [UrlController::class, 'updateShortUrl'])->name('short-url.update');
        /********* short url end */

        /***** referral route start here */
        Route::get('/referral', [ReferralsController::class, 'referral'])->name('referral');
        /***** referral route end here */

        /***** Online route start here */
        Route::get('/online-now', [OnlineController::class, 'onlineNow'])->name('online-now');
        Route::post('/online-status', [OnlineController::class, 'updateStatus'])->name('online.update-status');
        /****** Online route end here */

        /***** Online available start here */
        Route::get('/available-now', [AvailableController::class, 'availableNow'])->name('available-now');
        Route::post('/available-status', [AvailableController::class, 'updateStatus'])->name('available.update-status');
        /***** Online available end here */

        /*** forget end here */
        Route::get('/set-and-forget', [ForgetController::class, 'setForget'])->name('set-and-forget');
        Route::post('/set-and-forget', [ForgetController::class, 'save'])->name('set-and-forget.save');
        /*** forget end here */

        /**** featured listing route start here */
        // Route name kept as 'featured' for backward compatibility with existing blade/test references.
        Route::get('/featured-listing', [FeaturedController::class, 'featured'])->name('featured');
        Route::post('/featured-listing/purchase', [FeaturedController::class, 'purchase'])->name('featured.purchase');
        /**** featured listing route end here */

        /**** babe rank start here */
        Route::get('/my-babe-rank', [BabeRankController::class, 'myBabeRank'])->name('my-babe-rank');
        Route::get('/babe-rank-read-more', [BabeRankController::class, 'babeRank'])->name('babe-rank-read-more');
        /**** babe rank end here */

        /**** profile message route start here */
        Route::get('/profile-message', [ProfileMessageController::class, 'profileMessage'])->name('profile-message');
        Route::post('/profile-message', [ProfileMessageController::class, 'store'])->name('profile-message.store');
        /**** profile message route end here */

        /**** hide show profile start here */
        Route::get('/hide-show-profile', [ShowHideProfileController::class, 'hideShowProfile'])->name('hide-show-profile');
        Route::post('/hide-show-profile', [ShowHideProfileController::class, 'updateHideShowProfile'])->name('update-hide-show-profile');
        /*** hide show profile end here */

        /*** status tabs route */
        Route::get('/status', [StatusTabsController::class, 'show'])->name('status');
        /*** status tabs route end */

        /********** profile route start here */
        Route::get('/profile-setting', [ProfileSettingController::class, 'viewProfileSetting'])->name('profile-setting');
        /********** profile route end here */
    });
});
