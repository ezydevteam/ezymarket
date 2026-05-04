<?php

namespace App\Models;

use App\Enums\LicenseType;
use App\Models\Product\Product;
use App\Models\Support\SupportPackage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartProduct extends Model
{
    use HasFactory;

    protected $table = 'cart_products';

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'license_type',
        'quantity',
        'support_package_id',
    ];

    protected function casts(): array
    {
        return [
            'license_type' => LicenseType::class,
        ];
    }

    public function scopeForCurrentSession($query): void
    {
        if (authUser()) {
            $query->where('user_id', authUser()->id)
                ->whereNull('session_id');
        } else {
            $query->where('session_id', session()->get('session_id'))
                ->whereNull('user_id');
        }
    }

    public function isRegularLicense(): bool
    {
        return $this->license_type === LicenseType::REGULAR;
    }

    public function isExtendedLicense(): bool
    {
        return $this->license_type === LicenseType::EXTENDED;
    }

    public function getUnitPrice(): float
    {
        $product = $this->product;

        if ($this->isExtendedLicense()) {
            $unitPrice = $product->isExtendedOnDiscount()
                ? $product->discount->price->extended
                : $product->price->extended;
        } else {
            $unitPrice = $product->hasValidDiscount()
                ? $product->discount->price->regular
                : $product->price->regular;
        }
        return $unitPrice;
    }

    public function getTotalAmount(): float
    {
        return ($this->getUnitPrice() * $this->quantity);
    }

    public function getTotalAmountWithSupport(): float
    {
        $amount = $this->getUnitPrice();

        $supportPackage = $this->supportPackage;
        if ($supportPackage) {
            $amount += $supportPackage->calculatePrice($amount);
        }

        return ($amount * $this->quantity);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supportPackage(): BelongsTo
    {
        return $this->belongsTo(SupportPackage::class, 'support_package_id');
    }
}
















