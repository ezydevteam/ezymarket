<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

use App\Http\Controllers\Api\{
    AccountController,
    ProductController,
    PurchaseController
};

Route::prefix('api')
    ->name('api.')
    ->middleware('api.disable')
    ->group(function () {

        // Account Routes
        Route::prefix('account')
            ->name('account.')
            ->controller(AccountController::class)
            ->group(function () {
                Route::get('details', 'details')->name('details');
            });

        // Product Routes
        Route::prefix('products')
            ->name('products.')
            ->controller(ProductController::class)
            ->group(function () {
                Route::get('all', 'all')->name('all');
                Route::get('product', 'product')->name('product');
            });

        // Purchase Routes
        Route::prefix('purchases')
            ->name('purchases.')
            ->controller(PurchaseController::class)
            ->group(function () {
                Route::post('validation', 'validation')->name('validation');
            });
    });




















