<?php

namespace App\Models\Support;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportPackage extends Model
{
    use HasFactory;

    protected $table = 'support_packages';

    protected $fillable = [
        'name',
        'title',
        'days',
        'rate',
        'sort_id',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'array',
        ];
    }

    protected static function booted()
    {
        static::addGlobalScope(new SortableScope);
    }


    public function scopeFree($query)
    {
        $query->where(function ($q) {
            $q->where(function ($sq) {
                $sq->whereNull('rate->percentage')
                   ->orWhere('rate->percentage', '<=', 0);
            })->where(function ($sq) {
                $sq->whereNull('rate->fixed')
                   ->orWhere('rate->fixed', '<=', 0);
            });
        });
    }

    public function scopeNotFree($query)
    {
        $query->where(function ($q) {
            $q->where('rate->percentage', '>', 0)
              ->orWhere('rate->fixed', '>', 0);
        });
    }

    public function isFree(): bool
    {
        $rate = $this->rate;
        return (!isset($rate['percentage']) || $rate['percentage'] == 0)
            && (!isset($rate['fixed']) || $rate['fixed'] == 0);
    }

    /**
     * Calculate support price based on product price
     *
     * @param float $productPrice
     * @return float
     */
    public function calculatePrice(float $productPrice): float
    {
        $rate = $this->rate;
        $total = 0;

        // Add percentage-based pricing
        if (isset($rate['percentage']) && $rate['percentage'] > 0) {
            $total += ($productPrice * $rate['percentage']) / 100;
        }

        // Add fixed pricing
        if (isset($rate['fixed']) && $rate['fixed'] > 0) {
            $total += $rate['fixed'];
        }

        return ceil($total);
    }

    /**
     * Get percentage rate
     *
     * @return int
     */
    public function getPercentage(): int
    {
        return $this->rate['percentage'] ?? 0;
    }

    /**
     * Get fixed rate
     *
     * @return float
     */
    public function getFixed(): float
    {
        return $this->rate['fixed'] ?? 0;
    }

    /**
     * Check if package has percentage-based pricing
     *
     * @return bool
     */
    public function hasPercentageRate(): bool
    {
        return isset($this->rate['percentage']) && $this->rate['percentage'] > 0;
    }

    /**
     * Check if package has fixed pricing
     *
     * @return bool
     */
    public function hasFixedRate(): bool
    {
        return isset($this->rate['fixed']) && $this->rate['fixed'] > 0;
    }

    /**
     * Get formatted rate description
     */
    protected function rateDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                $parts = [];

                if ($this->hasPercentageRate()) {
                    $parts[] = $this->getPercentage() . '%';
                }

                if ($this->hasFixedRate()) {
                    $parts[] = '+' . getAmount($this->getFixed());
                }

                return !empty($parts) ? implode(' ', $parts) : 'Free';
            }
        );
    }
}
