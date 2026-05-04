<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class ArchivableObserver
 *
 * Centralized observer to handle the archival metadata (who deleted the record).
 * Supports both User and Admin actors via polymorphic tracking.
 */
class ArchivableObserver
{
    /**
     * Handle the "deleting" event.
     * Records the identity of the actor before the record is soft-deleted.
     *
     * @param Model $model
     * @return void
     */
    public function deleting(Model $model): void
    {
        // Only proceed if the model is being soft-deleted (not a force delete)
        if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
            
            $actor = $this->resolveActor();

            // We explicitly set these fields to either the actor or null.
            // This prevents old metadata from persisting on subsequent deletions.
            $model->deleted_by_id = $actor ? $actor->id : null;
            $model->deleted_by_type = $actor ? get_class($actor) : null;
            
            // Note: We MUST call $model->save() here. 
            // Eloquent's soft-delete process (runSoftDelete) performs a direct 
            // query update for the deleted_at column and excludes other dirty attributes.
            $model->save();
        }
    }

    /**
     * Handle the "restoring" event.
     * Clears archival metadata and records restoration timestamp if by admin.
     *
     * @param Model $model
     * @return void
     */
    public function restoring(Model $model): void
    {
        $model->deleted_by_id = null;
        $model->deleted_by_type = null;

        // Record restoration timestamp only if initiated by an Admin
        $actor = $this->resolveActor();
        if ($actor && $actor instanceof \App\Models\Admin) {
            $model->restored_at = now();
        }
    }

    /**
     * Resolve the currently authenticated actor (Admin or User).
     *
     * @return Model|null
     */
    protected function resolveActor(): ?Model
    {
        // Detect current request context (Admin Panel vs Front/User Panel)
        $isAdminRequest = request()->is('admin*');

        // Prioritize the guard that matches the current request context
        $primaryGuard = $isAdminRequest ? 'admin' : 'web';
        $secondaryGuard = $isAdminRequest ? 'web' : 'admin';

        // Check the primary guard first
        if (Auth::guard($primaryGuard)->check()) {
            return Auth::guard($primaryGuard)->user();
        }

        // Fallback to the other guard if the primary is not authenticated
        if (Auth::guard($secondaryGuard)->check()) {
            return Auth::guard($secondaryGuard)->user();
        }

        return null;
    }
}
