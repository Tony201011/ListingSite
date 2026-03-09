<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EscortReviewController;

// Public page
Route::get('/escort-reviews', [EscortReviewController::class, 'show'])->name('escort-review');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/escort-review/edit', [EscortReviewController::class, 'edit'])->name('escort-review.edit');
    Route::post('/escort-review/update', [EscortReviewController::class, 'update'])->name('escort-review.update');
});
