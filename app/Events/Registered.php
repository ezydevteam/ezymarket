<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * User Registered Event
 *
 * This event is fired when a new user successfully completes registration.
 *
 * Event Flow:
 * 1. User completes registration form
 * 2. Event is fired with the new User model
 * 3. Listeners can send welcome emails
 * 4. Additional setup tasks can be triggered
 *
 * @package App\Events
 * @see \App\Models\User
 */
class Registered
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user The newly registered user
     */
    public function __construct(
        public User $user
    ) {
    }
}

















