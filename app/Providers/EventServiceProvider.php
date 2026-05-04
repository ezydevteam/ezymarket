<?php

namespace App\Providers;

use App\Events\IdVerificationPending;
use App\Events\ProductResubmitted;
use App\Events\ProductSubmitted;
use App\Events\ProductUpdated;
use App\Events\Registered;
use App\Events\SaleCancelled;
use App\Events\SaleCreated;
use App\Events\SaleRefunded;
use App\Events\TicketCreated;
use App\Events\TicketReplyCreated;
use App\Events\TransactionPaid;
use App\Events\TransactionPending;
use App\Events\PayoutSubmitted;
use App\Events\UserSuspended;
use App\Events\UserBecameSeller;
use App\Events\UserIdStatus;
use App\Listeners\ProcessCancelledSale;
use App\Listeners\ProcessNewSale;
use App\Listeners\ProcessPaidTransaction;
use App\Listeners\ProcessPendingIdVerification;
use App\Listeners\ProcessPendingTransaction;
use App\Listeners\ProcessReferralRegistration;
use App\Listeners\ProcessRefundedSale;
use App\Listeners\ProcessResubmittedProduct;
use App\Listeners\ProcessSubmittedProduct;
use App\Listeners\ProcessSubmittedPayout;
use App\Listeners\ProcessTicketCreation;
use App\Listeners\ProcessTicketReplyCreation;
use App\Listeners\ProcessUpdatedProduct;
use App\Listeners\SendBecameSellerNotification;
use App\Listeners\SendEmailVerificationNotification;
use App\Listeners\SendIdStatusNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider
 *
 * Manages event-to-listener mappings for the EasyMarket application.
 * Handles real-time event processing for marketplace activities.
 *
 * Event Categories:
 * - User Events: Registration, verification
 * - Product Events: Submission, resubmission, updates
 * - Transaction Events: Pending, paid transactions
 * - Sale Events: Created, cancelled, refunded sales
 * - Support Events: Tickets, ticket replies
 * - Financial Events: Withdrawal submissions
 *
 * Features:
 * - Automatic listener registration
 * - Event discovery for organized event handling
 * - Queue-based async processing support
 * - Multiple listeners per event support
 *
 * Laravel 11 Features:
 * - Auto-discovery of events and listeners
 * - Attribute-based event listening
 * - Simplified event registration
 *
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Event-to-listener mappings for the application
     *
     * Maps events to their corresponding listeners. Each event can have
     * multiple listeners that are executed in the order defined.
     *
     * Listener Execution:
     * - Synchronous by default
     * - Can be queued using ShouldQueue interface
     * - Failed listeners logged to laravel.log
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // User registration and verification
        Registered::class => [
            SendEmailVerificationNotification::class,
            ProcessReferralRegistration::class,
        ],

        // User status changes
        UserIdStatus::class => [
            SendIdStatusNotification::class,
        ],
        UserBecameSeller::class => [
            SendBecameSellerNotification::class,
        ],
        UserSuspended::class => [
            // Add listeners here as needed (e.g., SendUserSuspendedEmail)
        ],

        // Identity verification
        IdVerificationPending::class => [
            ProcessPendingIdVerification::class,
        ],

        // Support ticket system
        TicketCreated::class => [
            ProcessTicketCreation::class,
        ],
        TicketReplyCreated::class => [
            ProcessTicketReplyCreation::class,
        ],

        // Financial payouts
        PayoutSubmitted::class => [
            ProcessSubmittedPayout::class,
        ],

        // Product lifecycle events
        ProductSubmitted::class => [
            ProcessSubmittedProduct::class,
        ],
        ProductResubmitted::class => [
            ProcessResubmittedProduct::class,
        ],
        ProductUpdated::class => [
            ProcessUpdatedProduct::class,
        ],

        // Transaction processing
        TransactionPending::class => [
            ProcessPendingTransaction::class,
        ],
        TransactionPaid::class => [
            ProcessPaidTransaction::class,
        ],

        // Sale management
        SaleCreated::class => [
            ProcessNewSale::class,
        ],
        SaleCancelled::class => [
            ProcessCancelledSale::class,
        ],
        SaleRefunded::class => [
            ProcessRefundedSale::class,
        ],
    ];

    /**
     * Register and bootstrap application events
     *
     * Additional event registration, closures, or custom logic can be
     * added here. Most events are registered via the $listen property.
     *
     * Laravel 11 automatically calls parent::boot() which registers
     * all events from the $listen property.
     *
     * @return void
     */
    public function boot(): void
    {
        // Parent boot registers all events from $listen property
        // Add custom event logic below if needed
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}


















