<?php

namespace App\Models\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'percentage',
        'countries',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'countries' => 'array',
        ];
    }

    /**
     * Scope to find tax by country code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $countryCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCountry($query, string $countryCode)
    {
        return $query->whereJsonContains('countries', $countryCode);
    }

    /**
     * Get the tax rate as a percentage.
     *
     * @return float
     */
    public function getRatePercentage(): float
    {
        return (float) $this->percentage;
    }

    /**
     * Get formatted country names.
     *
     * @return array
     */
    public function getCountryNames(): array
    {
        return array_map(function($code) {
            return countries($code) ?? $code;
        }, $this->countries ?? []);
    }
}
