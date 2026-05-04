<?php

namespace App\Events;

use App\Models\IdVerification;
use Illuminate\Queue\SerializesModels;

/**
 * ID Verification Pending Event
 *
 * Fired when a user submits identity verification documents
 * that require administrator review.
 *
 * Event Flow:
 * 1. User uploads ID documents
 * 2. Event fired
 * 3. Listener sends notification to admins
 *
 * Listeners:
 * - ProcessPendingIdVerification: Notifies admins for review
 *
 * Usage:
 * ```php
 * event(new IdVerificationPending($idVerification));
 * ```
 *
 * @package App\Events
 */
class IdVerificationPending
{
    use SerializesModels;

    /**
     * Create a new event instance
     *
     * Uses PHP 8.3 constructor property promotion for cleaner code.
     *
     * @param IdVerification $idVerification The ID verification instance
     */
    public function __construct(
        public IdVerification $idVerification
    ) {
    }
}




















