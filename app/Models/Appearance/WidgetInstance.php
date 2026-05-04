<?php

namespace App\Models\Appearance;

use App\Cache\CacheManager;
use App\Enums\Widget\WidgetArea;
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

/**
 * WidgetInstance Model
 *
 * Represents a placed widget with its configuration in a specific area.
 *
 * @property int $id
 * @property int $widget_id
 * @property WidgetArea $area
 * @property string|null $title
 * @property array|null $settings
 * @property int $order_id
 * @property bool $is_active
 */
class WidgetInstance extends Model
{
    protected static CacheManager $cache;

    protected $table = 'widget_instances';

    protected $fillable = [
        'widget_id',
        'area',
        'title',
        'settings',
        'order_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'widget_id' => 'integer',
            'area' => WidgetArea::class,
            'settings' => 'array',
            'order_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::$cache = CacheManager::scope('widget_area_', 60);

        static::saved(fn(WidgetInstance $instance) => self::clearCache($instance->area->value));
        static::deleted(fn(WidgetInstance $instance) => self::clearCache($instance->area->value));
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeByOrder(Builder $query): void
    {
        $query->orderBy('order_id');
    }

    public function scopeInArea(Builder $query, WidgetArea|string $area): void
    {
        $areaValue = $area instanceof WidgetArea ? $area->value : $area;
        $query->where('area', $areaValue);
    }

    public static function getForArea(WidgetArea|string $area): Collection
    {
        $areaValue = $area instanceof WidgetArea ? $area->value : $area;

        return self::cache()->remember(
            $areaValue,
            fn() => static::where('area', $areaValue)
                ->where('is_active', true)
                ->orderBy('order_id')
                ->with('widget')
                ->get()
        );
    }

    protected static function cache(): CacheManager
    {
        return static::$cache ??= CacheManager::scope('widget_area_', 60);
    }

    public static function clearCache(string $areaSlug): void
    {
        self::cache()->forget($areaSlug);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): self
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        return $this;
    }

    protected function displayTitle(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->title ?: $this->widget?->title ?? 'Widget'
        );
    }

    public function render(): string
    {
        $widgetClass = $this->widget?->getWidgetInstance();
        if (!$widgetClass) {
            return '';
        }
        return $widgetClass->render($this);
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class, 'widget_id');
    }
}
