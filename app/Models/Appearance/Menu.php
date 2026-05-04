<?php

namespace App\Models\Appearance;

use App\Cache\CacheManager;
use App\Enums\Menu\{MenuLocation, MenuType, MenuStyle};
use Illuminate\Database\Eloquent\{Model, Builder};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Menu Model
 *
 * Unified menu management with location support.
 * Supports 3-level nesting: Parent -> Child -> Grandchild
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property MenuType $menu_type
 * @property MenuStyle|null $menu_style
 * @property MenuLocation $location
 * @property int|null $parent_id
 * @property int $order_id
 * @property string|null $badge
 * @property string|null $custom_class
 * @property string|null $custom_html
 * @property string|null $icon
 * @property string|null $icon_color
 * @property bool $hide_name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Menu extends Model
{
    use HasFactory;

    /**
     * Maximum nesting depth (3 levels)
     */
    public const int MAX_DEPTH = 3;

    /**
     * The table associated with the model.
     */
    protected $table = 'menus';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'menu_type',
        'menu_style',
        'location',
        'parent_id',
        'order_id',
        'badge',
        'badge_color',
        'icon',
        'icon_color',
        'custom_class',
        'custom_html',
        'hide_name',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'menu_type' => MenuType::class,
            'menu_style' => MenuStyle::class,
            'location' => MenuLocation::class,
            'parent_id' => 'integer',
            'order_id' => 'integer',
            'hide_name' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model and register cache clearing on changes.
     */
    protected static function booted(): void
    {
        static::saved(fn(Menu $menu) => self::clearCache($menu->location->value));
        static::deleted(fn(Menu $menu) => self::clearCache($menu->location->value));
    }

    /**
     * Scope: Order by order_id.
     */
    public function scopeByOrder(Builder $query): void
    {
        $query->orderBy('order_id', 'asc');
    }

    /**
     * Scope: Only parent menus (no parent_id).
     */
    public function scopeParents(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Scope: Filter by location.
     */
    public function scopeLocation(Builder $query, string|MenuLocation $location): void
    {
        $value = $location instanceof MenuLocation ? $location->value : $location;
        $query->where('location', $value);
    }

    /**
     * Scope: Only active menus.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope: Include ordered children.
     */
    public function scopeWithOrderedChildren(Builder $query): void
    {
        $query->with(['children' => fn($q) => $q->byOrder()->with(['children' => fn($q2) => $q2->byOrder()])]);
    }

    /**
     * Check if this is an internal menu link.
     */
    public function isInternal(): bool
    {
        return $this->menu_type === MenuType::INTERNAL;
    }

    /**
     * Check if this is an external menu link.
     */
    public function isExternal(): bool
    {
        return $this->menu_type === MenuType::EXTERNAL;
    }

    /**
     * Check if this is a heading (not clickable).
     */
    public function isHeading(): bool
    {
        return $this->menu_type === MenuType::HEADING;
    }

    /**
     * Check if this is a mega menu.
     */
    public function isMegaMenu(): bool
    {
        return $this->menu_type === MenuType::MEGA;
    }

    /**
     * Get the mega menu style CSS class.
     */
    public function getMegaStyleClass(): string
    {
        return $this->menu_style?->cssClass() ?? 'mega-menu-1col';
    }

    /**
     * Get the number of columns for mega menu.
     */
    public function getMegaColumns(): int
    {
        return $this->menu_style?->columns() ?? 1;
    }

    /**
     * Check if label should be hidden.
     */
    public function shouldHideLabel(): bool
    {
        return $this->hide_name === true;
    }

    /**
     * Check if menu has an icon.
     */
    public function hasIcon(): bool
    {
        return !empty($this->icon);
    }

    /**
     * Get icon style attribute.
     */
    public function getIconStyle(): string
    {
        return $this->icon_color ? "color: {$this->icon_color};" : '';
    }

    /**
     * Check if menu is clickable.
     */
    public function isClickable(): bool
    {
        return $this->menu_type?->isClickable() ?? true;
    }

    /**
     * Check if this menu has a parent.
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if this menu has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if menu has a badge.
     */
    public function hasBadge(): bool
    {
        return !empty($this->badge);
    }

    /**
     * Get available locations.
     */
    public static function getLocations(): array
    {
        return MenuLocation::toArray();
    }

    /**
     * Get available menu types.
     */
    public static function getMenuTypes(): array
    {
        return MenuType::toArray();
    }

    /**
     * Get available menu styles.
     */
    public static function getMenuStyles(): array
    {
        return MenuStyle::toArray();
    }

    /**
     * Get available Bootstrap text & badge colors.
     */
    public static function getColorScheme(): array
    {
        return [
            'primary' => 'Primary (Blue)',
            'secondary' => 'Secondary (Gray)',
            'success' => 'Success (Green)',
            'danger' => 'Danger (Red)',
            'warning' => 'Warning (Yellow)',
            'info' => 'Info (Cyan)',
            'light' => 'Light (Light Gray)',
            'dark' => 'Dark (Black)',
            'body' => 'Body Text',
            'muted' => 'Muted (Lighter Gray)',
            'white' => 'White',
            'black-50' => 'Black 50% Opacity',
            'white-50' => 'White 50% Opacity',
        ];
    }

    /**
     * Get the nesting level (0 = root, 1 = child, 2 = grandchild).
     */
    public function getLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    /**
     * Check if can have children (max 3 levels).
     */
    public function canHaveChildren(): bool
    {
        return $this->getLevel() < (self::MAX_DEPTH - 1);
    }

    /**
     * Update menu order and parent.
     */
    public function updatePosition(int $order, ?int $parentId = null): bool
    {
        return $this->update([
            'order_id' => $order,
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Clear cache for a specific location.
     */
    public static function clearCache(?string $location = null): void
    {
        if ($location) {
            CacheManager::scope('menu_', 1440)->forget($location);
        } else {
            // Clear all menu location caches
            CacheManager::scope('menu_', 1440)->forgetMultiple(MenuLocation::values());
        }
    }

    /**
     * Get menus for frontend by location (cached).
     */
    public static function getByLocation(string|MenuLocation $location): Collection
    {
        $locationValue = $location instanceof MenuLocation ? $location->value : $location;

        return CacheManager::scope('menu_', 1440)->remember($locationValue, function () use ($locationValue) {
            return static::location($locationValue)
                ->active()
                ->parents()
                ->withOrderedChildren()
                ->byOrder()
                ->get();
        });
    }

    /**
     * Get the menu link URL.
     * Returns null for headings, full URL for external, route URL for internal.
     */
    protected function link(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->isHeading() || empty($this->slug)) {
                    return null;
                }

                if ($this->isExternal()) {
                    return $this->slug;
                }

                return url($this->slug);
            }
        );
    }

    /**
     * Get the children menus.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order_id');
    }

    /**
     * Get the parent menu.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get all descendants (children and grandchildren) recursively.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }
}
