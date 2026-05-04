<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    DashboardController,
    AdminSearchController,
    LicenseController,
    AdminNotificationController,
    ChatboxController,
    AdvertisementController,
    AccountController,
    PageController
};
use App\Http\Controllers\Admin\Auth\{
    LoginController,
    ForgotPasswordController,
    ResetPasswordController,
    TwoFactorController
};
use App\Http\Controllers\Admin\Roles\{
    StaffController,
    UserController
};
use App\Http\Controllers\Admin\Premium\{
    PremiumController,
    PlanController as PremiumPlanController,
    SettingsController as PremiumSettingsController
};
use App\Http\Controllers\Admin\Products\{
    ProductController,
    ProductUpdatedController
};
use App\Http\Controllers\Admin\Records\{
    StatementController,
    SaleController,
    PurchaseController,
    SupportEarningController,
    ReferralEarningController,
    PremiumEarningController,
    RefundController as RecordsRefundController
};
use App\Http\Controllers\Admin\Reports\{
    ProductCommentReportController,
    ProductReportsController,
    FeedbackController
};
use App\Http\Controllers\Admin\Products\Categories\{
    ProductCategoryController,
    ProductSubCategoryController
};
use App\Http\Controllers\Admin\Tickets\{
    TicketController,
    CategoryController as TicketCategoryController,
    SettingsController as TicketSettingsController
};
use App\Http\Controllers\Admin\Help\{
    ArticleController as HelpArticleController,
    CategoryController as HelpCategoryController
};
use App\Http\Controllers\Admin\Blog\{
    CategoryController as BlogCategoryController,
    ArticleController as BlogArticleController,
    CommentController as BlogCommentController
};
use App\Http\Controllers\Admin\Appearance\{
    ThemeController,
    AddonController,
    MenuController,
    WidgetController
};
use App\Http\Controllers\Admin\FakerController;
use App\Http\Controllers\Admin\Financial\{
    CurrencyController,
    TaxController,
    PaymentGatewayController,
    PayoutMethodController,
    TransactionController,
    PayoutController,
    SettingsController as FinancialSettingsController
};
use App\Http\Controllers\Admin\Settings\{
    GeneralController,
    ProductController as ProductSettingsController,
    WatermarkController,
    NewsletterController,
    ReferralController as ReferralSettingsController,
    ProfileController as ProfileSettingsController,
    StorageDriverController,
    SupportPackageController,
    SellerLevelController,
    BadgeController,
    SocialAuthController,
    CaptchaController,
    ExtensionController,
    TranslationController
};
use App\Http\Controllers\Admin\Mail\{
    MailTemplateController,
    MailSettingsController
};
use App\Http\Controllers\Admin\Sections\{
    AnnouncementController
};
use App\Http\Controllers\Admin\Builders\{
    HomeController,
    HeaderController,
    FooterController
};
use App\Http\Controllers\Admin\System\{
    InfoController,
    MaintenanceController,
    RichTextImageController,
    CronJobController,
    CustomStyleController
};
use App\Http\Controllers\Admin\Verifications\{
    IdVerificationController,
    SettingsController as IdVerificationSettingsController
};

Route::name('admin.')->group(function () {

    // Authentication Routes
    Route::get('/', fn() => redirect()->route('admin.login'))->name('index');

    Route::controller(LoginController::class)->group(function () {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login')->name('login.store');
        Route::post('logout', 'logout')->name('logout');
    });

    // Password Reset Routes
    Route::middleware('mail')->controller(ForgotPasswordController::class)->group(function () {
        Route::get('password/reset', 'showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'sendResetLinkEmail')->name('password.email');
    });

    Route::controller(ResetPasswordController::class)->group(function () {
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
        Route::post('password/reset', 'reset')->name('password.update');
    });

    // Two-Factor Authentication
    Route::middleware('auth:admin')->controller(TwoFactorController::class)->group(function () {
        Route::get('2fa/verify', 'show2FaVerifyForm')->name('2fa.verify');
        Route::post('2fa/verify', 'verify2fa');
    });

    // Protected Admin Routes
    Route::middleware(['auth:admin', '2fa.verify:admin'])->group(function () {

        // Admin panel search
        Route::get('search', [AdminSearchController::class, 'search'])->name('search');

        Route::middleware('demo')->group(function () {

            Route::middleware('admin.role:admin,manager')->group(function () {

                // Dashboard
                Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

                // Dashboard AJAX endpoints
                Route::prefix('dashboard')
                    ->name('dashboard.')
                    ->middleware('ajax.only')
                    ->controller(DashboardController::class)
                    ->group(function () {
                        Route::get('congrats/send', 'sendCongratsEmail')->name('congrats.send');
                        Route::get('birthday/send', 'sendBirthdayWishes')->name('birthday.send');
                        Route::get('premium-analytics', 'getPremiumAnalytics')->name('premium-analytics');
                        Route::get('premium-comparison', 'getPremiumComparisonAnalytics')->name('premium-comparison');
                        Route::get('user-analytics', 'getUserAnalytics')->name('user-analytics');
                        Route::get('user-comparison', 'getUserComparisonAnalytics')->name('user-comparison');
                        Route::get('sales-analytics', 'getSalesAnalytics')->name('sales-analytics');
                        Route::get('sales-comparison', 'getSalesComparisonAnalytics')->name('sales-comparison');
                        Route::get('country-analytics', 'getCountryAnalytics')->name('country-analytics');
                        Route::get('support-ticket', 'getSupportTicketAnalytics')->name('support-ticket');
                        Route::get('statistics', 'getStatistics')->name('statistics');
                        Route::get('revenue-expense', 'getRevenueExpense')->name('revenue-expense');
                        Route::get('traffic-sources', 'getTrafficSourcesAnalytics')->name('traffic-sources');
                        Route::get('product-status', 'getProductStatus')->name('product-status');
                        Route::get('user-role', 'getUserRole')->name('user-role');
                        Route::get('revenue-source', 'getRevenueSource')->name('revenue-source');
                        Route::get('expenses-type', 'getExpensesType')->name('expenses-type');
                        Route::get('geo-chart', 'getGeoChartData')->name('geo-chart');
                        Route::get('product-issues', 'getProductIssues')->name('product-issues');
                        Route::get('user-verification', 'getUserVerification')->name('user-verification');
                        Route::get('refund-stats', 'getRefundStats')->name('refund-stats');
                        Route::post('notes', 'storeNote')->name('notes.store');
                        Route::delete('notes/{id}/delete', 'deleteNote')->name('notes.delete');
                    });

                // License Verification
                Route::prefix('license-verification')
                    ->name('license-verification.')
                    ->middleware('addon.active:license_verification_tool')
                    ->controller(LicenseController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/', 'verify')->name('check');
                    });

                // Notifications
                Route::prefix('all-notifications')
                    ->name('notifications.')
                    ->controller(AdminNotificationController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('{adminNotification}/view', 'view')->name('view');
                        Route::post('read-all', 'readAll')->name('read.all');
                        Route::delete('delete-read', 'deleteRead')->name('delete.read');
                    });

                // Chatbox
                Route::prefix('chatbox')
                    ->name('chatbox.')
                    ->controller(ChatboxController::class)
                    ->group(function () {
                        Route::get('settings', 'index')->name('index');
                        Route::post('settings', 'store')->name('store');
                        Route::get('history', 'history')->name('history');
                        Route::post('ban-user/{user}', 'banUser')->name('ban-user');
                        Route::post('unban-user/{user}', 'unbanUser')->name('unban-user');
                    });

                // Roles Management
                Route::prefix('roles')->name('roles.')->group(function () {

                    // Users
                    Route::prefix('users')
                        ->name('users.')
                        ->controller(UserController::class)->group(function () {
                            Route::delete('/', 'bulkDelete')->name('bulk-delete');
                            Route::get('create-modal', 'createModal')->name('create.modal');
                            Route::post('{user}/sendmail', 'sendMail')->name('sendmail');
                            Route::post('{user}/status', 'updateStatus')->name('status.update');

                            Route::middleware('seller')->group(function () {
                                Route::post('{user}/payout', 'updatePayout')->name('payout.update');

                                Route::middleware('referral.disable')->group(function () {
                                    Route::get('{user}/referrals', 'referrals')->name('referrals');
                                    Route::delete('{user}/referrals/{id}', 'deleteReferral')->name('referrals.delete');
                                });

                                Route::post('{user}/featured', 'makeSellerFeatured')->name('featured');
                                Route::post('{user}/featured/remove', 'removeSellerFeatured')->name('featured.remove');
                            });

                            Route::get('{user}/wallet', 'wallet')->name('wallet.index');
                            Route::post('{user}/wallet', 'updateWallet')->name('wallet.update');
                            Route::post('{user}/premium/assign', 'assignPremium')->name('premium.assign')->middleware(['license:2']);
                            Route::post('{user}/premium/upgrade', 'upgradePremium')->name('premium.upgrade')->middleware(['license:2']);
                            Route::post('{user}/premium/cancel', 'cancelPremium')->name('premium.cancel')->middleware(['license:2']);
                            Route::post('{user}/profile', 'updateProfile')->name('profile.update');

                            Route::prefix('{user}/api-key')
                                ->name('api-key.')
                                ->middleware('api.disable')
                                ->group(function () {
                                    Route::get('/', [UserController::class, 'apiKey'])->name('index');
                                    Route::post('/', [UserController::class, 'apiKeyGenerate'])->name('generate');
                                });

                            Route::prefix('{user}/security')->name('security.')->group(function () {
                                Route::get('/', 'security')->name('index');
                                Route::patch('/password', 'updatePassword')->name('password.update');
                                Route::put('/2fa', 'update2FA')->name('2fa.update');
                            });

                            Route::prefix('{user}/badges')->name('badges.')->group(function () {
                                Route::get('/', [UserController::class, 'badges'])->name('index');
                                Route::post('store', [UserController::class, 'addBadge'])->name('store');
                                Route::delete('{id}', [UserController::class, 'deleteBadge'])->name('destroy');
                            });

                            Route::get('{user}/login', 'login')->name('login')->middleware('demo:GET');

                            // Trash routes
                            Route::prefix('trash')->name('trash.')->group(function () {
                                Route::get('/', 'trash')->name('index');
                                Route::post('{id}/restore', 'restore')->name('restore');
                                Route::delete('{id}/permanently-delete', 'permanentlyDelete')
                                    ->name('permanently-delete')
                                    ->middleware('admin.role:admin');
                            });
                        });

                    Route::resource('users', UserController::class)->except(['show']);

                    // Staff Management (Unified: managers, accountants, reviewers)
                    Route::prefix('staff')->name('staff.')
                        ->middleware('admin.role:admin')
                        ->controller(StaffController::class)->group(function () {
                            Route::delete('/', 'bulkDelete')->name('bulk-delete');
                            Route::get('create/modal', 'createModal')->name('create.modal');
                            Route::post('{staff}/sendmail', 'sendMail')->name('sendmail');
                            Route::post('{staff}/status', 'updateStatus')->name('status.update');
                            Route::get('{staff}/login', 'login')->name('login')->middleware('demo:GET');
                            Route::get('{staff}/security', 'security')->name('security.index');
                            Route::get('{staff}/privilege', 'privilege')->name('privilege.index');
                            Route::post('{staff}/privilege/update', 'updatePrivilege')->name('privilege.update');
                            Route::patch('{staff}/security/password', 'updatePassword')->name('password.update');
                            Route::put('{staff}/security/2fa', 'update2FA')->name('2fa.update');
                        });

                    Route::resource('staff', StaffController::class)
                        ->middleware('admin.role:admin')
                        ->except(['show', 'create']);
                });

                // Premium Membership Management
                Route::prefix('premium')
                    ->name('premium.')
                    ->middleware(['license:2'])
                    ->group(function () {
                        Route::controller(PremiumSettingsController::class)->group(function () {
                            Route::get('settings', 'index')->name('settings.index');
                            Route::post('settings', 'update')->name('settings.update');
                        });

                        Route::prefix('plans')
                            ->name('plans.')
                            ->controller(PremiumPlanController::class)
                            ->group(function () {
                                Route::get('/', 'index')->name('index');
                                Route::post('sortable', 'sortable')->name('sortable');
                                Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                                Route::post('bulk-active', 'bulkActive')->name('bulk-active');
                                Route::post('bulk-inactive', 'bulkInactive')->name('bulk-inactive');

                                Route::post('create', 'createPlan')->name('create');
                                Route::put('update/{premiumPlan}', 'updatePlan')->name('update');
                                Route::delete('{premiumPlan}', 'destroy')->name('destroy');
                            });

                        Route::prefix('members')
                            ->name('members.')
                            ->controller(PremiumController::class)
                            ->group(function () {
                                Route::get('/', 'index')->name('index');
                                Route::post('{premium}/cancel', 'cancel')->name('cancel');
                                Route::post('{premium}/hold', 'hold')->name('hold');
                                Route::post('{premium}/unhold', 'unhold')->name('unhold');

                                Route::post('bulk-hold', 'bulkHold')->name('bulk-hold');
                                Route::post('bulk-resume', 'bulkResume')->name('bulk-resume');
                                Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                            });
                    });
            });

            // Products Management
            Route::prefix('products')->name('products.')
                ->middleware('admin.role:admin,manager,reviewer')
                ->group(function () {

                    // Product Categories - Admin and Manager can manage
                    Route::controller(ProductCategoryController::class)
                        ->prefix('categories')
                        ->name('categories.')
                        ->middleware('admin.role:admin,manager')
                        ->group(function () {
                            Route::get('slug', 'slug')->name('slug');
                            Route::post('sortable', 'sortable')->name('sortable');
                            Route::delete('bulk-destroy', 'bulkDestroy')->name('bulk-destroy');
                        });

                    Route::resource('categories', ProductCategoryController::class)
                        ->middleware('admin.role:admin,manager')
                        ->except(['show']);

                    // Product Sub-Categories (Must be before resource route)
                    Route::prefix('categories/sub-categories')
                        ->name('categories.sub-categories.')
                        ->controller(ProductSubCategoryController::class)
                        ->middleware('admin.role:admin,manager')
                        ->group(function () {
                            Route::delete('bulk-destroy', 'bulkDestroy')->name('bulk-destroy');
                            Route::get('/', 'index')->name('index');
                            Route::post('/', 'store')->name('store');
                            Route::put('{subCategory}', 'update')->name('update');
                            Route::delete('{subCategory}', 'destroy')->name('destroy');

                            Route::get('slug', 'slug')->name('slug');
                            Route::get('create', 'createModal')->name('create.modal');
                            Route::get('edit/{subCategory}', 'editModal')->name('edit.modal');
                            Route::post('sortable', 'sortable')->name('sortable');
                        });

                    // Main Products
                    Route::controller(ProductController::class)->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('bulk-approve', 'bulkApprove')->name('bulk-approve');
                        Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');

                        Route::prefix('trash')->name('trash.')->group(function () {
                            Route::get('/', 'trash')->name('index');
                            Route::post('bulk-restore', 'bulkRestore')->name('bulk-restore');
                            Route::delete('bulk-permanently-delete', 'bulkPermanentlyDelete')->name('bulk-permanently-delete')->middleware('admin.role:admin');
                            Route::post('{id}/restore', 'restore')->name('restore');
                            Route::delete('{id}/permanently-delete', 'permanentlyDelete')->name('permanently-delete')->middleware('admin.role:admin');
                        });

                        Route::prefix('{id}/actions')->name('actions.')->group(function () {
                            Route::get('/', 'actions')->name('index');
                            Route::post('/', 'actionsUpdate')->name('update');
                            Route::put('/status', 'actionsStatus')->name('status');
                        });

                        Route::prefix('{id}/history')->name('history.')->group(function () {
                            Route::get('/', 'history')->name('index');
                            Route::delete('{history_id}', 'historyDelete')->name('delete');
                        });

                        Route::middleware('discount.disable')->prefix('{id}/discount')->name('discount.')->group(function () {
                            Route::get('/', 'discount')->name('index');
                            Route::post('/', 'discountStore')->name('store');
                            Route::put('/update-status', 'discountStatus')->name('update-status');
                            Route::delete('/', 'discountRemove')->name('remove');
                        });

                        Route::prefix('{id}/reviews')->name('reviews.')->group(function () {
                            Route::get('/', 'reviews')->name('index');
                            Route::delete('{review_id}', 'reviewsDelete')->name('delete');
                        });

                        Route::prefix('{id}/comments')->name('comments.')->group(function () {
                            Route::get('/', 'comments')->name('index');
                            Route::delete('{comment_id}', 'commentsDelete')->name('delete');
                        });

                        Route::prefix('{id}/statistics')->name('statistics.')->group(function () {
                            Route::get('/', 'statistics')->name('index');
                            Route::post('recalculate', 'recalculateStatistics')->name('recalculate');
                            Route::get('export/{format?}', 'exportStatistics')->name('export')->where('format', 'pdf|excel');
                        });

                        Route::get('{id}/download', 'download')->name('download');
                        Route::post('{id}/featured', 'makeFeatured')->name('featured');
                        Route::post('{id}/featured/remove', 'removeFeatured')->name('featured.remove');
                        Route::post('{id}/premium', 'makePremium')->name('premium');
                        Route::post('{id}/premium/remove', 'removePremium')->name('premium.remove');
                        Route::delete('{id}/soft-delete', 'softDelete')->name('soft-delete');
                    });

                    // Product Updates
                    Route::prefix('updated')
                        ->name('updated.')
                        ->controller(ProductUpdatedController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('bulk-approve', 'bulkApprove')->name('bulk-approve');
                            Route::post('bulk-reject', 'bulkReject')->name('bulk-reject');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                            Route::get('{productUpdate}/history', 'history')->name('history');
                            Route::get('{productUpdate}/actions', 'actions')->name('actions');
                            Route::post('{productUpdate}/actions/update', 'actionsUpdate')->name('actions.update');
                            Route::get('{productUpdate}/download', 'download')->name('download');
                            Route::delete('{productUpdate}', 'destroy')->name('destroy');
                        });
                });

            // main & updated product details show route with singular product prefix
            Route::prefix('product')
                ->name('products.')
                ->middleware('admin.role:admin,manager,reviewer')
                ->group(function () {
                    Route::get('{id}/show', [ProductController::class, 'show'])->name('show');
                    Route::get('{productUpdate}/updated', [ProductUpdatedController::class, 'show'])->name('updated.show');
                });

            // Records - Admin, Manager, and Accountant can view
            Route::prefix('records')
                ->name('records.')
                ->middleware('admin.role:admin,manager,accountant')
                ->group(function () {

                    Route::prefix('statements')
                        ->name('statements.')
                        ->controller(StatementController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{statement}', 'detailsModal')->name('details.modal');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('admin.role:admin,manager');
                            Route::delete('{statement}', 'destroy')->name('destroy')->middleware('admin.role:admin,manager');
                        });

                    Route::prefix('sales')
                        ->name('sales.')
                        ->controller(SaleController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{sale}', 'detailsModal')->name('details.modal');
                            Route::post('bulk-cancel', 'bulkCancel')->name('bulk-cancel')->middleware('admin.role:admin,manager');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('admin.role:admin,manager');
                            Route::post('{sale}/cancel', 'cancel')->name('cancel')->middleware('admin.role:admin,manager');
                            Route::delete('{sale}', 'destroy')->name('destroy')->middleware('admin.role:admin,manager');
                        });


                    Route::prefix('purchases')
                        ->name('purchases.')
                        ->controller(PurchaseController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{purchase}', 'detailsModal')->name('details.modal');
                        });

                    Route::prefix('support-earnings')
                        ->name('support-earnings.')
                        ->controller(SupportEarningController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{earning}', 'detailsModal')->name('details.modal');
                        });

                    Route::prefix('referral-earnings')
                        ->name('referral-earnings.')
                        ->controller(ReferralEarningController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{earning}', 'detailsModal')->name('details.modal');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('admin.role:admin,manager');
                            Route::delete('{earning}', 'destroy')->name('destroy')->middleware('admin.role:admin,manager');
                        });

                    // Premium Earning Routes
                    Route::prefix('premium-earnings')
                        ->name('premium-earnings.')
                        ->controller(PremiumEarningController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{earning}', 'detailsModal')->name('details.modal');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('admin.role:admin,manager');
                            Route::delete('{premiumEarning}', 'destroy')->name('destroy')->middleware('admin.role:admin,manager');
                        });

                    Route::prefix('refunds')
                        ->name('refunds.')
                        ->middleware('refunds.disable')
                        ->controller(RecordsRefundController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{refund}', 'detailsModal')->name('details.modal')->withTrashed();
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('admin.role:admin,manager');
                            Route::post('{refund}/send-email', 'sendEmail')->name('send-email')->middleware('admin.role:admin,manager')->withTrashed();
                            Route::post('{refund}/restore', 'restore')->name('restore')->middleware('admin.role:admin,manager')->withTrashed();
                            Route::delete('{refund}', 'destroy')->name('destroy')->middleware('admin.role:admin,manager')->withTrashed();

                            // Trash routes
                            Route::prefix('trash')->name('trash.')->group(function () {
                                Route::get('/', 'trash')->name('index');
                                Route::post('bulk-restore', 'bulkRestore')->name('bulk-restore')->middleware('admin.role:admin,manager');
                                Route::post('{id}/restore', 'restore')->name('restore')->middleware('admin.role:admin,manager');
                                Route::delete('{id}/permanently-delete', 'permanentlyDelete')->name('permanently-delete')->middleware('admin.role:admin');
                            });
                        });
                });
        });

        Route::middleware('admin.role:admin,manager')->group(function () {

            // ID Verifications
            Route::prefix('id-verification')
                ->name('id-verification.')
                ->middleware(['id-verification.disable'])
                ->group(function () {
                    Route::controller(IdVerificationController::class)->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('bulk-approve', 'bulkApprove')->name('bulk-approve')->middleware('demo');
                        Route::post('bulk-reject', 'bulkReject')->name('bulk-reject')->middleware('demo');
                        Route::get('{idVerification}', 'review')->name('review')->where('idVerification', '[0-9]+');
                        Route::post('{idVerification}/reject', 'reject')->name('reject')->where('idVerification', '[0-9]+');
                        Route::post('{idVerification}/approve', 'approve')->name('approve')->where('idVerification', '[0-9]+');
                        Route::get('{idVerification}/{document}/view', 'document')->name('document')->where('idVerification', '[0-9]+');
                        Route::post('{idVerification}/{document}/download', 'download')->name('download')->where('idVerification', '[0-9]+');
                        Route::delete('{idVerification}', 'destroy')->name('destroy')->middleware('demo')->where('idVerification', '[0-9]+');
                    });

                    Route::prefix('settings')
                        ->name('settings.')
                        ->controller(IdVerificationSettingsController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                        });
                });

            // Ads
            Route::prefix('ads')
                ->name('ads.')
                ->middleware('demo')
                ->controller(AdvertisementController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{advertisement}/edit', 'edit')->name('edit');
                    Route::post('{advertisement}', 'update')->name('update');
                });

            // Reports
            Route::prefix('reports')->name('reports.')->group(function () {

                Route::prefix('comment-reports')
                    ->name('comment-reports.')
                    ->controller(ProductCommentReportController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('bulk-keep', 'bulkKeep')->name('bulk-keep')->middleware('demo');
                        Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('demo');
                        Route::post('{productCommentReport}/keep', 'keepComment')->name('keep');
                        Route::delete('{productCommentReport}/delete', 'deleteComment')->middleware('demo')->name('delete');
                    });

                Route::prefix('product-reports')
                    ->name('product-reports.')
                    ->controller(ProductReportsController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::put('settings', 'updateSettings')->name('settings.update')->middleware('demo');
                        Route::post('bulk-resolve', 'bulkResolve')->name('bulk-resolve')->middleware('demo');
                        Route::post('bulk-cancel', 'bulkCancel')->name('bulk-cancel')->middleware('demo');
                        Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('demo');
                        Route::post('{product}/restrict', 'restrictProduct')->name('restrict')->middleware('demo');
                        Route::post('{product}/unrestrict', 'unrestrictProduct')->name('unrestrict')->middleware('demo');
                        Route::delete('{product}/delete-product', 'deleteProduct')->name('delete-product')->middleware('demo');
                        Route::put('{report}/update-status', 'updateStatus')->name('update-status')->middleware('demo');
                        Route::delete('{report}/delete', 'destroy')->name('destroy')->middleware('demo');
                    });

                Route::prefix('feedback')
                    ->name('feedback.')
                    ->controller(FeedbackController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('bulk-review', 'bulkReview')->name('bulk-review')->middleware('demo');
                        Route::post('bulk-resolve', 'bulkResolve')->name('bulk-resolve')->middleware('demo');
                        Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('demo');
                        Route::put('{feedback}/status', 'updateStatus')->name('update-status')->middleware('demo');
                        Route::delete('{feedback}', 'destroy')->name('destroy')->middleware('demo');
                    });
            });
        });

        Route::middleware(['demo', 'admin.role:admin,manager'])->group(function () {

            // Tickets
            Route::prefix('tickets')
                ->name('tickets.')
                ->group(function () {
                    Route::prefix('settings')
                        ->name('settings.')
                        ->controller(TicketSettingsController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('/', 'update')->name('update');
                        });
                    Route::prefix('categories')
                        ->name('categories.')
                        ->controller(TicketCategoryController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('sortable', 'sortable')->name('sortable');
                            Route::post('bulk-inactive', 'bulkInactive')->name('bulk-inactive');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                            Route::post('/', 'store')->name('store');
                            Route::put('{category}', 'update')->name('update');
                            Route::delete('{category}', 'destroy')->name('destroy');
                        });

                    Route::controller(TicketController::class)->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('create-modal', 'createModal')->name('create.modal');
                        Route::delete('/', 'bulkDestroy')->name('bulk-destroy');
                        Route::post('create', 'store')->name('store');
                        Route::post('bulk-close', 'bulkClose')->name('bulk-close');
                        Route::get('{ticket}', 'show')->name('show')->withTrashed();
                        Route::post('{ticket}', 'reply')->name('reply')->withTrashed();
                        Route::post('{ticket}/close', 'close')->name('close')->withTrashed();
                        Route::post('{ticket}/restore', 'restore')->name('restore')->withTrashed();
                        Route::delete('{ticket}/delete', 'destroy')->name('destroy')->withTrashed();
                        Route::get('{ticket}/{attachment}/download', 'download')->name('download');

                        // Trash routes
                        Route::prefix('trash')->name('trash.')->group(function () {
                            Route::get('/', 'trash')->name('index');
                            Route::post('bulk-restore', 'bulkRestore')->name('bulk-restore')->middleware('admin.role:admin,manager');
                            Route::post('{id}/restore', 'restore')->name('restore')->middleware('admin.role:admin,manager');
                            Route::delete('{id}/permanently-delete', 'permanentlyDelete')->name('permanently-delete')->middleware('admin.role:admin');
                        });
                    });
                });

            // Help Center
            Route::prefix('help')
                ->name('help.')
                ->middleware('addon.active:help_center')
                ->group(function () {
                    Route::controller(HelpArticleController::class)->group(function () {
                        Route::get('articles/slug', 'slug')->name('articles.slug');
                    });
                    Route::resource('articles', HelpArticleController::class)->except(['show']);

                    Route::controller(HelpCategoryController::class)->group(function () {
                        Route::get('categories/slug', 'slug')->name('categories.slug');
                        Route::post('categories/sortable', 'sortable')->name('categories.sortable');
                    });
                    Route::resource('categories', HelpCategoryController::class)->except(['show']);
                });

            // Blog
            Route::prefix('blog')
                ->name('blog.')
                ->middleware('blog.disable')
                ->group(function () {
                    Route::controller(BlogCategoryController::class)->group(function () {
                        Route::get('categories/slug', 'slug')->name('categories.slug');
                    });

                    Route::delete('categories/bulk-delete', [BlogCategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
                    Route::resource('categories', BlogCategoryController::class)->except(['show', 'create', 'edit']);

                    Route::controller(BlogArticleController::class)->group(function () {
                        Route::get('articles/slug', 'slug')->name('articles.slug');
                        Route::get('articles/categories/{lang}', 'getCategories')->middleware('ajax.only');
                    });

                    Route::delete('articles/bulk-delete', [BlogArticleController::class, 'bulkDelete'])->name('articles.bulk-delete');
                    Route::resource('articles', BlogArticleController::class)->except(['show']);

                    Route::prefix('comments')
                        ->name('comments.')
                        ->controller(BlogCommentController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('bulk-publish', 'bulkPublish')->name('bulk-publish');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                            Route::post('{comment}/unhold', 'unhold')->name('unhold');
                            Route::put('{comment}', 'update')->name('update');
                            Route::delete('{comment}', 'destroy')->name('destroy');
                        });
                });

            // Appearance
            Route::prefix('appearance')
                ->name('appearance.')
                ->group(function () {

                    Route::prefix('themes')->name('themes.')->controller(ThemeController::class)->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('upload', 'upload')->name('upload');
                        Route::post('{theme}/active', 'makeActive')->name('active')->middleware('admin.role:admin');

                        Route::prefix('{theme}/settings')->name('settings.')->group(function () {
                            Route::get('/', [ThemeController::class, 'showSettings'])->name('index');
                            Route::get('{group}', [ThemeController::class, 'showSettings'])->name('group');
                            Route::post('{group}', [ThemeController::class, 'updateSettings'])->name('update');
                        });

                        Route::prefix('{theme}/custom-css')->name('custom-css.')->group(function () {
                            Route::get('/', [ThemeController::class, 'showCustomCss'])->name('index');
                            Route::post('/', [ThemeController::class, 'updateCustomCss'])->name('update');
                        });
                    });

                    Route::prefix('addons')->name('addons.')->controller(AddonController::class)->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/', 'upload')->name('upload');
                        Route::post('{addon}/update', 'update')->name('update');
                    });

                    Route::controller(MenuController::class)->prefix('menus')->name('menus.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('nestable', 'nestable')->name('nestable');
                        Route::post('bulk-add', 'bulkAdd')->name('bulk-add');
                        Route::post('bulk-delete', 'bulkDelete')->name('bulk-delete');
                        Route::get('menu-list', 'renderMenuList')->name('menu-list');
                        Route::post('import', 'import')->name('import');
                        Route::put('{menu}', 'update')->name('update');
                        Route::delete('{menu}', 'destroy')->name('destroy');
                    });

                    // Widgets
                    Route::controller(WidgetController::class)->prefix('widgets')->name('widgets.')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::post('/', 'store')->name('store');
                        Route::post('sortable', 'sortable')->name('sortable');
                        Route::get('{instance}/instance', 'instance')->name('instance');
                        Route::put('{instance}', 'update')->name('update');
                        Route::post('{instance}/toggle', 'toggle')->name('toggle');
                        Route::delete('{instance}', 'destroy')->name('destroy');
                    });
                });

            // Faker
            Route::prefix('faker')
                ->name('faker.')
                ->middleware('addon.active:faker')
                ->controller(FakerController::class)
                ->group(function () {
                    Route::get('settings', 'settings');
                    Route::post('settings', 'settingsUpdate')->name('settings');

                    Route::prefix('tools')->name('tools.')->group(function () {
                        Route::get('/', [FakerController::class, 'tools'])->name('index');
                        Route::get('{tool}', [FakerController::class, 'tool'])->name('tool');
                        Route::post('{tool}/generate', [FakerController::class, 'generate'])->name('generate');
                    });
                });

            // Settings
            Route::prefix('settings')
                ->name('settings.')
                ->group(function () {

                    Route::prefix('general')
                        ->name('general.')
                        ->controller(GeneralController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                        });

                    Route::prefix('product')
                        ->name('product.')
                        ->controller(ProductSettingsController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                        });

                    Route::prefix('watermark')
                        ->name('watermark.')
                        ->controller(WatermarkController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                        });

                    Route::prefix('newsletter')
                        ->name('newsletter.')
                        ->controller(NewsletterController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                        });

                    Route::prefix('referral')
                        ->name('referral.')
                        ->controller(ReferralSettingsController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                        });

                    Route::prefix('profile')
                        ->name('profile.')
                        ->controller(ProfileSettingsController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                        });

                    Route::prefix('storage-drivers')
                        ->name('storage-drivers.')
                        ->controller(StorageDriverController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update', 'update')->name('update');
                            Route::post('test', 'connectionTest')->name('test');
                        });

                    Route::prefix('support-packages')
                        ->name('support-packages.')
                        ->middleware('product_support.disable')
                        ->controller(SupportPackageController::class)
                        ->group(function () {
                            Route::post('sortable', 'sortable')->name('sortable');
                            Route::get('{supportPackage}/edit-modal', 'editModal')->name('edit-modal');
                            Route::resource('/', SupportPackageController::class)->parameters(['' => 'supportPackage'])->except(['show', 'create', 'edit']);
                        });

                    Route::prefix('seller-levels')
                        ->name('seller-levels.')
                        ->controller(SellerLevelController::class)
                        ->group(function () {
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                            Route::get('{sellerLevel}/edit-modal', 'editModal')->name('edit-modal');
                            Route::resource('/', SellerLevelController::class)->parameters(['' => 'sellerLevel'])->except(['show', 'create', 'edit']);
                        });

                    Route::prefix('badges')
                        ->name('badges.')
                        ->controller(BadgeController::class)
                        ->group(function () {
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                            Route::get('{badge}/edit-modal', 'editModal')->name('edit-modal');
                            Route::resource('/', BadgeController::class)->parameters(['' => 'badge'])->except(['show', 'create', 'edit']);
                        });

                    Route::prefix('social-auth')
                        ->name('social-auth.')
                        ->controller(SocialAuthController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('sortable', 'sortable')->name('sortable');
                            Route::post('upload', 'upload')->name('upload');
                            Route::get('{socialAuth}/edit', 'edit')->name('edit');
                            Route::post('{socialAuth}', 'update')->name('update');
                        });

                    Route::prefix('captcha')
                        ->name('captcha.')
                        ->controller(CaptchaController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('{captchaProvider}/edit', 'edit')->name('edit');
                            Route::post('{captchaProvider}', 'update')->name('update');
                            Route::post('{captchaProvider}/default', 'makeDefault')->name('default');
                        });

                    Route::prefix('extensions')
                        ->name('extensions.')
                        ->controller(ExtensionController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('change-status', 'changeStatus')->name('change-status');
                            Route::get('{extension}/edit', 'edit')->name('edit');
                            Route::post('{extension}', 'update')->name('update');
                        });

                    Route::prefix('translation')
                        ->name('translation.')
                        ->controller(TranslationController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('/', 'update')->name('update');

                            Route::prefix('translates')
                                ->name('translates.')
                                ->controller(TranslationController::class)
                                ->group(function () {
                                    Route::get('/', 'translates')->name('index');
                                    Route::post('/', 'translatesUpdate')->name('update');
                                });
                        });
                });

            // Manage Pages
            Route::prefix('pages')->name('pages.')->group(function () {
                Route::controller(PageController::class)->group(function () {
                    Route::get('slug', 'slug')->name('slug');
                    Route::delete('/', 'bulkDestroy')->name('bulk-destroy');
                });
                Route::resource('/', PageController::class)->except(['show'])->parameters(['' => 'page']);
            });

            // Mail Management
            Route::prefix('mail')->name('mail.')->group(function () {
                // Mail Settings
                Route::prefix('settings')->name('settings.')->controller(MailSettingsController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('update', 'update')->name('update');
                    Route::post('test', 'test')->name('test');
                });

                // Mail Templates
                Route::prefix('templates')
                    ->name('templates.')
                    ->controller(MailTemplateController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                        Route::get('/create', 'create')->name('create');
                        Route::post('/', 'store')->name('store');
                        Route::get('{mailTemplate}/edit', 'edit')->name('edit');
                        Route::post('{mailTemplate}', 'update')->name('update');
                        Route::delete('{mailTemplate}', 'destroy')->name('destroy');
                    });
            });

            // Sections
            Route::prefix('sections')
                ->name('sections.')
                ->group(function () {

                    Route::prefix('announcement')
                        ->name('announcement.')
                        ->controller(AnnouncementController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('/', 'update')->name('update');
                        });
                });

            // Builders
            Route::prefix('builders')
                ->name('builders.')
                ->group(function () {

                    Route::prefix('home')
                        ->name('home.')
                        ->controller(HomeController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update-layout', 'updateLayout')->name('update-layout');
                            Route::post('upload-image', 'uploadImage')->name('upload-image');
                            Route::get('edit-block/{blockId}', 'editBlock')->name('edit-block')->middleware('ajax.only');
                            Route::post('update/{blockId}', 'updateBlock')->name('update');
                            Route::get('section-settings', 'sectionSettings')->name('section-settings')->middleware('ajax.only');
                        });

                    Route::prefix('header')
                        ->name('header.')
                        ->controller(HeaderController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update-layout', 'updateLayout')->name('update-layout');
                            Route::post('upload-image', 'uploadImage')->name('upload-image');
                            Route::get('edit-block/{blockId}', 'editBlock')->name('edit-block')->middleware('ajax.only');
                            Route::get('section-settings', 'sectionSettings')->name('section-settings')->middleware('ajax.only');
                        });

                    Route::prefix('footer')
                        ->name('footer.')
                        ->controller(FooterController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('update-layout', 'updateLayout')->name('update-layout');
                            Route::post('upload-image', 'uploadImage')->name('upload-image');
                            Route::get('edit-block/{blockId}', 'editBlock')->name('edit-block')->middleware('ajax.only');
                            Route::get('section-settings', 'sectionSettings')->name('section-settings')->middleware('ajax.only');
                        });
                });

            // System
            Route::prefix('system')
                ->name('system.')
                ->group(function () {
                    Route::controller(InfoController::class)->group(function () {
                        Route::get('info', 'index')->name('info.index');
                        Route::get('info/cache', 'cache')->name('info.cache')->middleware('demo:GET');
                    });

                    Route::controller(MaintenanceController::class)
                        ->group(function () {
                            Route::get('maintenance', 'index');
                            Route::post('maintenance', 'update')->name('maintenance')->middleware('admin.role:admin');
                        });

                    Route::prefix('rich-text-images')
                        ->name('rich-text-images.')
                        ->controller(RichTextImageController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::delete('{richTextImage}', 'destroy')->name('destroy');
                        });

                    Route::prefix('cronjob')
                        ->name('cronjob.')
                        ->controller(CronJobController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('key-generate', 'generateKey')->name('generate-key');
                            Route::post('key-remove', 'removeKey')->name('remove-key');
                            Route::post('run', 'run')->name('run');
                        });

                    // Custom Styles
                    Route::prefix('custom-style')
                        ->name('custom-style.')
                        ->middleware('admin.role:admin')
                        ->group(function () {
                            Route::controller(CustomStyleController::class)->group(function () {
                                Route::get('/', 'index')->name('index');
                                Route::post('/', 'update')->name('update');
                            });
                        });
                });
        });

        Route::middleware(['demo'])->group(function () {
            // Financial - Admin, Manager, and Accountant can access
            Route::prefix('financial')
                ->name('financial.')
                ->middleware('admin.role:admin,manager,accountant')
                ->group(function () {
                    Route::controller(FinancialSettingsController::class)
                        ->middleware('admin.role:admin,manager')
                        ->group(function () {
                            Route::get('settings', 'index');
                            Route::post('settings', 'update')->name('settings');
                        });

                    Route::prefix('currencies')->name('currencies.')
                        ->middleware('admin.role:admin,manager')
                        ->group(function () {
                            Route::post('sortable', [CurrencyController::class, 'sortable'])
                                ->name('sortable');
                            Route::post('{currency}/default', [CurrencyController::class, 'makeDefault'])
                                ->name('default');
                            Route::get('/', [CurrencyController::class, 'index'])->name('index');
                            Route::post('create', [CurrencyController::class, 'store'])->name('store');
                            Route::put('{currency}', [CurrencyController::class, 'update'])->name('update');
                            Route::delete('{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
                        });

                    // Buyer Tax Routes
                    Route::prefix('buyer-taxes')
                        ->name('buyer-taxes.')
                        ->middleware('admin.role:admin,manager')
                        ->controller(TaxController::class)
                        ->group(function () {
                            Route::get('/', 'index')->defaults('type', 'buyer')->name('index');
                            Route::delete('bulk-delete', 'bulkDelete')->defaults('type', 'buyer')->name('bulk-delete');
                            Route::post('create', 'store')->defaults('type', 'buyer')->name('store');
                            Route::put('/{id}', 'update')->defaults('type', 'buyer')->name('update');
                            Route::delete('/{id}', 'destroy')->defaults('type', 'buyer')->name('destroy');
                        });

                    // Seller Tax Routes
                    Route::prefix('seller-taxes')
                        ->name('seller-taxes.')
                        ->middleware('admin.role:admin,manager')
                        ->controller(TaxController::class)
                        ->group(function () {
                            Route::get('/', 'index')->defaults('type', 'seller')->name('index');
                            Route::delete('bulk-delete', 'bulkDelete')->defaults('type', 'seller')->name('bulk-delete');
                            Route::post('create', 'store')->defaults('type', 'seller')->name('store');
                            Route::put('/{id}', 'update')->defaults('type', 'seller')->name('update');
                            Route::delete('/{id}', 'destroy')->defaults('type', 'seller')->name('destroy');
                        });

                    Route::prefix('payment-gateways')
                        ->name('payment-gateways.')
                        ->middleware('admin.role:admin,manager')
                        ->controller(PaymentGatewayController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('sortable', 'sortable')->name('sortable');
                            Route::put('{paymentGateway}', 'update')->name('update');
                        });

                    Route::prefix('payout-methods')
                        ->name('payout-methods.')
                        ->middleware('admin.role:admin,manager')
                        ->controller(PayoutMethodController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::post('bulk-active', 'bulkActive')->name('bulk-active');
                            Route::post('bulk-inactive', 'bulkInactive')->name('bulk-inactive');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete');
                            Route::post('sortable', 'sortable')->name('sortable');
                            Route::post('create-method', 'create')->name('create');
                            Route::put('{payoutMethod}', 'update')->name('update');
                            Route::delete('{payoutMethod}', 'destroy')->name('destroy');
                        });

                    // Payouts
                    Route::prefix('payouts')
                        ->name('payouts.')
                        ->controller(PayoutController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('{payout}/details-modal', 'detailsModal')->name('details.modal')->withTrashed();
                            Route::post('bulk-return', 'bulkReturn')->name('bulk-return');
                            Route::post('bulk-approve', 'bulkApprove')->name('bulk-approve');
                            Route::post('bulk-completed', 'bulkCompleted')->name('bulk-completed');
                            Route::post('bulk-cancel', 'bulkCancel')->name('bulk-cancel');
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('admin.role:admin,manager');
                            Route::post('{payout}/update-status', 'updateStatus')->name('update-status')->withTrashed();
                            Route::post('{payout}/restore', 'restore')->name('restore')->middleware('admin.role:admin,manager')->withTrashed();
                            Route::delete('{payout}', 'destroy')->name('destroy')->middleware('admin.role:admin,manager')->withTrashed();

                            // Trash routes
                            Route::prefix('trash')->name('trash.')->group(function () {
                                Route::get('/', 'trash')->name('index');
                                Route::post('bulk-restore', 'bulkRestore')->name('bulk-restore')->middleware('admin.role:admin,manager');
                                Route::post('{id}/restore', 'restore')->name('restore')->middleware('admin.role:admin,manager');
                                Route::delete('{id}/permanently-delete', 'permanentlyDelete')->name('permanently-delete')->middleware('admin.role:admin');
                            });
                        });

                    // Transactions
                    Route::prefix('transactions')
                        ->name('transactions.')
                        ->controller(TransactionController::class)
                        ->group(function () {
                            Route::get('/', 'index')->name('index');
                            Route::get('details-modal/{transaction}', 'detailsModal')->name('details.modal')->withTrashed();
                            Route::get('{transaction}/payment-proof/view', 'paymentProof')->name('payment-proof')->withTrashed();
                            Route::post('{transaction}/paid', 'paid')->name('paid')->withTrashed();
                            Route::post('{transaction}/cancel', 'cancel')->name('cancel')->withTrashed();
                            Route::post('bulk-paid', 'bulkPaid')->name('bulk-paid');
                            Route::post('bulk-cancel', 'bulkCancel')->name('bulk-cancel');
                            Route::post('{transaction}/restore', 'restore')->name('restore')->middleware('admin.role:admin,manager')->withTrashed();
                            Route::delete('bulk-delete', 'bulkDelete')->name('bulk-delete')->middleware('admin.role:admin,manager');
                            Route::delete('{transaction}', 'destroy')->name('destroy')->middleware('admin.role:admin,manager')->withTrashed();

                            // Trash routes
                            Route::prefix('trash')->name('trash.')->group(function () {
                                Route::get('/', 'trash')->name('index');
                                Route::post('bulk-restore', 'bulkRestore')->name('bulk-restore')->middleware('admin.role:admin,manager');
                                Route::post('{id}/restore', 'restore')->name('restore')->middleware('admin.role:admin,manager');
                                Route::delete('{id}/permanently-delete', 'permanentlyDelete')->name('permanently-delete')->middleware('admin.role:admin');
                            });
                        });
                });

            // Account
            Route::prefix('account')
                ->name('account.')
                ->controller(AccountController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('details', 'updateDetails')->name('details');
                    Route::get('password', 'showPassword')->name('password');
                    Route::post('password', 'updatePassword')->name('password.update');
                    Route::get('security', 'show2FA')->name('security');
                    Route::post('security/2fa/enable', 'enable2FA')->name('2fa.enable');
                    Route::post('security/2fa/disable', 'disable2FA')->name('2fa.disable');
                });
        });
    });
});
