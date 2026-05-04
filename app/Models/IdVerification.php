<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\{BadgeAlias, IdDocumentType, IdVerificationStatus};
use App\Events\UserIdStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ID Verification Model
 *
 * Manages user identity verification requests and documents.
 * Handles document uploads, status changes, and badge awards.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property IdDocumentType $document_type
 * @property string $document_number
 * @property object $documents
 * @property IdVerificationStatus $status
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read User $user
 */
class IdVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'documents',
        'status',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_type' => IdDocumentType::class,
            'status' => IdVerificationStatus::class,
            'documents' => 'object',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Bootstrap the model and its events.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (IdVerification $idVerification): void {
            $driver = storageDriver();
            $disk = $driver ? $driver->disk : 'local';

            foreach ($idVerification->documents as $document) {
                if ($document) {
                    removeFileFromStorage($document, $disk);
                }
            }
        });

        static::updated(function (IdVerification $idVerification): void {
            if (!$idVerification->isDirty('status')) {
                return;
            }

            // ID Verification was approved
            if ($idVerification->status === IdVerificationStatus::APPROVED) {
                self::handleApproval($idVerification);
            }

            // ID Verification was rejected
            if ($idVerification->status === IdVerificationStatus::REJECTED) {
                event(new UserIdStatus($idVerification->user, $idVerification->id));
            }
        });
    }

    /**
     * Scope a query to only include pending verifications.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', IdVerificationStatus::PENDING);
    }

    /**
     * Check if the verification is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === IdVerificationStatus::PENDING;
    }

    /**
     * Scope a query to only include approved verifications.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', IdVerificationStatus::APPROVED);
    }

    /**
     * Check if the verification is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === IdVerificationStatus::APPROVED;
    }

    /**
     * Scope a query to only include rejected verifications.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', IdVerificationStatus::REJECTED);
    }

    /**
     * Check if the verification is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === IdVerificationStatus::REJECTED;
    }

    /**
     * Check if the document type is National ID.
     *
     * @return bool
     */
    public function isNationalIdDocument(): bool
    {
        return $this->document_type === IdDocumentType::NATIONAL_ID;
    }

    /**
     * Check if the document type is Passport.
     *
     * @return bool
     */
    public function isPassportDocument(): bool
    {
        return $this->document_type === IdDocumentType::PASSPORT;
    }

    /**
     * Get the status label.
     *
     * @return Attribute
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->label(),
        );
    }

    /**
     * Get the status badge class for UI display.
     *
     * @return Attribute
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badgeClass(),
        );
    }

    /**
     * Get the status icon.
     *
     * @return Attribute
     */
    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->icon(),
        );
    }

    /**
     * Get all document type options.
     *
     * @return array<string, string>
     */
    public static function getDocumentTypeOptions(): array
    {
        return IdDocumentType::options();
    }

    /**
     * Get the document type label.
     *
     * @return string
     */
    public function getDocumentType(): string
    {
        return $this->document_type->label();
    }

    /**
     * Get all status options.
     *
     * @return array<int, string>
     */
    public static function getStatusOptions(): array
    {
        return IdVerificationStatus::options();
    }

    /**
     * Get the storage disk for ID verification documents.
     *
     * @return string
     */
    public function getStorageDisk(): string
    {
        $driver = storageDriver();
        return $driver ? $driver->alias : 'local';
    }

    /**
     * Get the user that owns the ID verification.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the number of rejections for the user.
     *
     * @return int
     */
    public function getUserRejectionsCount(): int
    {
        $query = static::where('user_id', $this->user_id)
            ->where('status', IdVerificationStatus::REJECTED);

        // If current record is rejected, exclude it from count
        if ($this->isRejected() && $this->id) {
            $query->where('id', '!=', $this->id);
        }

        return $query->count();
    }

    /**
     * Get the last rejection date for the user.
     * If current verification is rejected, returns the previous rejection's date.
     * Otherwise, returns the most recent rejection date.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getLastRejectionDate(): ?Carbon
    {
        $query = static::where('user_id', $this->user_id)
            ->where('status', IdVerificationStatus::REJECTED);

        // If current record is rejected, exclude it and get previous rejection
        if ($this->isRejected() && $this->id) {
            $query->where('id', '!=', $this->id);
        }

        $lastRejection = $query->orderByDesc('updated_at')->first();

        return $lastRejection?->updated_at;
    }

    /**
     * Get the last rejection reason for the user.
     * If current verification is rejected, returns the previous rejection's reason.
     * Otherwise, returns the most recent rejection reason.
     *
     * @return string|null
     */
    public function getLastRejectionReason(): ?string
    {
        $query = static::where('user_id', $this->user_id)
            ->where('status', IdVerificationStatus::REJECTED);

        // If current record is rejected, exclude it and get previous rejection
        if ($this->isRejected() && $this->id) {
            $query->where('id', '!=', $this->id);
        }

        $lastRejection = $query->orderByDesc('updated_at')->first();

        return $lastRejection?->rejection_reason;
    }

    /**
     * Handle ID verification approval logic.
     *
     * @param IdVerification $idVerification
     * @return void
     */
    private static function handleApproval(IdVerification $idVerification): void
    {
        $user = $idVerification->user;

        if (!$user) {
            return;
        }

        $verifiedBadge = Badge::where('alias', BadgeAlias::VERIFIED_ACCOUNT->value)->first();

        if ($verifiedBadge && !$user->badges()->where('badge_id', $verifiedBadge->id)->exists()) {
            $user->addBadge($verifiedBadge);
        }

        event(new UserIdStatus($user, $idVerification->id));
    }
}
















