<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    GeneralController,
    UtilityController
};
use App\Http\Controllers\Theme\{
    BlogController,
    CartController,
    CategoryController,
    ChatboxController,
    CheckoutController,
    FeedbackController,
    HelpController,
    HomeController,
    LanguageController,
    PremiumController,
    NotificationController,
    ProductController,
    ProductReportController,
    ProfileController
};
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,
    AblyAuthController,
    SocialAuthController,
    ForgotPasswordController,
    VerificationController,
    TwoFactorController
};
use App\Http\Controllers\UserPanel\{
    DashboardController,
    SellerController,
    ProductController as UserPanelProductController,
    UploadController,
    PurchaseController,
    TransactionController,
    PayoutController,
    ReferralController,
    WalletController,
    RefundController,
    TicketController,
    SettingsController
};
use App\Http\Controllers\UserPanel\Tools\LicenseVerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::post('image/upload', [UtilityController::class, 'uploadImage']);
Route::get('cronjob', [UtilityController::class, 'cronjob'])->name('cronjob')->middleware('demo:GET');

Route::middleware(['maintenance'])->group(function () {
    Auth::routes(['verify' => false, 'reset' => false]);

    Route::post('cookie/accept', [UtilityController::class, 'cookie'])->middleware('ajax.only');

    // Authentication Routes
    Route::controller(LoginController::class)->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login');
        Route::post('logout', 'logout')->name('logout');
    });

    Route::middleware(['registration.disable'])->group(function () {
        Route::controller(RegisterController::class)->group(function () {
            Route::get('register', 'showRegistrationForm')->name('register');
            Route::post('register', 'register')->middleware('trustip');
            Route::post('check-username-availability', 'checkUsernameAvailability')->name('check.username.availability');

            // Registration OTP Routes
            Route::get('email/verify/otp', 'showOtpForm')->name('verification.otp');
            Route::post('email/verify/otp', 'verifyOtp')->name('verification.otp.verify');
            Route::post('email/verify/otp/resend', 'resendOtp')->name('verification.otp.resend');
        });
    });

    Route::post('/ably/auth', [AblyAuthController::class, 'authenticate'])
        ->name('ably.auth')
        ->middleware('auth');

    // OAuth Routes
    Route::prefix('oauth')->name('oauth.')->group(function () {
        Route::middleware('demo:GET')->controller(SocialAuthController::class)->group(function () {
            Route::get('{provider}', 'redirectToProvider')->name('login')->middleware('trustip');
            Route::get('{provider}/callback', 'handleProviderCallback')->name('callback');
        });

        Route::middleware('auth')->controller(SocialAuthController::class)->group(function () {
            Route::get('data/complete', 'showCompleteForm');
            Route::post('data/complete', 'complete')->name('data.complete')->middleware('trustip');
        });
    });

    // Password Reset Routes
    Route::middleware('mail')->group(function () {
        Route::controller(ForgotPasswordController::class)->group(function () {
            Route::get('password/forgot', 'showForgotForm')->name('password.request');
            Route::post('password/email', 'sendOtp')->name('password.email');

            // Password Reset OTP Routes
            Route::get('password/verify', 'showOtpForm')->name('password.otp');
            Route::post('password/verify', 'verifyOtp')->name('password.otp.verify');
            Route::post('password/verify/resend', 'resendOtp')->name('password.otp.resend');
            Route::get('password/reset', 'showNewPasswordForm')->name('password.reset');
            Route::post('password/reset', 'resetPassword')->name('password.update');
        });
    });


    // Email Verification Routes
    Route::middleware('oauth.complete')->group(function () {
        Route::middleware('mail')->controller(VerificationController::class)->group(function () {
            Route::get('email/verify', 'show')->name('verification.notice');
            Route::post('email/verify/email/change', 'changeEmail')->name('change.email');

            // Email Change OTP Routes
            Route::get('email/verify/change/otp', 'showEmailChangeOtpForm')->name('verification.email.otp');
            Route::post('email/verify/change/otp', 'verifyEmailChangeOtp')->name('verification.email.otp.verify');
            Route::post('email/verify/change/otp/resend', 'resendEmailChangeOtp')->name('verification.email.otp.resend');
        });
    });

    // 2FA Verification Routes - Must be accessible to authenticated users without 2FA check
    Route::middleware(['auth', 'oauth.complete', 'verified'])->controller(TwoFactorController::class)->group(function () {
        Route::get('2fa/verify', 'show2FaVerifyForm')->name('2fa.verify');
        Route::post('2fa/verify', 'verify2fa')->name('2fa.verify.submit');
    });

    // Language Switcher
    Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
    Route::get('/products/live-search', [ProductController::class, 'liveSearch'])->name('product.live_search');

    // User Panel Routes
    Route::prefix('user')
        ->name('user.')
        ->middleware(['auth', 'oauth.complete', 'verified', '2fa.verify'])
        ->group(function () {

            Route::get('/', fn() => redirect()->route(authUser()->isSeller() ? 'user.dashboard' : 'user.purchase.index'))
                ->name('index');

            Route::get('dashboard', [DashboardController::class, 'index'])
                ->name('dashboard')
                ->middleware('seller');

            Route::get('search', [DashboardController::class, 'search'])
                ->name('search');

            // Export Routes
            Route::prefix('export')
                ->middleware('seller')
                ->controller(DashboardController::class)
                ->group(function () {
                    Route::get('dashboard/{format?}', 'exportDashboardReport')
                        ->name('export.dashboard')
                        ->where('format', 'pdf|excel');
                });

            Route::middleware('demo')->group(function () {
                // Become seller Routes
                Route::prefix('become-a-seller')
                    ->middleware(['seller.disable', 'not.seller'])
                    ->controller(SellerController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('become_seller');
                        Route::post('/', 'store')->name('become_seller.store');
                    });

                // Product Routes
                Route::prefix('products')
                    ->middleware('seller')
                    ->controller(UserPanelProductController::class)
                    ->name('product.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/drafts', 'drafts')->name('drafts');
                    });
                Route::prefix('product')
                    ->name('product.')
                    ->middleware('seller')
                    ->controller(UserPanelProductController::class)
                    ->group(function () {
                        Route::get('create', 'create')->name('create')->middleware(['id-verification.required', 'draft.owner']);
                        Route::post('store', 'store')->name('store')->middleware('draft.owner');
                        Route::post('save-draft', 'saveDraft')->name('save_draft')->middleware('draft.owner');
                        Route::get('category/{slug}/data', 'getCategoryData')->name('category.data');
                        Route::get('slug', 'slug')->name('slug');
                        Route::post('{id}/publish', 'publishDraft')->name('publish');
                        Route::delete('{id}/draft', 'deleteDraft')->name('draft.delete');
                        Route::get('{id}/edit', 'edit')->name('edit');
                        Route::post('{id}/update', 'update')->name('update');

                        // Changelogs
                        Route::prefix('{id}/changelogs')
                            ->name('changelogs.')
                            ->middleware('product_changelogs.disable')
                            ->group(function () {
                                Route::get('/', [UserPanelProductController::class, 'changelogs'])->name('index');
                                Route::post('store', [UserPanelProductController::class, 'changelogsStore'])->name('store');
                                Route::delete('delete/{changelog_id}', [UserPanelProductController::class, 'changelogsDelete'])->name('delete');
                            });

                        Route::get('{id}/history', 'history')->name('history');

                        Route::middleware('discount.disable')->group(function () {
                            Route::get('{id}/discount', 'discount')->name('discount');
                            Route::post('{id}/discount', 'discountCreate')->name('discount.create');
                            Route::delete('{id}/discount/delete', 'discountDelete')->name('discount.delete');
                        });

                        Route::get('{id}/statistics', 'statistics')->name('statistics');
                        Route::post('{id}/statistics/recalculate', 'recalculateStatistics')->name('statistics.recalculate');
                        Route::get('{id}/export-statistics/{format?}', 'exportStatistics')
                            ->name('export.statistics')
                            ->where('format', 'pdf|excel');
                        Route::post('files/{category_id}/load', 'loadFiles')->name('files.load');
                        Route::delete('files/{category_id}/delete/{id}', 'deleteFile')->name('files.delete');
                        Route::post('files/{category_id}/upload', [UploadController::class, 'upload'])->name('upload');
                        Route::post('{id}/download', 'download')->name('download');
                        Route::delete('{id}/delete', 'destroy')->name('destroy');
                    });

                // Purchase Routes
                Route::get('purchases', [PurchaseController::class, 'index'])
                    ->name('purchase.index');
                Route::prefix('purchase')
                    ->name('purchase.')
                    ->controller(PurchaseController::class)
                    ->group(function () {
                        Route::middleware('product_support.disable')->group(function () {
                            Route::post('{id}/support/purchase', 'purchaseSupport')->name('support.purchase');
                            Route::post('{id}/support/extend', 'extendSupport')->name('support.extend');
                        });

                        Route::get('{id}/license', 'showLicense')->name('license');
                        Route::get('{id}/download', 'downloadProduct')->name('download');

                        // AJAX Modals
                        Route::get('{id}/modal-code', 'modalCode')->name('modal.code');
                        Route::get('{id}/modal-support', 'modalSupport')->name('modal.support');
                    });

                // Transaction Routes
                Route::get('transactions', [TransactionController::class, 'index'])
                    ->name('transaction.index');
                Route::prefix('transaction')
                    ->name('transaction.')
                    ->controller(TransactionController::class)
                    ->group(function () {
                        Route::get('{id}', 'show')->name('show');
                        Route::get('{id}/invoice', 'invoice')->name('invoice');
                        Route::delete('{id}', 'destroy')->name('destroy');
                    });

                Route::middleware('seller')->group(function () {
                    Route::get('referrals', [ReferralController::class, 'index'])
                        ->name('referrals')
                        ->middleware('referral.disable');

                    // Payout Routes
                    Route::get('payouts', [PayoutController::class, 'index'])
                        ->name('payout.index');
                    Route::prefix('payout')
                        ->name('payout.')
                        ->controller(PayoutController::class)
                        ->group(function () {
                        Route::get('modal/payout', 'modalPayout')->name('modal.payout');
                        Route::post('store', 'store')->name('store')->middleware('id-verification.required');
                        Route::get('{id}', 'show')->name('show');
                        Route::post('{id}/recall', 'recall')->name('recall');
                        Route::delete('{id}', 'destroy')->name('destroy');
                        });
                });

                // Wallet Routes
                Route::prefix('wallet')
                    ->name('wallet.')
                    ->controller(WalletController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('modal/deposit', 'modalDeposit')->name('modal.deposit');
                        Route::post('/', 'deposit')->name('deposit')
                            ->middleware(['deposit.disable', 'id-verification.required']);
                    });

                // Refund Routes
                Route::get('refunds', [RefundController::class, 'index'])
                    ->name('refund.index')
                    ->middleware('refunds.disable');
                Route::prefix('refund')
                    ->name('refund.')
                    ->middleware('refunds.disable')
                    ->controller(RefundController::class)
                    ->group(function () {
                        Route::post('store', 'store')->name('store');
                        Route::get('{id}', 'show')->name('show');
                        Route::post('{id}/reply', 'reply')->name('reply');
                        Route::post('{id}/accept', 'accept')->name('accept');
                        Route::post('{id}/decline', 'decline')->name('decline');
                        Route::post('{id}/cancel', 'cancel')->name('cancel');
                        Route::delete('{id}', 'destroy')->name('destroy');

                        // AJAX Modals
                        Route::get('modal/create', 'modalCreate')->name('modal.create');
                    });

                // Ticket Routes
                Route::get('tickets', [TicketController::class, 'index'])
                    ->name('ticket.index')
                    ->middleware('tickets.disable');
                Route::prefix('ticket')
                    ->name('ticket.')
                    ->middleware('tickets.disable')
                    ->controller(TicketController::class)
                    ->group(function () {
                        Route::post('create', 'store')->name('store');
                        Route::get('{id}', 'show')->name('show');
                        Route::post('{id}', 'reply')->name('reply');
                        Route::post('{id}/cancel', 'cancel')->name('cancel');
                        Route::delete('{id}', 'destroy')->name('destroy');
                        Route::get('{id}/{attachment_id}/download', 'download')->name('download');
                        Route::get('modal/create', 'modalCreate')->name('modal.create');
                    });


                // Tools Routes
                Route::prefix('tool')->name('tool.')->group(function () {
                    Route::get('/', fn() => redirect()->route('user.index'))->name('index');

                    Route::prefix('license-verification')
                        ->name('license-verification.')
                        ->middleware('addon.active:license_verification_tool')
                        ->controller(LicenseVerificationController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('/', 'verify')->name('verify');
                        });
                });

                // Restoration Notice Routing
                Route::post('restoration/dismiss/{type}/{id}', [UtilityController::class, 'dismissRestorationNotice'])
                    ->name('restoration.dismiss');

                // Settings Routes
                Route::prefix('settings')
                    ->name('settings.')
                    ->controller(SettingsController::class)
                    ->group(function () {
                        Route::get('account', 'account')->name('account');
                        Route::post('account', 'updateAccount')->name('account.update');
                        Route::get('profile', 'profile')->name('profile');
                        Route::post('profile', 'updateProfile')->name('profile.update');

                        Route::middleware('seller')->group(function () {
                            Route::get('payout', 'payout')->name('payout');
                            Route::post('payout', 'updatePayout')->name('payout.update');
                        });

                        Route::middleware(['license:2', 'premium.disable'])->group(function () {
                            Route::get('premium', 'premium')->name('premium');
                            Route::post('premium/cancel', 'cancelPremium')->name('premium.cancel');
                        });

                        Route::middleware('api.disable')->group(function () {
                            Route::get('api-key', 'apiKey')->name('api-key');
                            Route::post('api-key/generate', 'generateApiKey')->name('api-key.generate');
                        });

                        Route::get('badges', 'badges')->name('badges');
                        Route::post('badges/sortable', 'sortBadges')->name('badges.sortable');
                        Route::get('password', 'password')->name('password');
                        Route::post('password', 'updatePassword')->name('password.update');
                        Route::post('password/reset-otp', 'sendPasswordResetOtp')->name('password.reset_otp');
                        Route::get('password/verify', 'showPasswordResetOtpForm')->name('password.verify_otp');
                        Route::post('password/verify', 'verifyPasswordResetOtp')->name('password.verify_otp.submit');
                        Route::get('2fa', 'twoFactor')->name('2fa');
                        Route::post('2fa/enable', 'enable2FA')->name('2fa.enable');
                        Route::post('2fa/disable', 'disable2FA')->name('2fa.disable');

                        Route::middleware('id-verification.disable')->group(function () {
                            Route::get('id-verification', 'idVerification')->name('id-verification');
                            Route::post('id-verification', 'storeIdVerification')->name('id-verification.store');
                        });

                        // Chatbox Settings
                        Route::prefix('chatbox')
                            ->name('chatbox.')
                            ->middleware('chatbox.disable')
                            ->group(function () {
                                Route::get('blocked-users', 'chatboxBlockedUsers')->name('blocked-users');
                                Route::post('unblock-user', 'unblockUser')->name('unblock-user');
                            });

                        // Notification Settings
                        Route::prefix('notification')
                            ->name('notification.')
                            ->group(function () {
                                Route::get('preferences', 'notifications')->name('preferences');
                                Route::post('preferences', 'updateNotifications')->name('preferences.update');
                            });
                    });
            });
        });
});

// Public Routes
Route::middleware(['oauth.complete', 'verified', '2fa.verify'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])
        ->name('home')
        ->middleware('referral');

    Route::middleware('maintenance')->group(function () {
        // Premium Membership Routes
        Route::prefix('premium')
            ->name('premium.')
            ->middleware(['license:2', 'premium.disable'])
            ->controller(PremiumController::class)
            ->group(function () {
                Route::get('plans', 'index')->name('plans');
                Route::post('{id}/subscribe', 'subscribe')->name('subscribe')->middleware('auth');
            });

        Route::get('favorites', [GeneralController::class, 'favorites'])
            ->name('favorites.index')
            ->middleware('auth');

        // Category Routes
        Route::prefix('categories')
            ->name('categories.')
            ->controller(CategoryController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('{category_slug}', 'category')->name('category');
                Route::get('{category_slug}/{sub_category_slug}', 'subCategory')->name('sub-category');
            });

        // Product Routes (Frontend)
        Route::prefix('products')
            ->name('products.')
            ->controller(ProductController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('search', 'search')->name('search');
                Route::get('preview/{id}', 'preview')->name('preview');

                // Free Downloads
                Route::prefix('free')
                    ->name('free.')
                    ->middleware('free_products_login')
                    ->group(function () {
                        Route::post('download/{id}', [ProductController::class, 'freeDownload'])->name('download');
                        Route::get('download/{id}/external', [ProductController::class, 'freeExternalDownload'])->name('download.external');
                    });

                Route::middleware(['auth', 'oauth.complete', 'verified', '2fa.verify', 'id-verification.required'])->group(function () {
                    Route::get('free/license/{id}', [ProductController::class, 'freeLicense'])->name('free.license');

                    // Premium products Downloads
                    Route::prefix('premium')
                        ->name('premium.')
                        ->middleware(['license:2', 'premium.disable'])
                        ->controller(ProductController::class)
                        ->group(function () {
                            Route::post('download/{id}', 'premiumDownload')->name('download');
                            Route::get('download/{id}/external', 'premiumExternalDownload')->name('download.external');
                            Route::get('license/{id}', 'premiumLicense')->name('license');
                        });
                });

                // Product Details
                Route::middleware('product.views')->group(function () {
                    // Product Reports - Must come before catch-all route
                    Route::middleware(['auth', 'verified'])->controller(ProductReportController::class)->group(function () {
                        Route::get('{slug}/{product}/report', 'create')->name('report.create');
                        Route::post('{slug}/{product}/report', 'store')->name('report.store');
                        Route::get('{slug}/{product}/report/check', 'canReport')->name('report.check');
                    });

                    Route::get('{slug}/{id}/tab/{tab}', [ProductController::class, 'getAjaxTabContent'])->name('ajax_content');

                    // Buy Now route MUST come before the general show route
                    Route::post('{slug}/{id}/buy-now', [ProductController::class, 'buyNow'])
                        ->name('buy-now')
                        ->middleware(['auth', 'buy_now.disable']);

                    Route::get('{slug}/{id}/{tab?}', [ProductController::class, 'show'])->name('show');

                    Route::middleware('product_comments.disable')->group(function () {
                        Route::get('{slug}/{id}/comments/{comment_id}', [ProductController::class, 'comment'])->name('comment');
                    });

                    Route::middleware('product_reviews.disable')->group(function () {
                        Route::get('{slug}/{id}/reviews/{review_id}', [ProductController::class, 'review'])->name('review');

                        Route::middleware('auth')->group(function () {
                            Route::post('{slug}/{id}/reviews', [ProductController::class, 'reviewsStore'])->name('reviews.store');
                            Route::post('{slug}/{id}/reviews/{review_id}/reply', [ProductController::class, 'reviewsReply'])->name('reviews.reply');
                        });
                    });
                });
            });

        // Cart Routes
        Route::prefix('cart')
            ->name('cart.')
            ->controller(CartController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('add-product', 'addProduct')->name('add-product');
                Route::post('update-product/{id}', 'updateProduct')->name('update-product');
                Route::post('remove-product/{id}', 'removeProduct')->name('remove-product');
                Route::post('empty', 'empty')->name('empty');
            });

        // Checkout Routes
        Route::middleware(['auth', 'oauth.complete', 'verified', '2fa.verify', 'id-verification.required'])->group(function () {
            Route::post('cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

            Route::prefix('checkout')
                ->name('checkout.')
                ->controller(CheckoutController::class)
                ->group(function () {
                    Route::get('{id}', 'index')->name('index');
                    Route::post('{id}', 'process')->name('process')->middleware('trustip');
                });
        });

        // Profile Routes
        Route::prefix('user/{id}/profile')
            ->name('profile.')
            ->controller(ProfileController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('store', 'store')->name('store');
                Route::get('followers', 'followers')->name('followers');
                Route::get('following', 'following')->name('following');
                Route::get('reviews', 'reviews')->name('reviews')->middleware('product_reviews.disable');
            });

        // Notification Routes
        Route::prefix('{username}/notifications')
            ->name('notifications.')
            ->middleware('auth', 'oauth.complete', 'verified')
            ->controller(NotificationController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('recent', 'getRecentNotifications')->name('recent');
                Route::post('{id}/read', 'markAsRead')->name('read');
                Route::post('mark-all-read', 'markAllAsRead')->name('mark-all-read');
                Route::get('unread-count', 'getUnreadCount')->name('unread-count');
                Route::delete('{id}', 'deleteNotification')->name('delete');
                Route::delete('/', 'deleteAllNotifications')->name('delete-all');
            });

        // Chatbox Routes
        Route::prefix('chatbox')
            ->name('chatbox.')
            ->middleware(['auth', 'oauth.complete', 'verified', 'chatbox.disable'])
            ->controller(ChatboxController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::prefix('api')
                    ->name('api.')
                    ->middleware(['ajax.only:chatbox.index'])
                    ->group(function () {
                        Route::get('conversations', [ChatboxController::class, 'conversations'])->name('conversations');
                        Route::get('recent', [ChatboxController::class, 'recent'])->name('recent');
                        Route::get('conversation/{conversation}', [ChatboxController::class, 'conversation'])->name('conversation');
                        Route::post('users/search', [ChatboxController::class, 'searchUsers'])->name('users.search');
                    });

                Route::get('unread-count', 'getUnreadCount')->name('unread-count');
                Route::post('/', 'store')->name('store');
                Route::post('conversation/{conversation}/mark-read', 'markUnreadAsRead')->name('mark-read');
                Route::delete('message/{message}', 'deleteMessage')->name('message.delete');
                Route::delete('conversation/{conversation}', 'destroyConversation')->name('conversation.delete');
                Route::post('block', 'block')->name('block');
                Route::post('unblock', 'unblock')->name('unblock');
            });

        // Help Center Routes
        Route::prefix('help')
            ->name('help.')
            ->middleware('addon.active:help_center')
            ->controller(HelpController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('categories', fn() => redirect()->route('help.index'));
                Route::get('categories/{slug}', 'category')->name('category');
                Route::get('articles', fn() => redirect()->route('help.index'));
                Route::get('articles/{slug}', 'article')->name('article');
                Route::post('articles/{slug}', 'react')->name('react');
            });

        // Blog Routes
        Route::prefix('blog')
            ->name('blog.')
            ->middleware('blog.disable')
            ->controller(BlogController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('categories', fn() => redirect()->route('blog.index'));
                Route::get('categories/{slug}', 'category')->name('category');
                Route::get('articles', fn() => redirect()->route('blog.index'));
                Route::get('articles/{slug}', 'article')->name('article');
                Route::post('articles/{slug}', 'publishComment')->name('comment');
            });

        Route::prefix('contact')
            ->name('contact.')
            ->middleware(['contact.disable', 'mail'])
            ->controller(GeneralController::class)
            ->group(function () {
                Route::get('/', 'contact')->name('index');
                Route::post('/', 'handleContactForm')->name('submit');
            });

        // Feedback Routes
        Route::prefix('feedback')
            ->name('feedback.')
            ->middleware(['auth', 'verified'])
            ->controller(FeedbackController::class)
            ->group(function () {
                Route::get('/', 'create')->name('create');
                Route::post('/', 'store')->name('store');
            });

        Route::get('faqs', [GeneralController::class, 'faq']);

        Route::get('api-docs', [GeneralController::class, 'apiDocs'])
            ->name('api.docs')
            ->middleware('api.disable');

        Route::get('currency/{code}', [UtilityController::class, 'currency'])->name('currency');

        Route::get('{slug}', [GeneralController::class, 'page'])->name('page');
    });
});
