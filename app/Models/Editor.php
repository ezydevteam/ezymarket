<?php

declare(strict_types=1);

namespace App\Models;

use App\Methods\AvatarGenerator;
use App\Models\Product\ProductCategory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\HasApiTokens;

/**
 * Editor Model
 *
 * Represents an editor user who can review and manage product submissions
 * within assigned categories.
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string|null $avatar
 * @property string $password
 * @property string|null $remember_token
 * @property int $google2fa_status
 * @property string|null $google2fa_secret
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read string $name
 * @property-read string $avatar_url
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductCategory> $categories
 */
class Editor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The authentication guard for editors.
     *
     * @var string
     */
    protected $guard = 'editors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'avatar',
        'password',
        'google2fa_status',
        'google2fa_secret',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'google2fa_status' => 'boolean',
        ];
    }

    // ===================================
    // STATUS CHECK METHODS
    // ===================================

    /**
     * Check if the editor is assigned to a specific category.
     *
     * @param int $categoryId
     * @return bool
     */
    public function hasCategory(int $categoryId): bool
    {
        return $this->categories->contains('id', $categoryId);
    }

    /**
     * Check if the editor has 2FA enabled.
     *
     * @return bool
     */
    public function has2fa(): bool
    {
        return $this->google2fa_status === true;
    }

    // ===================================
    // ATTRIBUTE ACCESSORS (Laravel 11.x)
    // ===================================

    /**
     * Get the editor's full name or username.
     *
     * Access via: $editor->full_name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->firstname && $this->lastname) {
                    return "{$this->firstname} {$this->lastname}";
                }

                if ($this->username) {
                    return $this->username;
                }

                if ($this->email) {
                    return explode('@', $this->email)[0];
                }

                return 'Unknown Editor';
            }
        );
    }

    /**
     * Get the editor's avatar URL.
     *
     * Access via: $editor->avatar_url
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->avatar) {
                    return asset($this->avatar);
                }

                return AvatarGenerator::initials(
                    firstname: $this->firstname,
                    lastname: $this->lastname,
                    username: $this->username,
                    email: $this->email
                );
            }
        );
    }

    /**
     * Decrypt the Google 2FA secret when accessing.
     *
     * @param string|null $value
     * @return string|null
     */
    public function getGoogle2faSecretAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    // ===================================
    // RELATIONSHIPS
    // ===================================

    /**
     * Get the product categories assigned to this editor.
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'editor_product_categories', 'editor_id', 'product_category_id');
    }

    // ===================================
    // NOTIFICATIONS
    // ===================================

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        Notification::sendPasswordResetNotification($this, $token);
    }
}

















