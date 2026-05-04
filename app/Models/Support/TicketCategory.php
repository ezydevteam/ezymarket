<?php

namespace App\Models\Support;

use App\Scopes\SortableScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ticket Category Model
 *
 * Represents a category for organizing support tickets.
 *
 * @property int $id
 * @property string $name
 * @property bool $status
 * @property int $sort_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Ticket[] $tickets
 * @property-read string $status_badge
 */
class TicketCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'sort_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'sort_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'status_badge',
    ];

    /**
     * Boot the model and register scopes.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortableScope);
    }

    /**
     * Scope query to active categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Check if category is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Get the category's status badge HTML.
     *
     * @return Attribute
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status
                ? '<span class="badge bg-text-green">' . translate('Active') . '</span>'
                : '<span class="badge bg-text-red">' . translate('Inactive') . '</span>'
        );
    }

    /**
     * Get all tickets for this category.
     *
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'ticket_category_id');
    }
}
