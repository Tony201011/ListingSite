<?php

use App\Http\Controllers\Subscription\PaymentSubscriptionController;
use App\Http\Controllers\Auth\ProviderRegisterController;
use App\Http\Controllers\Subscription\PurchaseCreditController;
use App\Http\Controllers\Profile\AccountController;
use Illuminate\Support\Facades\Route;

// Account management — no active-profile session required
Route::get('/my-account', [AccountController::class, 'myAccount'])->name('my-account');
Route::put('/my-account', [AccountController::class, 'updateAccount'])->name('my-account.update');
Route::get('/delete-account', [AccountController::class, 'deleteAccountPage'])->name('account.delete-page');
Route::delete('/delete-account', [AccountController::class, 'destroy'])->name('account.destroy');
Route::get('/account-restore-requests', [AccountController::class, 'accountRestoreRequests'])->name('account.restore-requests');
Route::middleware(['profile.steps'])->group(function () {
    Route::get('/change-password', [ProviderRegisterController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [ProviderRegisterController::class, 'updatePassword'])->name('change-password.update');
    Route::get('/change-email', [ProviderRegisterController::class, 'changeEmail'])->name('change-email');
    Route::post('/change-email', [ProviderRegisterController::class, 'updateEmail'])->name('change-email.update');
});

// Balance / credit routes — profile scoped
Route::middleware('profile.selected')->group(function () {
    Route::post('/purchase-credit/checkout', [PurchaseCreditController::class, 'checkout'])->name('purchase-credit.checkout');
    Route::post('/purchase-credit/woo-checkout', [PurchaseCreditController::class, 'wooCheckout'])->name('purchase-credit.woo-checkout');
    Route::post('/purchase-credit/create-intent', [PurchaseCreditController::class, 'createPaymentIntent'])->name('purchase-credit.create-intent');
    Route::get('/credit-history', [PurchaseCreditController::class, 'creditHistory'])->name('credit-history');
    Route::get('/credit-history-last-month', [PurchaseCreditController::class, 'creditHistoryLastMonth'])->name('credit-history-last-month');
    Route::get('/purchase-history', [PurchaseCreditController::class, 'purchaseHistory'])->name('purchase-history');
    Route::post('/purchase-history/{purchaseTransaction}/complaint', [PurchaseCreditController::class, 'storeComplaint'])->name('purchase-history.complaint');
});
Route::get('/purchase-credit/success', [PurchaseCreditController::class, 'checkoutSuccess'])->name('purchase-credit.success');
Route::get('/payment-subscription', [PaymentSubscriptionController::class, 'index'])->name('payment-subscription');
