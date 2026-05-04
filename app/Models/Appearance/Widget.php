<?php

namespace App\Models\Appearance;

use App\Contracts\WidgetContract;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Widget Model
 *
 * Represents available widget types in the system.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $class
 * @property string|null $description
 * @property string|null $icon
 * @property bool $is_active
 */
class Widget extends Model
{
    protected $table = 'widgets';

    protected $fillable = [
        'title',
        'slug',
        'class',
        'description',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeByTitle(Builder $query): void
    {
        $query->orderBy('title');
    }

    public function getWidgetInstance(): ?WidgetContract
    {
        if (!class_exists($this->class)) {
            return null;
        }

        return app($this->class);
    }

    public function isValid(): bool
    {
        return class_exists($this->class)
            && is_subclass_of($this->class, WidgetContract::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WidgetInstance::class, 'widget_id');
    }
}
