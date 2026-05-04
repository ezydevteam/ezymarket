<?php

namespace App\Models\Financial;

use App\Enums\{TransactionStatus, TransactionType};
use App\Models\User;
use App\Models\Purchase;
use App\Models\Premium\PremiumPlan;
use App\Models\Financial\{BuyerTax, PaymentGateway, TransactionProduct};
use App\Traits\CanBeArchived;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property object|null $tax
 * @property float $fees
 * @property float $total
 * @property string|null $payment_id
 * @property string|null $payer_id
 * @property string|null $payer_email
 * @property string|null $payment_proof
 * @property TransactionType $type
 * @property object|null $support
 * @property int|null $purchase_id
 * @property int|null $plan_id
 * @property TransactionStatus $status
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Transaction extends Model
{
    use HasFactory, CanBeArchived;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::forceDeleted(function (self $transaction): void {
            if ($transaction->payment_proof) {
                removeFileFromStorage($transaction->payment_proof, 'local');
            }
        });
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'amount',
        'tax',
        'fees',
        'total',
        'payment_id',
        'payer_id',
        'payer_email',
        'payment_proof',
        'type',
        'support',
        'purchase_id',
        'plan_id',
        'status',
        'reason',
    ];

    /**
     * The relationships that should always be eager loaded.
     */
    protected $with = [
        'trxProducts',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'fees' => 'decimal:2',
            'total' => 'decimal:2',
            'tax' => 'object',
            'support' => 'object',
            'restored_at' => 'datetime',
        ];
    }

    // ============================================
    // Query Scopes
    // ============================================

    /**
     * Scope a query to only include unpaid transactions.
     */
    public function scopeUnpaid($query): void
    {
        $query->where('status', TransactionStatus::UNPAID);
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query): void
    {
        $query->where('status', TransactionStatus::PENDING);
    }

    /**
     * Scope a query to only include paid transactions.
     */
    public function scopePaid($query): void
    {
        $query->where('status', TransactionStatus::PAID);
    }

    /**
     * Scope a query to only include cancelled transactions.
     */
    public function scopeCancelled($query): void
    {
        $query->where('status', TransactionStatus::CANCELLED);
    }

    /**
     * Scope a query to only include purchase transactions.
     */
    public function scopeTypePurchase($query): void
    {
        $query->where('type', TransactionType::PURCHASE);
    }

    /**
     * Scope a query to only include premium transactions.
     */
    public function scopeTypePremium($query): void
    {
        $query->where('type', TransactionType::PREMIUM);
    }

    // ============================================
    // Status Checkers
    // ============================================

    /**
     * Check if transaction is unpaid.
     */
    public function isUnpaid(): bool
    {
        return $this->status === TransactionStatus::UNPAID;
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING;
    }

    /**
     * Check if transaction is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === TransactionStatus::PAID;
    }

    /**
     * Check if transaction is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === TransactionStatus::CANCELLED;
    }

    // ============================================
    // Type Checkers
    // ============================================

    /**
     * Check if transaction type is purchase.
     */
    public function isTypePurchase(): bool
    {
        return $this->type === TransactionType::PURCHASE;
    }

    /**
     * Check if transaction type is support purchase.
     */
    public function isTypeSupportPurchase(): bool
    {
        return $this->type === TransactionType::SUPPORT_PURCHASE;
    }

    /**
     * Check if transaction type is support extend.
     */
    public function isTypeSupportExtend(): bool
    {
        return $this->type === TransactionType::SUPPORT_EXTEND;
    }

    /**
     * Check if transaction type is deposit.
     */
    public function isTypeDeposit(): bool
    {
        return $this->type === TransactionType::DEPOSIT;
    }

    /**
     * Check if transaction type is premium.
     */
    public function isTypePremium(): bool
    {
        return $this->type === TransactionType::PREMIUM;
    }

      // ============================================
    // Eloquent Attributes
    // ============================================

    /**
     * Get the status name attribute.
     */
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->label()
        );
    }

    /**
     * Get the type name attribute.
     */
    protected function typeName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type->label()
        );
    }

    /**
     * Get the status badge attribute.
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status->badge()
        );
    }

    /**
     * Get the type badge attribute.
     */
    protected function typeBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type->badge()
        );
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * Check if transaction has fees.
     */
    public function hasFees(): bool
    {
        return $this->fees > 0;
    }

    /**
     * Check if transaction has tax.
     */
    public function hasTax(): bool
    {
        return $this->tax !== null;
    }

    /**
     * Get all available status options.
     */
    public static function getStatusOptions(): array
    {
        return TransactionStatus::labels();
    }

    /**
     * Get all available type options.
     */
    public static function getTypeOptions(): array
    {
        return TransactionType::labels();
    }

    /**
     * Calculate transaction totals including tax and fees.
     */
    public function calculate(): void
    {
        $total = $this->amount;
        $tax = null;
        $user = $this->user;

        if (!$this->isTypeDeposit()) {
            $buyerTax = BuyerTax::whereJsonContains('countries', $user->address['country'] ?? null)->first();
            if ($buyerTax) {
                $taxRate = $buyerTax->percentage;
                $taxAmount = round((($total * $taxRate) / 100), 2);

                $tax = [
                    'name' => $buyerTax->name,
                    'rate' => $taxRate,
                    'amount' => $taxAmount,
                ];

                $total += $taxAmount;
            }
        }

        $paymentGateway = $this->paymentGateway;

        $fees = 0;
        if ($paymentGateway->fees > 0) {
            $fees = ($total * $paymentGateway->fees) / 100;
        }

        $total += round($fees, 2);

        $this->tax = $tax;
        $this->fees = $fees;
        $this->total = $total;
        $this->update();
    }

    // ============================================
    // Relationships
    // ============================================

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment gateway for the transaction.
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    /**
     * Get the transaction products for the transaction.
     */
    public function trxProducts(): HasMany
    {
        return $this->hasMany(TransactionProduct::class);
    }

    /**
     * Get the purchase associated with the transaction.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the premium plan for the transaction.
     */
    public function premiumPlan(): BelongsTo
    {
        return $this->belongsTo(PremiumPlan::class, 'plan_id');
    }
}
