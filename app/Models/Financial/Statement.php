<?php

declare(strict_types=1);

namespace App\Models\Financial;

use App\Enums\StatementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Statement Model
 *
 * Represents financial transaction statements for users.
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property float $amount
 * @property float|null $buyer_fee
 * @property float|null $seller_fee
 * @property float $total
 * @property StatementType $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read string $type_name
 * @property-read string $type_badge_class
 * @property-read string $type_icon
 * @property-read string $type_sign
 * @property-read string $formatted_buyer_fee
 * @property-read string $formatted_seller_fee
 * @property-read string $formatted_total
 *
 * @property-read User $user
 */
class Statement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'amount',
        'buyer_fee',
        'seller_fee',
        'tax',
        'total',
        'type',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => StatementType::class
        ];
    }

    /**
     * Scope query to credit statements
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeCredit($query): void
    {
        $query->where('type', StatementType::CREDIT->value);
    }

    /**
     * Scope query to debit statements
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeDebit($query): void
    {
        $query->where('type', StatementType::DEBIT->value);
    }

    /**
     * Get the human-readable type name
     *
     * @return Attribute<string, never>
     */
    protected function typeName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type->label()
        );
    }

    /**
     * Get the Bootstrap badge class for type
     *
     * @return Attribute<string, never>
     */
    protected function typeBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type->badge()
        );
    }

    /**
     * Get the FontAwesome icon for type
     *
     * @return Attribute<string, never>
     */
    protected function typeIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type->icon()
        );
    }

    /**
     * Get the sign prefix for type
     *
     * @return Attribute<string, never>
     */
    protected function typeSign(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type->sign()
        );
    }

    /**
     * Check if statement is credit type
     *
     * @return bool
     */
    public function isCredit(): bool
    {
        return $this->type === StatementType::CREDIT;
    }

    /**
     * Check if statement is debit type
     *
     * @return bool
     */
    public function isDebit(): bool
    {
        return $this->type === StatementType::DEBIT;
    }

    /**
     * Get the user who owns the statement
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
