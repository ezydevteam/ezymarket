<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Enums\Product\CommentReportReason;
use App\Models\User;
use Illuminate\Database\Eloquent\{
    Model,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\BelongsTo,
};

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_comment_reply_id
 * @property CommentReportReason $reason
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read ProductCommentReply $commentReply
 */
class ProductCommentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_comment_reply_id',
        'reason',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'reason' => CommentReportReason::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ==================== Attributes ====================

    protected function reasonBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reason->badge()
        );
    }

    protected function reasonLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reason->label()
        );
    }

    // ==================== Static Methods ====================

    public static function getReasonOptions(): array
    {
        return CommentReportReason::labels();
    }

    // ==================== Relationships ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentReply(): BelongsTo
    {
        return $this->belongsTo(ProductCommentReply::class, 'product_comment_reply_id');
    }
}
