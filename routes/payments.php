<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payments\{
    BankwireController,
    PaypalController,
    PaypalIpnController,
    StripeController,
    CoinbaseController,
    FlutterwaveController,
    RazorpayController,
    NowpaymentsController,
    UddoktapayController,
};

Route::prefix('payments')->name('payments.')->group(function () {

    // Manual Payment Routes
    Route::prefix('manual')->name('manual.')->group(function () {
        Route::post('bankwire', [BankwireController::class, 'submit'])->name('bankwire');
    });

    // IPN (Instant Payment Notification) Routes
    Route::prefix('ipn')->name('ipn.')->group(function () {
        Route::get('paypal', [PaypalController::class, 'ipn'])->name('paypal');
        Route::get('paypal-ipn', [PaypalIpnController::class, 'ipn'])->name('paypal-ipn');
        Route::get('stripe', [StripeController::class, 'ipn'])->name('stripe');
        Route::get('coinbase', [CoinbaseController::class, 'ipn'])->name('coinbase');
        Route::get('flutterwave', [FlutterwaveController::class, 'ipn'])->name('flutterwave');
        Route::post('razorpay', [RazorpayController::class, 'ipn'])->name('razorpay');
        Route::get('nowpayments', [NowpaymentsController::class, 'ipn'])->name('nowpayments');
        Route::get('uddoktapay', [UddoktapayController::class, 'ipn'])->name('uddoktapay');
    });

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::post('paypal-ipn', [PaypalIpnController::class, 'notification'])->name('paypal-ipn');
    });

    // Webhook Routes
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::post('paypal', [PaypalController::class, 'webhook'])->name('paypal');
        Route::post('stripe', [StripeController::class, 'webhook'])->name('stripe');
        Route::post('coinbase', [CoinbaseController::class, 'webhook'])->name('coinbase');
        Route::post('flutterwave', [FlutterwaveController::class, 'webhook'])->name('flutterwave');
        Route::post('razorpay', [RazorpayController::class, 'webhook'])->name('razorpay');
        Route::post('nowpayments', [NowpaymentsController::class, 'webhook'])->name('nowpayments');
        Route::post('uddoktapay', [UddoktapayController::class, 'webhook'])->name('uddoktapay');
    });
});
