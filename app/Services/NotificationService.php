<?php

namespace App\Services;

use App\Jobs\SendEmailNotification;
use App\Models\{
    Badge,
    IdVerification,
    Purchase,
    Refund,
    RefundReply,
    Sale,
    User,
};
use App\Models\Notification\AdminNotification;
use App\Models\Product\{
    Product,
    ProductChangeLog,
    ProductComment,
    ProductCommentReply,
    ProductDiscount,
    ProductReview,
    ProductReviewReply,
    ProductReport
};
use App\Models\Financial\{
    Transaction,
    Payout,
    PayoutMethod
};
use App\Models\Premium\Premium;
use App\Models\Support\{Ticket, TicketReply};
use App\Notifications\Auth\{
    EmailUpdateNotification,
    EmailOtpNotification,
    LoginFailedNotification,
    LoginSuccessNotification,
    PasswordUpdateNotification
};
use App\Notifications\{
    BadgeChangeNotification,
    BecomeSellerNotification,
    FollowerNotification,
    IdStatusNotification,
    PremiumNotification,
    PaymentConfirmedNotification,
    ProductChangeLogNotification,
    ProductCommentNotification,
    ProductCommentReplyNotification,
    ProductDiscountNotification,
    ProductFavoriteNotification,
    ProductReportStatusNotification,
    ProductReviewNotification,
    ProductReviewReplyNotification,
    ProductSoldNotification,
    ProductSubmissionStatusNotification,
    ProductUpdateStatusNotification,
    PurchaseConfirmedNotification,
    RefundReplyNotification,
    RefundRequestNotification,
    RefundStatusNotification,
    SalesEarningsNotification,
    TicketNewNotification,
    TicketReplyNotification,
    TicketStatusNotification,
    TransactionCancelledNotification,
    PayoutMethodUpdateNotification,
    PayoutStatusNotification,
    PayoutSubmittedNotification,
    BirthdayWishNotification,
    CongratsNotification
};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Notification Service
 *
 * Centralized service for handling all application notifications.
 * Manages both database notifications and email notifications via jobs.
 *
 * @package App\Services
 */
class NotificationService
{
    /**
     * Send email verification notification.
     */
    public function sendEmailVerificationNotification(User $user, string $otp): void
    {
        if (!@settings('actions')->email_verification) {
            return;
        }

        $notification = new EmailOtpNotification($user, $otp, 'registration');
        $this->notify($user, $notification);
    }

    /**
     * Send password reset notification.
     */
    public function sendPasswordResetNotification(User $user, string $otp, string $token = ''): void
    {
        $notification = new EmailOtpNotification($user, $otp, 'password_reset');
        $this->notify($user, $notification);
    }

    /**
     * Send email change OTP notification.
     */
    public function sendEmailChangeNotification(User $user, string $email, string $otp): void
    {
        $notification = new EmailOtpNotification($user, $otp, 'email_change');
        $this->notify($user, $notification, null, $email);
    }

    /**
     * Send email update notification (informative).
     */
    public function sendEmailUpdateNotification(User $user): void
    {
        $notification = new EmailUpdateNotification($user);
        $this->notify($user, $notification);
    }

    /**
     * Send password update notification.
     */
    public function sendPasswordUpdateNotification(User $user): void
    {
        $notification = new PasswordUpdateNotification($user);
        $this->notify($user, $notification);
    }

    /**
     * Send login success notification.
     */
    public function sendLoginSuccessNotification(User $user): void
    {
        $notification = new LoginSuccessNotification($user);
        $this->notify($user, $notification);
    }

    /**
     * Send login failed notification.
     */
    public function sendLoginFailedNotification(User $user): void
    {
        $notification = new LoginFailedNotification($user);
        $this->notify($user, $notification);
    }

    /**
     * Send payment confirmed notification.
     */
    public function sendPaymentConfirmedNotification(User $user, Transaction $transaction): void
    {
        $notification = new PaymentConfirmedNotification($transaction, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send purchase confirmed notification.
     */
    public function sendPurchaseConfirmedNotification(User $user, Purchase $purchase): void
    {
        $notification = new PurchaseConfirmedNotification($purchase, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send product sold notification to seller.
     */
    public function sendProductSoldNotification(User $seller, Product $product, User $buyer): void
    {
        $notification = new ProductSoldNotification($product, $buyer, $seller);
        $emailData = $notification->getEmailData();
        $this->notify($seller, $notification, $emailData);
    }

    /**
     * Send sales earnings notification to seller.
     */
    public function sendSalesEarningsNotification(User $seller, Sale $earnings): void
    {
        $notification = new SalesEarningsNotification($earnings, $seller);
        $emailData = $notification->getEmailData();
        $this->notify($seller, $notification, $emailData);
    }

    /**
     * Send product comment notification to seller.
     */
    public function sendProductCommentNotification(Product $product, ProductComment $comment, ProductCommentReply $commentReply, User $commenter): void
    {
        $seller = $product->seller;
        if (!$seller) {
            return;
        }

        if ($seller->id !== $commenter->id) {
            $notification = new ProductCommentNotification($product, $comment, $commentReply, $commenter, $seller);
            $emailData = $notification->getEmailData();
            $this->notify($seller, $notification, $emailData);
        }
    }

    /**
     * Send product comment reply notification.
     */
    public function sendProductCommentReplyNotification(Product $product, ProductComment $comment, ProductCommentReply $reply, User $replier): void
    {
        $commenter = $comment->user;
        $seller = $product->seller;

        // Notify the original commenter only if they are not the replier
        if ($commenter && $commenter->id !== $replier->id) {
            $notification = new ProductCommentReplyNotification($product, $comment, $reply, $replier, $commenter);
            $emailData = $notification->getEmailData();
            $this->notify($commenter, $notification, $emailData);
        }

        // Always notify the product seller
        if ($seller && $seller->id !== $replier->id) {
            $notification = new ProductCommentReplyNotification($product, $comment, $reply, $replier, $seller);
            $emailData = $notification->getEmailData();
            $this->notify($seller, $notification, $emailData);
        }

        // Also notify other users who have replied to this comment
        $previousRepliers = $comment->replies()
            ->where('user_id', '!=', $replier->id)
            ->where('user_id', '!=', $commenter?->id)
            ->where('user_id', '!=', $seller?->id)
            ->pluck('user_id')
            ->unique();

        $users = User::whereIn('id', $previousRepliers)->get();
        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $notification = new ProductCommentReplyNotification($product, $comment, $reply, $replier, $user);
            $emailData = $notification->getEmailData();
            $this->notify($user, $notification, $emailData);
        }
    }

    /**
     * Send product review notification to seller.
     */
    public function sendProductReviewNotification(User $seller, ProductReview $review, Product $product): void
    {
        $notification = new ProductReviewNotification($review, $product, $seller);
        $emailData = $notification->getEmailData();
        $this->notify($seller, $notification, $emailData);
    }

    /**
     * Send product review reply notification.
     */
    public function sendProductReviewReplyNotification(User $user, ProductReview $review, ProductReviewReply $reply, Product $product, User $seller): void
    {
        $notification = new ProductReviewReplyNotification($review, $reply, $product, $seller, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send payout submitted notification.
     */
    public function sendPayoutSubmittedNotification(Payout $payout): void
    {
        $seller = $payout->seller;
        if (!$seller) {
            return;
        }

        $notification = new PayoutSubmittedNotification($payout, $seller);
        $emailData = $notification->getEmailData();
        $this->notify($seller, $notification, $emailData);
    }

    /**
     * Send payout status notification.
     */
    public function sendPayoutStatusNotification(Payout $payout): void
    {
        $seller = $payout->seller;
        if (!$seller) {
            return;
        }

        $notification = new PayoutStatusNotification($payout, $seller);
        $emailData = $notification->getEmailData();
        $this->notify($seller, $notification, $emailData);
    }

    /**
     * Send payout method update notification.
     */
    public function sendPayoutMethodUpdateNotification(User $user, PayoutMethod $payoutMethod): void
    {
        $notification = new PayoutMethodUpdateNotification($payoutMethod, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send product submission status notification.
     */
    public function sendProductSubmissionStatusNotification(Product $product, string $status, string $reason = null): void
    {
        try {
            $seller = $product->seller;

            if (!$seller) {
                return;
            }

            // Create and send notification to the product SELLER
            $notification = new ProductSubmissionStatusNotification($product, $seller, $status, $reason);
            $emailData = $notification->getEmailData();
            $this->notify($seller, $notification, $emailData);

            // If the product is approved, notify followers also
            if ($status === 'approved') {
                $followers = User::whereHas('followings', function ($query) use ($seller) {
                    $query->where('following_id', $seller->id);
                })
                    ->active()
                    ->get();

                if ($followers->isNotEmpty()) {
                    $followers->chunk(50)->each(function ($followerChunk) use ($product) {
                        foreach ($followerChunk as $follower) {
                            $notification = new ProductSubmissionStatusNotification($product, $follower, 'approved');
                            $emailData = $notification->getEmailData();
                            $this->notify($follower, $notification, $emailData);
                        }
                    });
                }
            }
        } catch (\Throwable) {
            // Silent error handling
        }
    }

    /**
     * Send product update status notification.
     */
    public function sendProductUpdateStatusNotification(Product $product, string $status, string $reason = null): void
    {
        try {
            $seller = $product->seller;

            if (!$seller) {
                return;
            }

            // Notify the Seller for 'approved' or 'rejected' statuses
            if (in_array($status, ['approved', 'rejected'], true)) {
                $notification = new ProductUpdateStatusNotification($product, $seller, $status, $reason);
                $emailData = $notification->getEmailData();
                $this->notify($seller, $notification, $emailData);
            }

            // Notify buyers, followers, and users who favorited for 'approved' status
            if ($status === 'approved') {
                $users = User::where('id', '!=', $seller->id)
                    ->active()
                    ->where(function ($q) use ($product, $seller) {
                        $q->whereHas('purchases', function ($subQ) use ($product) {
                            $subQ->where('product_id', $product->id)->active();
                        })
                            ->orWhereHas('followings', function ($subQ) use ($seller) {
                                $subQ->where('following_id', $seller->id);
                            })
                            ->orWhereHas('favorites', function ($subQ) use ($product) {
                                $subQ->where('product_id', $product->id);
                            });
                    })
                    ->with([
                        'purchases' => fn($q) => $q->where('product_id', $product->id),
                        'followings' => fn($q) => $q->where('following_id', $seller->id),
                        'favorites' => fn($q) => $q->where('product_id', $product->id)
                    ])
                    ->get();

                // Process users with relationship priority
                $users->each(function ($user) use ($product) {
                    try {
                        // Determine relationship: buyer > follower > favorited
                        $relationship = 'favorited';

                        if ($user->purchases->isNotEmpty()) {
                            $relationship = 'buyer';
                        } elseif ($user->followings->isNotEmpty()) {
                            $relationship = 'follower';
                        }

                        $notification = new ProductUpdateStatusNotification($product, $user, 'approved', null, $relationship);
                        $emailData = $notification->getEmailData();
                        $this->notify($user, $notification, $emailData);
                    } catch (\Throwable) {
                        // Silent error - continue with other notifications
                    }
                });
            }
        } catch (\Throwable) {
            // Silent error handling
        }
    }

    /**
     * Send product discount notification.
     */
    public function sendProductDiscountNotification(Product $product, ProductDiscount $discount): void
    {
        try {
            // Build a single query with conditional relationships
            $users = User::where('id', '!=', $product->Seller_id)
                ->active()
                ->where(function ($query) use ($product) {
                    $query->whereHas('purchases', function ($q) use ($product) {
                        $q->where('product_id', $product->id)->active();
                    })
                        ->orWhereHas('followings', function ($q) use ($product) {
                            $q->where('following_id', $product->Seller_id);
                        })
                        ->orWhereHas('favorites', function ($q) use ($product) {
                            $q->where('product_id', $product->id);
                        });
                })
                ->with([
                    'purchases' => fn($q) => $q->where('product_id', $product->id),
                    'followings' => fn($q) => $q->where('following_id', $product->Seller_id),
                    'favorites' => fn($q) => $q->where('product_id', $product->id)
                ])
                ->get();

            if ($users->isEmpty()) {
                return;
            }

            // Process users with priority
            $users->each(function ($user) use ($product, $discount) {
                try {
                    // Priority: buyer > follower > favorited
                    $relationship = 'favorited';

                    if ($user->purchases->isNotEmpty()) {
                        $relationship = 'buyer';
                    } elseif ($user->followings->isNotEmpty()) {
                        $relationship = 'follower';
                    }

                    $notification = new ProductDiscountNotification($product, $discount, $user, $relationship);
                    $emailData = $notification->getEmailData();

                    $this->notify($user, $notification, $emailData);
                } catch (\Throwable) {
                    // Silent error - continue with other notifications
                }
            });
        } catch (\Throwable) {
            // Silent error - continue with other notifications
        }
    }

    /**
     * Send product changelog notification to buyers.
     */
    public function sendProductChangeLogNotification(Product $product, ProductChangeLog $changelog): void
    {
        $purchasedUserIds = Purchase::where('product_id', $product->id)
            ->active()
            ->pluck('user_id')
            ->unique();

        $users = User::whereIn('id', $purchasedUserIds)->get();
        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $notification = new ProductChangeLogNotification($user, $product, $changelog);
            $emailData = $notification->getEmailData();
            $this->notify($user, $notification, $emailData);
        }
    }

    /**
     * Send new ticket notification.
     */
    public function sendTicketNewNotification(Ticket $ticket): void
    {
        $user = $ticket->user;
        if (!$user) {
            return;
        }

        $notification = new TicketNewNotification($ticket, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send ticket reply notification.
     */
    public function sendTicketReplyNotification(Ticket $ticket, TicketReply $ticketReply): void
    {
        $user = $ticket->user;
        if (!$user) {
            return;
        }

        if ($ticket->user_id !== $ticketReply->user_id) {
            $notification = new TicketReplyNotification($ticket, $ticketReply, $user);
            $emailData = $notification->getEmailData();
            $this->notify($user, $notification, $emailData);
        }
    }

    /**
     * Send ticket status notification.
     */
    public function sendTicketStatusNotification(Ticket $ticket): void
    {
        $user = $ticket->user;
        if (!$user) {
            return;
        }

        $notification = new TicketStatusNotification($ticket, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send refund request notification to seller.
     */
    public function sendRefundRequestNotification(Refund $refund, RefundReply $refundReply): void
    {
        $seller = $refund->seller;
        if (!$seller) {
            return;
        }

        $notification = new RefundRequestNotification($refund, $refundReply, $seller);
        $emailData = $notification->getEmailData();
        $this->notify($seller, $notification, $emailData);
    }

    /**
     * Send refund reply notification.
     */
    public function sendRefundReplyNotification(Refund $refund, RefundReply $refundReply): void
    {
        $user = $refundReply->user_id === $refund->user_id ? $refund->seller : $refund->user;
        if (!$user) {
            return;
        }

        $notification = new RefundReplyNotification($refund, $refundReply, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send refund status notification.
     */
    public function sendRefundStatusNotification(Refund $refund, RefundReply $refundReply, string $status): void
    {
        $user = $refund->user;
        if (!$user) {
            return;
        }

        $notification = new RefundStatusNotification($refund, $refundReply, $status, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send follower notification.
     */
    public function sendFollowerNotification(User $following, User $follower): void
    {
        $notification = new FollowerNotification($following, $follower);
        $emailData = $notification->getEmailData();
        $this->notify($following, $notification, $emailData);
    }

    /**
     * Send product favorite notification to seller.
     */
    public function sendProductFavoriteNotification(Product $product, User $user): void
    {
        $seller = $product->seller;

        if (!$seller) {
            return;
        }

        if ($seller->id !== $user->id) {
            $notification = new ProductFavoriteNotification($product, $user, $seller);
            $emailData = $notification->getEmailData();
            $this->notify($seller, $notification, $emailData);
        }
    }

    /**
     * Send badge change notification.
     */
    public function sendBadgeChangeNotification(User $user, Badge $badge, string $changeType = 'new'): void
    {
        $notification = new BadgeChangeNotification($badge, $user, $changeType);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send ID verification status notification.
     */
    public function sendIdStatusNotification(IdVerification $idVerification): void
    {
        $user = $idVerification->user;
        if (!$user) {
            return;
        }

        $notification = new IdStatusNotification($idVerification, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send become seller notification.
     */
    public function sendBecomeSellerNotification(User $user): void
    {
        $notification = new BecomeSellerNotification($user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send transaction cancelled notification.
     */
    public function sendTransactionCancelledNotification(Transaction $transaction): void
    {
        $user = $transaction->user;
        if (!$user) {
            return;
        }

        $notification = new TransactionCancelledNotification($transaction, $user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send premium membership notification.
     */
    public function sendPremiumNotification(Premium $premium, string $status): void
    {
        try {
            $user = $premium->user;

            if (!$user) {
                return;
            }

            $notification = new PremiumNotification($premium, $status);
            $emailData = $notification->getEmailData();
            $this->notify($user, $notification, $emailData);
        } catch (\Throwable) {
            // Silent error
        }
    }

    /**
     * Send product report status notification.
     */
    public function sendProductReportStatusNotification(Product $product, ProductReport $report, string $status): void
    {
        $seller = $product->seller;
        $reporter = $report->user;

        // Define which statuses should notify which recipients
        $sellerStatuses = ['deleted', 'restricted', 'un_restricted'];
        $reporterStatuses = ['deleted', 'restricted', 'resolved', 'dismissed'];

        if ($seller && in_array($status, $sellerStatuses, true)) {
            $notification = new ProductReportStatusNotification($product, $report, $seller, $status);
            $emailData = $notification->getEmailData();
            if ($emailData && $emailData['template']) {
                $this->notify($seller, $notification, $emailData);
            }
        }

        if ($reporter && in_array($status, $reporterStatuses, true)) {
            $notification = new ProductReportStatusNotification($product, $report, $reporter, $status);
            $emailData = $notification->getEmailData();
            if ($emailData && $emailData['template']) {
                $this->notify($reporter, $notification, $emailData);
            }
        }
    }

    /**
     * Send birthday wish notification.
     */
    public function sendBirthdayWishNotification(User $user): void
    {
        $notification = new BirthdayWishNotification($user);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send congratulations notification to top seller & top sold products.
     */
    public function sendCongratsNotification(User $user, ?Product $product = null, string $templateType): void
    {
        $notification = new CongratsNotification($user, $templateType, $product);
        $emailData = $notification->getEmailData();
        $this->notify($user, $notification, $emailData);
    }

    /**
     * Send admin notification.
     *
     * Creates a database notification for admin panel to alert administrators
     * about important events or actions requiring attention.
     */
    public function sendAdminNotification(string $title, string $image, ?string $link = null): void
    {
        $notification = new AdminNotification();
        $notification->title = $title;
        $notification->image = $image;
        $notification->link = $link;
        $notification->save();
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(User $user, string $notificationId): ?bool
    {
        return $user->notifications()->where('id', $notificationId)->first()?->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Get paginated notifications for a user.
     */
    public function getNotifications(User $user, int $limit = 10): LengthAwarePaginator
    {
        return $user->notifications()->latest()->paginate($limit);
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecentNotifications(User $user, int $limit = 10): Collection
    {
        return $user->notifications()->latest()->limit($limit)->get();
    }

    /**
     * Send notification with optional email.
     *
     * Sends real-time notification (database + broadcast) and optionally
     * dispatches email notification job if conditions are met.
     */
    public function notify(object $notifiable, object $notification, ?array $emailData = null, ?string $targetEmail = null): void
    {
        try {
            // If a target email is provided and it's a synchronous notification (no extra emailData)
            // we use Laravel's on-demand routing to ensure it goes to the correct address.
            if ($targetEmail && is_null($emailData)) {
                \Illuminate\Support\Facades\Notification::route('mail', $targetEmail)->notify($notification);
            } else {
                // Send real-time notification (database + broadcast, and sync email if via() includes it)
                $notifiable->notify($notification);
            }

            $preference = $notification->getNotificationPreference() ?? null;

            // Handle email separately via jobs if data provided
            if ($emailData && $this->shouldSendEmail($notifiable, $preference, $targetEmail)) {
                SendEmailNotification::dispatch($notifiable, $emailData['template'], $emailData['shortcodes'], $preference, $targetEmail);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Notification delivery failed', [
                'notifiable_id' => $notifiable->id ?? null,
                'notification' => get_class($notification),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if email should be sent to the notifiable entity.
     *
     * Validates notification preferences, email validity, user status,
     * and filters out test email domains in production.
     */
    protected function shouldSendEmail(object $notifiable, ?string $preference, ?string $targetEmail = null): bool
    {
        $email = $targetEmail ?? $notifiable->email;

        // Check notification preferences
        if (method_exists($notifiable, 'wantsNotification') && !$notifiable->wantsNotification('email', $preference)) {
            return false;
        }

        // Check if email exists and is valid
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check if user is suspended
        if (method_exists($notifiable, 'isSuspended') && $notifiable->isSuspended()) {
            return false;
        }

        // Check if email is verified
        if (method_exists($notifiable, 'hasVerifiedEmail') && !$notifiable->hasVerifiedEmail()) {
            return false;
        }

        // Check test email domains in production
        if (app()->environment('production')) {
            $testDomains = [
                'example.com',
                'example.org',
                'example.net',
                'test.com',
                'localhost',
                'mailinator.com',
                'guerrillamail.com',
                'mailtrap.io'
            ];

            $domain = strtolower(substr(strrchr($notifiable->email, '@'), 1));
            if (in_array($domain, $testDomains, true)) {
                return false;
            }
        }

        return true;
    }
}
