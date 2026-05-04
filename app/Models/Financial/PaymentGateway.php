<?php

namespace App\Models\Financial;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $table = 'payment_gateways';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Mode constants
     */
    const MODE_SANDBOX = 'sandbox';
    const MODE_LIVE = 'live';

    /**
     * Alias constants
     */
    const ALIAS_BALANCE = 'balance';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'logo',
        'fees',
        'charge_currency',
        'charge_rate',
        'credentials',
        'instructions',
        'mode',
        'is_active',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortableScope);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'credentials' => 'object',
            'parameters' => 'object',
            'is_manual' => 'boolean',
            'fees' => 'integer',
            'charge_rate' => 'decimal:4',
            'is_active' => 'boolean',
            'sort_id' => 'integer',
        ];
    }

    /**
     * Scope a query to only include active gateways.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_id.
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_id');
    }

    /**
     * Scope a query to exclude balance gateway if user balance is zero.
     */
    public function scopeExcludeBalanceIfZero($query, float $balance)
    {
        return $balance == 0
            ? $query->whereNot('alias', self::ALIAS_BALANCE)
            : $query;
    }

    /**
     * Scope a query to only include account balance gateway.
     */
    public function scopeAccountBalance($query)
    {
        return $query->where('alias', self::ALIAS_BALANCE);
    }

    /**
     * Scope a query to exclude account balance gateway.
     */
    public function scopeNotAccountBalance($query)
    {
        return $query->whereNot('alias', self::ALIAS_BALANCE);
    }

    /**
     * Scope a query for transaction type.
     */
    public function scopeForTrx($query, $trx)
    {
        if ($trx->isTypeDeposit()) {
            return $query->notAccountBalance();
        }

        return $query;
    }

    /**
     * Check if gateway is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Check if gateway is manual.
     */
    public function isManual(): bool
    {
        return $this->is_manual === true;
    }

    /**
     * Check if gateway is in sandbox mode.
     */
    public function isSandboxMode(): bool
    {
        return $this->mode === self::MODE_SANDBOX;
    }

    /**
     * Check if gateway is in live mode.
     */
    public function isLiveMode(): bool
    {
        return $this->mode === self::MODE_LIVE;
    }

    /**
     * Check if gateway is account balance.
     */
    public function isAccountBalance(): bool
    {
        return $this->alias === self::ALIAS_BALANCE;
    }

    /**
     * Check if gateway has custom charge currency.
     */
    public function hasCustomChargeCurrency(): bool
    {
        return !empty($this->charge_currency) && !empty($this->charge_rate);
    }

    /**
     * Get the gateway logo URL.
     */
    protected function logoLink(): Attribute
    {
        return Attribute::make(
            get: fn() => asset($this->logo ?? 'images/default-gateway.png'),
        );
    }

    /**
     * Get the currency code for this gateway.
     */
    public function getCurrency(): string
    {
        return $this->hasCustomChargeCurrency()
            ? $this->charge_currency
            : defaultCurrency()->code;
    }

    /**
     * Calculate the charge amount based on gateway rate.
     */
    public function getChargeAmount(float $amount): float
    {
        if ($this->hasCustomChargeCurrency()) {
            return round($amount * $this->charge_rate, 2);
        }

        return round($amount, 2);
    }

    /**
     * Get available gateway modes.
     */
    public static function getModes(): array
    {
        return [
            self::MODE_SANDBOX => translate('Sandbox'),
            self::MODE_LIVE => translate('Live'),
        ];
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            false => translate('Inactive'),
            true => translate('Active'),
        ];
    }
}
