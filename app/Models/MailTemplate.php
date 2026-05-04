<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MailTemplate Model
 *
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property string $subject
 * @property string $content
 * @property array|null $shortcodes
 * @property bool $is_active
 */
class MailTemplate extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Templates that cannot be deleted.
     */
    private const DEFAULT_TEMPLATES = [
        'registration_otp',
        'password_reset_otp',
        'email_change_otp',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alias',
        'name',
        'subject',
        'content',
        'shortcodes',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shortcodes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // ============================================
    // Query Scopes
    // ============================================

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive templates.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    // ============================================
    // Status Checkers
    // ============================================

    /**
     * Check if the template is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if the template is inactive.
     */
    public function isInactive(): bool
    {
        return $this->is_active === false;
    }

    /**
     * Check if the template is a default template.
     */
    public function isDefault(): bool
    {
        return in_array($this->alias, self::DEFAULT_TEMPLATES);
    }

    /**
     * Get formatted shortcodes for display.
     *
     * Filters out invalid shortcodes and formats them as {{shortcode}}.
     *
     * @return array Array of formatted shortcodes
     */
    public function getFormattedShortcodes(): array
    {
        $shortcodes = $this->shortcodes;

        // Ensure shortcodes is an array
        if (!is_array($shortcodes)) {
            return [];
        }

        $formatted = [];

        foreach ($shortcodes as $shortcode) {
            // Skip numeric keys and array values
            if (is_numeric($shortcode) || is_array($shortcode)) {
                continue;
            }

            // Convert to string and format
            $shortcodeValue = is_string($shortcode) ? $shortcode : (string)$shortcode;
            $formatted[] = '{{' . $shortcodeValue . '}}';
        }

        return $formatted;
    }
}
