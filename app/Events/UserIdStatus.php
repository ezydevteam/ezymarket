<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a user's ID verification status changes.
 *
 * This event is dispatched when a user's ID verification status changes to either
 * VERIFIED (approved) or REJECTED. Listeners can handle sending notifications,
 * unlocking/locking features, etc.
 */
class UserIdStatus
{
    use SerializesModels;

    /**
     * The user whose ID verification status changed.
     *
     * @var \App\Models\User
     */
    public User $user;

    /**
     * The ID verification record (optional).
     *
     * @var int|null
     */
    public ?int $verificationId;

    /**
     * The admin who processed the verification (optional).
     *
     * @var int|null
     */
    public ?int $verifiedBy;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user The user whose ID verification status changed
     * @param int|null $verificationId The ID of the verification record
     * @param int|null $verifiedBy The ID of the admin who processed the verification
     */
    public function __construct(User $user, ?int $verificationId = null, ?int $verifiedBy = null)
    {
        $this->user = $user;
        $this->verificationId = $verificationId;
        $this->verifiedBy = $verifiedBy;
    }
}
