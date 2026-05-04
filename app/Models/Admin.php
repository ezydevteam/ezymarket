<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Admin\AdminRole;
use App\Methods\AvatarGenerator;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductHistory;
use App\Notifications\Auth\PasswordResetNotification;
use App\Services\GeolocationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Encryption\DecryptException;
use Reefki\DeviceDetector\Device;
use Illuminate\Support\Facades\DB;

/**
 * Admin Model
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 * @property string $email
 * @property string $role
 * @property bool $status
 * @property string|null $avatar
 * @property string $password
 * @property int $google2fa_status
 * @property string|null $google2fa_secret
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read string $full_name
 * @property-read string $avatar_url
 * @property-read string $role_label
 */
class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The guard name for authentication.
     *
     * @var string
     */
    protected $guard = 'admins';

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
        'role',
        'status',
        'last_login_at',
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
            'last_login_at' => 'datetime',
            'google2fa_status' => 'integer',
            'status' => 'boolean',
            'password' => 'hashed',
            'role' => AdminRole::class,
        ];
    }

    /**
     * Scope a query to only include admins with 2FA enabled.
     */
    public function scopeWith2fa(Builder $query): Builder
    {
        return $query->where('google2fa_status', 1);
    }

    /**
     * Scope a query to only include admins.
     */
    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('role', AdminRole::ADMIN->value);
    }

    /**
     * Scope a query to only include staff (exclude admin role).
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereNot('role', AdminRole::ADMIN->value);
    }

    /**
     * Scope a query to only include active admins.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include admins with system access (admin, manager).
     */
    public function scopeSystemAccess(Builder $query): Builder
    {
        return $query->whereIn('role', [
            AdminRole::ADMIN->value,
            AdminRole::MANAGER->value,
        ]);
    }

    /**
     * Scope a query to only include admins with product access (admin, manager, reviewer).
     */
    public function scopeProductAccess(Builder $query): Builder
    {
        return $query->whereIn('role', [
            AdminRole::ADMIN->value,
            AdminRole::MANAGER->value,
            AdminRole::REVIEWER->value,
        ]);
    }

    /**
     * Scope a query to only include admins with financial access (admin, manager, accountant).
     */
    public function scopeFinancialAccess(Builder $query): Builder
    {
        return $query->whereIn('role', [
            AdminRole::ADMIN->value,
            AdminRole::MANAGER->value,
            AdminRole::ACCOUNTANT->value,
        ]);
    }

    /**
     * Check if user is admin (main administrator).
     */
    public function isAdmin(): bool
    {
        return $this->role === AdminRole::ADMIN;
    }

    /**
     * Check if user is manager.
     */
    public function isManager(): bool
    {
        return $this->role === AdminRole::MANAGER;
    }

    /**
     * Check if user is accountant.
     */
    public function isAccountant(): bool
    {
        return $this->role === AdminRole::ACCOUNTANT;
    }

    /**
     * Check if admin is reviewer role.
     */
    public function isReviewer(): bool
    {
        return $this->role === AdminRole::REVIEWER;
    }

    /**
     * Check if role can manage system settings.
     */
    public function canManageSystem(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Check if role can manage products.
     */
    public function canManageProducts(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isReviewer();
    }

    /**
     * Check if role can manage financial data.
     */
    public function canManageFinancials(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isAccountant();
    }

    /**
     * Check if admin is active.
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Check if admin is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === false;
    }

    /**
     * Activate the admin account.
     */
    public function activate(): bool
    {
        return $this->update(['status' => true]);
    }

    /**
     * Deactivate the admin account.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => false]);
    }

    /**
     * Check if admin has two-factor authentication enabled.
     */
    public function has2fa(): bool
    {
        return $this->google2fa_status === 1;
    }

    /**
     * Enable two-factor authentication.
     */
    public function enable2fa(?string $secret = null): bool
    {
        return $this->update([
            'google2fa_status' => 1,
            'google2fa_secret' => $secret,
        ]);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable2fa(): bool
    {
        return $this->update([
            'google2fa_status' => 0,
            'google2fa_secret' => null,
        ]);
    }

    /**
     * Decrypt the Google 2FA secret when accessed.
     */
    protected function google2faSecret(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (!$value) {
                    return null;
                }

                try {
                    return decrypt($value);
                } catch (DecryptException $e) {
                    return null;
                }
            }
        );
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PasswordResetNotification($this, $token, 'admin.password.reset'));
    }

    /**
     * Update admin's password.
     */
    public function updatePassword(string $password): bool
    {
        return $this->update([
            'password' => bcrypt($password),
        ]);
    }

    /**
     * Get the admin's full name attribute.
     *
     * Access via: $admin->full_name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->firstname && $this->lastname) {
                    return $this->firstname . ' ' . $this->lastname;
                }

                if ($this->firstname) {
                    return $this->firstname;
                }

                if ($this->username) {
                    return $this->username;
                }

                if ($this->email) {
                    return explode('@', $this->email)[0];
                }

                return 'Admin';
            }
        );
    }

    /**
     * Get the admin's avatar URL attribute.
     *
     * Access via: $admin->avatar_url
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
     * Delete admin's avatar file.
     */
    public function deleteAvatar(): void
    {
        if ($this->avatar) {
            removeFile($this->avatar);
        }
    }

    /**
     * Get the admin's role label attribute.
     *
     * Access via: $admin->role_label
     *
     * @return Attribute<string, never>
     */
    protected function roleLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->role instanceof AdminRole ? $this->role->label() : ucfirst((string) $this->role)
        );
    }

    /**
     * Check if admin has specific role(s).
     *
     * @param AdminRole|array<AdminRole> $roles
     * @return bool
     */
    public function hasRole(AdminRole|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }
        return $this->role === $roles;
    }

    /**
     * Check if admin has category assigned (only for reviewers).
     *
     * @param int $categoryId
     * @return bool
     */
    public function hasCategory(int $categoryId): bool
    {
        if (!$this->isReviewer()) {
            return false;
        }
        return $this->categories->contains('id', $categoryId);
    }

    /**
     * Log admin login activity with device and location information
     *
     * @return void
     */
    public function logLoginActivity(): void
    {
        $ip = getIp();
        $ipLookup = app(GeolocationService::class)->lookup($ip);

        // Device detection using Device Facade
        $detector = Device::detectRequest(request());
        $client = $detector->getClient();
        $os = $detector->getOs();

        // Update last login timestamp
        $this->update(['last_login_at' => now()]);

        // Create a new login activity entry for each login in admin_login_activities table
        DB::table('admin_login_activities')->insert([
            'admin_id' => $this->id,
            'ip_address' => $ip,
            'country' => $ipLookup->country,
            'timezone' => $ipLookup->timezone,
            'location' => $ipLookup->location,
            'latitude' => $ipLookup->latitude,
            'longitude' => $ipLookup->longitude,
            'browser' => $client['name'] ?? 'Unknown',
            'browser_version' => $client['version'] ?? null,
            'os' => $os['name'] ?? 'Unknown',
            'os_version' => $os['version'] ?? null,
            'device_type' => $detector->getDeviceName() ?: 'Desktop',
            'device_brand' => $detector->getBrandName() ?: null,
            'device_model' => $detector->getModel() ?: null,
            'is_bot' => $detector->isBot() ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get the product categories assigned to this admin (for reviewer role).
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'admin_product_categories',
            'admin_id',
            'product_category_id'
        )->withTimestamps();
    }

    /**
     * Get the product history entries created by this admin.
     *
     * @return HasMany
     */
    public function productHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class, 'admin_id');
    }
}
