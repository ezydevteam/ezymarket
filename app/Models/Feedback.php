<?php

namespace App\Models;

use App\Enums\{FeedbackStatus, FeedbackField};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Feedback Model
 *
 * @property int $id
 * @property string $subject
 * @property FeedbackField $field
 * @property string $description
 * @property array|null $screenshots
 * @property FeedbackStatus $status
 * @property int $user_id
 * @property string|null $admin_notes
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'field',
        'description',
        'screenshots',
        'status',
        'user_id',
        'admin_notes',
        'reviewed_at',
        'resolved_at'
    ];

    protected function casts(): array
    {
        return [
            'field' => FeedbackField::class,
            'status' => FeedbackStatus::class,
            'screenshots' => 'array',
            'reviewed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    // ==================== Attributes ====================

    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badge()
        );
    }

    protected function fieldLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->field->label()
        );
    }

    protected function fieldBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->field->badge()
        );
    }

    protected function screenshotLinks(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->screenshots) || !is_array($this->screenshots)) {
                    return [];
                }

                return array_filter(
                    array_map(fn($path) => storageUrl($path), $this->screenshots)
                );
            }
        );
    }

    // ==================== Scopes ====================

    public function scopePending($query)
    {
        return $query->where('status', FeedbackStatus::PENDING);
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', FeedbackStatus::REVIEWED);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', FeedbackStatus::RESOLVED);
    }

    // ==================== Static Methods ====================

    public static function getStatusOptions(): array
    {
        return FeedbackStatus::labels();
    }

    public static function getFeedbackFields(): array
    {
        return FeedbackField::labels();
    }

    // ==================== Helper Methods ====================

    public function hasScreenshots(): bool
    {
        return !empty($this->screenshots) && is_array($this->screenshots);
    }

    public function isPending(): bool
    {
        return $this->status === FeedbackStatus::PENDING;
    }

    public function isReviewed(): bool
    {
        return $this->status === FeedbackStatus::REVIEWED;
    }

    public function isResolved(): bool
    {
        return $this->status === FeedbackStatus::RESOLVED;
    }

    // ==================== Relationships ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
