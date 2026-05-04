<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;

/**
 * User Observer
 *
 * Handles model lifecycle events for User model.
 * Centralizes logic that should run on user creation, updates, deletion, etc.
 */
class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * Runs after a new user is created.
     */
    public function created(User $user): void
    {
        // Add country badge if address exists
        if (!empty($user->address['country'])) {
            $user->addCountryBadge($user->address['country']);
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * Runs after a user is updated.
     * Use for external side effects, not core business logic.
     */
    public function updated(User $user): void
    {
        // Address changed (country badge)
        if ($user->isDirty('address')) {
            $country = !empty($user->address['country']) ? $user->address['country'] : null;
            if ($country) {
                $user->addCountryBadge($country);
            }
        }
    }

    /**
     * Handle the User "deleting" event.
     *
     * Runs before a user is deleted (soft or force delete).
     */
    public function deleting(User $user): void
    {
        // Only cleanup resources on force delete
        // Soft delete should preserve data for potential restore
        if ($user->isForceDeleting()) {
            $user->deleteResources();
        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * Runs after a user is deleted.
     */
    public function deleted(User $user): void
    {
        // Post-deletion logic can go here
    }

    /**
     * Handle the User "restored" event.
     *
     * Runs after a user is restored from soft delete.
     */
    public function restored(User $user): void
    {
        // Logic to run when user is restored
        // Could re-enable certain features, send notification, etc.
    }

    /**
     * Handle the User "force deleted" event.
     *
     * Runs after a user is permanently deleted.
     */
    public function forceDeleted(User $user): void
    {
        // Final cleanup logic after permanent deletion
    }
}
