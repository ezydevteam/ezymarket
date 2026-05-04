<?php

declare(strict_types=1);

namespace App\Models\Premium;

use App\Enums\{PremiumPlanInterval, PremiumStatus};
use App\Models\Premium\PremiumPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Premium Model
 *
 * Represents a user's premium membership with status tracking,
 * expiration management, and download limits.
 *
 * @package App\Models\Premium
 *
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property int $total_downloads
 * @property PremiumStatus $status
 * @property \Carbon\Carbon|null $expiry_at
 * @property \Carbon\Carbon|null $last_notification_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read PremiumPlan $plan
 * @property-read string $status_name
 * @property-read string $status_badge_class
 * @property-read string $status_icon
 * @property-read string $status_badge
 * @property-read int|null $days_remaining
 * @property-read bool $is_unlimited_downloads
 * @property-read bool $is_daily_limit_reached
 *
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder onHold()
 * @method static \Illuminate\Database\Eloquent\Builder aboutToExpire()
 * @method static \Illuminate\Database\Eloquent\Builder expired()
 * @method static \Illuminate\Database\Eloquent\Builder byStatus(PremiumStatus $status)
 */
class Premium extends Model
{
    use HasFactory;

    public const RENEWING_DAYS = 3;
    public const EXPIRING_DAYS = 3;

    protected $table = 'premium_memberships';

    protected $fillable = [
        'user_id',
        'plan_id',
        'total_downloads',
        'status',
        'expiry_at',
        'last_notification_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PremiumStatus::class,
            'expiry_at' => 'datetime',
            'last_notification_at' => 'datetime',
        ];
    }

    // ==================== Query Scopes ====================

    /**
     * Scope to filter by premium membership status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param PremiumStatus $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, PremiumStatus $status)
    {
        return $query->where('status', $status->value);
    }

    /**
     * Scope to get only active premium memberships
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', PremiumStatus::ACTIVE->value);
    }

    /**
     * Scope to get only on-hold premium memberships
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnHold($query)
    {
        return $query->where('status', PremiumStatus::ON_HOLD->value);
    }

    /**
     * Scope to get premium memberships about to expire
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAboutToExpire($query)
    {
        return $query->whereHas('plan', function ($q) {
            $q->whereNot('interval', PremiumPlanInterval::LIFETIME->value);
        })
            ->whereNotNull('expiry_at')
            ->where('expiry_at', '>', Carbon::now())
            ->whereRaw('DATEDIFF(expiry_at, NOW()) <= ?', [self::RENEWING_DAYS]);
    }

    /**
     * Scope to get expired premium memberships
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_at')
            ->where('expiry_at', '<', Carbon::now());
    }

    // ==================== Accessor Attributes ====================

    /**
     * Get the status name
     *
     * @return Attribute
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->label()
        );
    }

    /**
     * Get the status badge class
     *
     * @return Attribute
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badgeClass()
        );
    }

    /**
     * Get the status icon
     *
     * @return Attribute
     */
    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->icon()
        );
    }

    /**
     * Get the formatted status badge HTML
     *
     * @return Attribute
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => sprintf(
                '<span class="badge %s"><i class="bi %s me-1"></i>%s</span>',
                $this->status_badge_class,
                $this->status_icon,
                $this->status_name
            )
        );
    }

    /**
     * Get the number of days remaining until expiration
     *
     * @return Attribute
     */
    protected function daysRemaining(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->expiry_at) || $this->plan->isLifetime()) {
                    return null;
                }

                $days = Carbon::now()->diffInDays($this->expiry_at, false);
                return $days >= 0 ? (int) $days : 0;
            }
        );
    }

    /**
     * Check if subscription has unlimited downloads
     *
     * @return Attribute
     */
    protected function isUnlimitedDownloads(): Attribute
    {
        return Attribute::make(
            get: fn() => is_null($this->plan->downloads)
        );
    }

    // ==================== Status Check Methods ====================

    /**
     * Check if premium membership is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === PremiumStatus::ACTIVE;
    }

    /**
     * Check if premium membership is on hold
     *
     * @return bool
     */
    public function isOnHold(): bool
    {
        return $this->status === PremiumStatus::ON_HOLD;
    }

    /**
     * Check if daily download limit has been reached
     *
     * @return bool
     */
    public function isDailyLimitReached(): bool
    {
        return !is_null($this->plan->downloads) && $this->total_downloads >= $this->plan->downloads;
    }

    /**
     * Check if premium membership is about to expire
     *
     * @return bool
     */
    public function isAboutToExpire(): bool
    {
        if ($this->plan->isLifetime()) {
            return false;
        }

        if (is_null($this->expiry_at)) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        $expiryDate = Carbon::parse($this->expiry_at);
        $today = Carbon::now();
        $daysLeft = $today->diffInDays($expiryDate, false);

        return $daysLeft <= self::RENEWING_DAYS && $daysLeft >= 0;
    }

    /**
     * Check if premium membership has expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (is_null($this->expiry_at)) {
            return false;
        }
        return $this->expiry_at->isPast();
    }

    // ==================== UI Helper Methods ====================

    /**
     * Get the button class for the premium membership action button
     *
     * @param bool $isRecommended Whether this is a recommended package
     * @return string CSS classes for the button
     */
    public function getButtonClass(bool $isRecommended = false): string
    {
        $baseClass = 'btn btn-lg w-100';

        if ($this->isAboutToExpire() && !$this->plan->isFree()) {
            return $baseClass . ' ' . ($isRecommended ? 'btn-warning' : 'btn-outline-warning') . ' action-confirm';
        }

        if ($this->isExpired() && !$this->plan->isFree()) {
            return $baseClass . ' ' . ($isRecommended ? 'btn-danger' : 'btn-outline-danger') . ' action-confirm';
        }

        if ($this->isExpired() && $this->plan->isFree()) {
            return $baseClass . ' btn-danger';
        }

        return $baseClass . ' ' . ($isRecommended ? 'btn-primary' : 'btn-outline-primary');
    }

    /**
     * Get the button text for the premium membership action button
     *
     * @return string Translated button text
     */
    public function getButtonText(): string
    {
        if (($this->isAboutToExpire() || $this->isExpired()) && !$this->plan->isFree()) {
            return translate('Renew');
        }

        if ($this->isExpired() && $this->plan->isFree()) {
            return translate('Expired');
        }

        return translate('Subscribed');
    }

    /**
     * Check if the premium membership button should be disabled
     *
     * @return bool
     */
    public function isButtonDisabled(): bool
    {
        return ($this->isExpired() && $this->plan->isFree()) ||
            (!$this->isAboutToExpire() && !$this->isExpired());
    }

    // ==================== Relationships ====================

    /**
     * Get the user that owns this premium membership
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the premium plan associated with this premium membership
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PremiumPlan::class, 'plan_id');
    }
}
