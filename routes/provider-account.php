<?php

use App\Http\Controllers\Frontend\PaymentSubscriptionController;
use App\Http\Controllers\Frontend\ProviderRegisterController;
use App\Http\Controllers\Subscription\PurchaseCreditController;
use App\Http\Controllers\Profile\AccountController;
use Illuminate\Support\Facades\Route;

// Account management — no active-profile session required
Route::get('/delete-account', [AccountController::class, 'deleteAccountPage'])->name('account.delete-page');
Route::delete('/delete-account', [AccountController::class, 'destroy'])->name('account.destroy');
Route::middleware(['profile.steps'])->group(function () {
    Route::get('/change-password', [ProviderRegisterController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [ProviderRegisterController::class, 'updatePassword'])->name('change-password.update');
    Route::get('/change-email', [ProviderRegisterController::class, 'changeEmail'])->name('change-email');
    Route::post('/change-email', [ProviderRegisterController::class, 'updateEmail'])->name('change-email.update');
});

// Balance / credit routes — profile scoped
Route::middleware('profile.selected')->group(function () {
    Route::get('/purchase-credit', [PurchaseCreditController::class, 'purchaseCredit'])->name('purchase-credit');
    Route::post('/purchase-credit/checkout', [PurchaseCreditController::class, 'checkout'])->name('purchase-credit.checkout');
    Route::post('/purchase-credit/create-intent', [PurchaseCreditController::class, 'createPaymentIntent'])->name('purchase-credit.create-intent');
    Route::get('/purchase-credit/success', [PurchaseCreditController::class, 'checkoutSuccess'])->name('purchase-credit.success');
    Route::get('/credit-history', [PurchaseCreditController::class, 'creditHistory'])->name('credit-history');
    Route::get('/credit-history-last-month', [PurchaseCreditController::class, 'creditHistoryLastMonth'])->name('credit-history-last-month');
    Route::get('/purchase-history', [PurchaseCreditController::class, 'purchaseHistory'])->name('purchase-history');
    Route::post('/purchase-history/{purchaseTransaction}/complaint', [PurchaseCreditController::class, 'storeComplaint'])->name('purchase-history.complaint');
});
Route::get('/payment-subscription', [PaymentSubscriptionController::class, 'index'])->name('payment-subscription');
