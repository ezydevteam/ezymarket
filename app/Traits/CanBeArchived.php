<?php

namespace App\Traits;

use App\Models\{User, Admin};
use App\Observers\ArchivableObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\{Builder, SoftDeletes, Relations\MorphTo};

/**
 * Trait CanBeArchived
 *
 * Provides standardized soft-delete and archival tracking functionality.
 * Automatically records the identity of the actor (User/Admin) who performs the deletion.
 */
trait CanBeArchived
{
    use SoftDeletes;

    /**
     * Boot the trait and register the global archivable observer.
     */
    protected static function bootCanBeArchived(): void
    {
        static::observe(ArchivableObserver::class);
    }

    /**
     * Get the actor who archived/deleted this record.
     */
    public function archivedBy(): MorphTo
    {
        return $this->morphTo('archivedBy', 'deleted_by_type', 'deleted_by_id')->withTrashed();
    }

    /**
     * Check if the record was archived by a specific user.
     *
     * @param int $userId
     * @return bool
     */
    public function isArchivedBy(int $userId): bool
    {
        return (int) $this->deleted_by_id === $userId && $this->deleted_by_type === User::class;
    }

    /**
     * Check if the record was archived by an administrator.
     */
    public function isArchivedByAdmin(): bool
    {
        return $this->deleted_by_type === Admin::class;
    }

    /**
     * Scope query to active records and user-deleted records.
     * Use case: Main Dashboard / Module Index.
     */
    public function scopeWithUserTrashed(Builder $query): Builder
    {
        return $query->withTrashed()->where(function ($q) {
            $q->whereNull('deleted_at')
                ->orWhere('deleted_by_type', User::class)
                ->orWhereNull('deleted_by_type');
        });
    }

    /**
     * Scope query to only include records deleted by administrators.
     * Use case: Trash Directory.
     */
    public function scopeOnlyAdminTrashed(Builder $query): Builder
    {
        return $query->onlyTrashed()->where('deleted_by_type', Admin::class);
    }

    /**
     * Explicitly transfer a user-archived record to the administrative trash.
     * Marks the record as deleted by the currently authenticated administrator.
     */
    public function moveToAdminTrash(): void
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();

            $this->deleted_by_id = $admin->id;
            $this->deleted_by_type = get_class($admin);
            $this->save();
        }
    }
}
