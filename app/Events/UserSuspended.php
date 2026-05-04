<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a user is suspended.
 *
 * This event is dispatched when a user's status changes to SUSPENDED.
 * Listeners can handle sending notifications, logging, revoking access, etc.
 */
class UserSuspended
{
    use SerializesModels;

    /**
     * The suspended user.
     *
     * @var \App\Models\User
     */
    public User $user;

    /**
     * The reason for suspension (optional).
     *
     * @var string|null
     */
    public ?string $reason;

    /**
     * The admin who performed the suspension (optional).
     *
     * @var int|null
     */
    public ?int $suspendedBy;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user The user being suspended
     * @param string|null $reason The reason for the suspension
     * @param int|null $suspendedBy The ID of the admin who suspended the user
     */
    public function __construct(User $user, ?string $reason = null, ?int $suspendedBy = null)
    {
        $this->user = $user;
        $this->reason = $reason;
        $this->suspendedBy = $suspendedBy;
    }
}
