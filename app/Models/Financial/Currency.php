<?php

namespace App\Models\Financial;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    public const BEFORE_PRICE = 1;
    public const AFTER_PRICE = 2;

    protected $fillable = [
        'code',
        'symbol',
        'country',
        'position',
        'rate',
        'icon',
        'sort_id',
        'is_default',
    ];

    protected $casts = [
        'position' => 'integer',
        'rate' => 'decimal:9',
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SortableScope);
    }

    public function scopeDefault($query): void
    {
        $query->where('is_default', true);
    }

    public function isDefault(): bool
    {
        return $this->is_default === true;
    }

    public function isBeforePrice(): bool
    {
        return $this->position === self::BEFORE_PRICE;
    }

    public function isAfterPrice(): bool
    {
        return $this->position === self::AFTER_PRICE;
    }

    protected function iconLink(): Attribute
    {
        return Attribute::make(
            get: fn() => asset($this->icon)
        );
    }

    public function getPositionName(): string
    {
        return self::getCurrencyPositionOptions()[$this->position];
    }

    public static function getCurrencyPositionOptions(): array
    {
        return [
            self::BEFORE_PRICE => translate('Before price'),
            self::AFTER_PRICE => translate('After price'),
        ];
    }
}
