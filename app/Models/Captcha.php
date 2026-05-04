<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Captcha extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'captcha_providers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alias',
        'name',
        'description',
        'logo',
        'site_key',
        'secret_key',
        'is_active',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    public function scopeInactive($query): void
    {
        $query->where('is_active', false);
    }

    public function scopeDefault($query): void
    {
        $query->where('is_default', true);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function isInactive(): bool
    {
        return $this->is_active === false;
    }

    public function isDefault(): bool
    {
        return $this->is_default === true;
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->logo ? asset($this->logo) : null
        );
    }

}


















