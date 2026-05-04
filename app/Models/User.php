<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\{
    Product\ProductStatus,
    User\SellerType,
    User\UserStatus
};
use App\Events\{UserSuspended, UserBecameSeller};
use App\Models\{
    Financial\Payout,
    Financial\PayoutMethod,
    Financial\Transaction,
    Product\Product,
    Product\ProductComment,
    Product\ProductReview,
    Premium\Premium,
    Support\Ticket
};
use App\Concerns\{HasBadges, HasSocialAuth, HasChatFeatures, HasEmailOtp};
use App\Facades\Notification;
use App\Methods\AvatarGenerator;
use App\Services\GeolocationService;
use Reefki\DeviceDetector\Device as DeviceDetector;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{
    Builder,
    SoftDeletes,
    Casts\Attribute,
    Factories\HasFactory,
    Relations\HasOne,
    Relations\HasMany,
    Relations\BelongsTo,
    Relations\BelongsToMany,
};
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * Represents a user in the system who can be a buyer, seller, or both.
 * Handles authentication, authorization, profile management, and user relationships.
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $api_key
 * @property array|null $address
 * @property bool $is_seller
 * @property bool $is_featured_seller
 * @property \App\Enums\SellerType|null $seller_type
 * @property float $balance
 * @property int|null $level_id
 * @property int $total_sales
 * @property float $total_sales_amount
 * @property float $total_referrals_earnings
 * @property int $total_reviews
 * @property float $avg_reviews
 * @property int $total_followers
 * @property int $total_following
 * @property string|null $avatar
 * @property array|null $basic_info
 * @property string|null $facebook_id
 * @property string|null $google_id
 * @property string|null $microsoft_id
 * @property string|null $envato_id
 * @property string|null $github_id
 * @property int|null $payout_method_id
 * @property string|null $payout_account
 * @property bool $had_premium
 * @property bool $is_id_verified
 * @property int $google2fa_status
 * @property string|null $google2fa_secret
 * @property \App\Enums\Usertatus $status
 * @property \Carbon\Carbon|null $last_active_at
 * @property bool $is_online
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read string $full_name
 * @property-read string|null $country_name
 * @property-read string $avatar_url
 * @property-read string $profile_cover_url
 * @property-read string $profile_link
 * @property-read string $portfolio_link
 * @property-read string $referral_link
 * @property-read \App\Models\User|null $referred_by_user
 * @property-read string|null $last_active_formatted
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IdVerification> $idVerifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follower> $followers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follower> $followings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $following
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $followedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $activeProducts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductComment> $productComments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductReview> $productReviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartProduct> $cartProducts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserBadge> $badges
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Purchase> $purchases
 * @property-read \App\Models\Level|null $level
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Referral> $referrals
 * @property-read \App\Models\Referral|null $referral
 * @property-read \App\Models\Referral|null $referred_by
 * @property-read \App\Models\PayoutMethod|null $payoutMethod
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductReview> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Favorite> $favorites
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payout> $payouts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read \App\Models\Premium\Premium|null $premium
 * @property array|null $notification_preferences
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Chatbox> $conversations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatboxChat> $sentMessages
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatboxBlock> $blocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChatboxBlock> $blockedBy
 *
 * @method static Builder|User active()
 * @method static Builder|User suspended()
 * @method static Builder|User emailVerified()
 * @method static Builder|User emailUnVerified()
 * @method static Builder|User idVerified()
 * @method static Builder|User idUnverified()
 * @method static Builder|User user()
 * @method static Builder|User seller()
 * @method static Builder|User featuredSeller()
 * @method static Builder|User whereDataCompleted()
 * @method int increment(string $column, float|int $amount = 1, array $extra = [])
 * @method int decrement(string $column, float|int $amount = 1, array $extra = [])
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasBadges, HasSocialAuth, HasChatFeatures, HasEmailOtp, Notifiable, SoftDeletes;

    protected $table = 'users';

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
        'phone',
        'password',
        'api_key',
        'address',
        'basic_info',
        'is_seller',
        'seller_type',
        'is_featured_seller',
        'total_sales',
        'total_sales_amount',
        'total_referrals_earnings',
        'total_reviews',
        'avg_reviews',
        'total_followers',
        'total_following',
        'balance',
        'payout_account',
        'had_premium',
        'is_id_verified',
        'google2fa_status',
        'google2fa_secret',
        'payout_method_id',
        'level_id',
        'facebook_id',
        'google_id',
        'microsoft_id',
        'envato_id',
        'github_id',
        'can_message',
        'is_online',
        'last_active_at',
        'status',
        'deleted_by',
        'notification_preferences',
        'chatbox_blocked_users',
        'email_otp',
        'email_otp_expires_at',
        'pending_email'
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'location',
        'avatar_url',
        'profile_link',
        'store_link',
        'last_active_formatted',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_seller' => 'boolean',
            'is_featured_seller' => 'boolean',
            'had_premium' => 'boolean',
            'is_id_verified' => 'boolean',
            'is_online' => 'boolean',
            'can_message' => 'boolean',
            'created_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'deleted_at' => 'datetime',
            'address' => 'array',
            'basic_info' => 'array',
            'balance' => 'decimal:2',
            'total_sales_amount' => 'decimal:2',
            'total_referrals_earnings' => 'decimal:2',
            'avg_reviews' => 'decimal:2',
            'email_otp_expires_at' => 'datetime',
            'status' => UserStatus::class,
            'seller_type' => SellerType::class,
            'notification_preferences' => 'array',
            'chatbox_blocked_users' => 'array',
        ];
    }

    // ===================================
    // MODEL EVENTS
    // ===================================

    /**
     * The "booted" method of the model.
     *
     * Register model event listeners for important actions.
     */
    protected static function booted(): void
    {
        // User status changed
        static::updated(function (User $user) {
            if ($user->isDirty('status') && $user->status === UserStatus::SUSPENDED) {

                // Revoke all tokens (logout from all devices)
                $user->tokens()->delete();

                // Dispatch event
                event(new UserSuspended($user));
            }

            // User became a seller
            if ($user->isDirty('is_seller') && $user->is_seller === true) {
                event(new UserBecameSeller($user));
            }

            // User became exclusive seller
            if ($user->isDirty('seller_type') && $user->seller_type === SellerType::EXCLUSIVE) {
                $user->addExclusiveSellerBadge();

                // Dispatch event (exclusive upgrade)
                // event(new UserBecameSeller($user, SellerType::EXCLUSIVE, true));
            }

            // Email verified
            if ($user->isDirty('email_verified_at') && $user->email_verified_at !== null) {
                logger()->info('User email verified', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                // event(new UserEmailVerified($user));
            }
        });
    }

    // ===================================
    // QUERY SCOPES
    // ===================================

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', UserStatus::ACTIVE);
    }

    /**
     * Scope a query to only include suspended users.
     */
    public function scopeSuspended(Builder $query): void
    {
        $query->where('status', UserStatus::SUSPENDED);
    }

    /**
     * Scope a query to only include users with verified email.
     */
    public function scopeEmailVerified(Builder $query): void
    {
        $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope a query to only include users with verified ID.
     */
    public function scopeIdVerified(Builder $query): void
    {
        $query->where('is_id_verified', true);
    }

    /**
     * Scope a query to only include regular users (not sellers).
     */
    public function scopeUser(Builder $query): void
    {
        $query->where('is_seller', false);
    }

    /**
     * Scope a query to only include sellers.
     */
    public function scopeSeller(Builder $query): void
    {
        $query->where('is_seller', true);
    }

    /**
     * Scope a query to only include featured sellers.
     */
    public function scopeFeaturedSeller(Builder $query): void
    {
        $query->where('is_featured_seller', true);
    }

    /**
     * Scope a query to only include users with completed data.
     */
    public function scopeWhereDataCompleted(Builder $query): void
    {
        $query->whereNotNull('firstname')
            ->whereNotNull('lastname')
            ->whereNotNull('username')
            ->whereNotNull('email')
            ->whereNotNull('password');
    }

    // ===================================
    // STATUS CHECK METHODS
    // ===================================

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === UserStatus::SUSPENDED;
    }

    /**
     * Check if the user's email is verified.
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Check if the user's ID is verified.
     */
    public function isIdVerified(): bool
    {
        return $this->is_id_verified === true
            && $this->idVerifications()->approved()->exists();
    }

    /**
     * Check if the user has a pending ID verification request.
     */
    public function isIdPending(): bool
    {
        return !$this->isIdVerified()
            && $this->idVerifications()->pending()->exists();
    }

    /**
     * Check if ID verification is required for this user.
     */
    public function requiresIdVerification(): bool
    {
        return @settings('id_verification')?->status
            && @settings('id_verification')?->required
            && !$this->isIdVerified();
    }

    /**
     * Check if the user is a regular user (not a seller).
     */
    public function isUser(): bool
    {
        return $this->is_seller === false;
    }

    /**
     * Check if the user is a new user (created within the last month).
     *
     * @return bool True if user was created less than 1 month ago
     */
    public function isNewUser(): bool
    {
        return $this->created_at && $this->created_at->greaterThan(now()->subMonth());
    }

    /**
     * Check if the user is not seller & has purchased at least one product.
     *
     * @return bool True if user has at least one active purchase
     */
    public function isBuyer(): bool
    {
        return $this->isUser() && $this->purchases()->active()->exists();
    }

    /**
     * Check if the user is a seller.
     */
    public function isSeller(): bool
    {
        return $this->is_seller === true;
    }

    /**
     * Check if the user is a featured seller.
     */
    public function isFeaturedSeller(): bool
    {
        return $this->is_featured_seller === true;
    }

    /**
     * Check if the user is a seller or in the referral program.
     */
    public function isSellerOrInReferralProgram(): bool
    {
        return $this->isSeller() || $this->referral()->exists();
    }

    /**
     * Check if the user is an exclusive seller.
     */
    public function isExclusiveSeller(): bool
    {
        return $this->seller_type === SellerType::EXCLUSIVE;
    }

    /**
     * Check if the user has 2FA enabled.
     */
    public function has2fa(): bool
    {
        return $this->google2fa_status === true && !empty($this->google2fa_secret);
    }

    /**
     * Check if the user has a payout account configured.
     */
    public function hasPayoutAccount(): bool
    {
        return $this->payout_method_id && $this->payout_account;
    }

    /**
     * Check if the user's profile data is completed.
     */
    public function isDataCompleted(): bool
    {
        return filled([
            $this->firstname,
            $this->lastname,
            $this->username,
            $this->email,
            $this->password,
        ]);
    }

    /**
     * Check if the user is following another user.
     */
    public function isFollowingUser(int $userId): bool
    {
        return $this->followings()->where('following_id', $userId)->exists();
    }

    /**
     * Check if the user has purchased a specific product.
     */
    public function hasPurchasedProduct(int $productId): bool
    {
        return $this->purchases()->where('product_id', $productId)->active()->exists();
    }

    /**
     * Get the purchase record for a specific product.
     */
    public function getPurchaseRecord(int $productId): ?Purchase
    {
        if ($this->hasPurchasedProduct($productId)) {
            return $this->purchases()->where('product_id', $productId)->first();
        }
        return null;
    }

    /**
     * Check if a product is in the user's favorites.
     */
    public function hasProductInFavorite(int $productId): bool
    {
        return $this->favorites()->where('product_id', $productId)->exists();
    }

    /**
     * Check if the user has an active premium membership.
     */
    public function isPremiumMember(): bool
    {
        return $this->premium !== null;
    }

    /**
     * Check if the user is subscribed to a specific plan.
     */
    public function subscribedToPlan(int $planId): bool
    {
        return $this->premium && $this->premium->plan->id === $planId;
    }

    /**
     * Check if the user was previously a premium member.
     */
    public function hadPremiumMembership(): bool
    {
        return $this->had_premium === true;
    }

    /**
     * Check if the user has reviewed a specific product.
     */
    public function hasReviewedProduct(int $productId): bool
    {
        return $this->productReviews()->where('product_id', $productId)->exists();
    }

    /**
     * Check if the user is currently online.
     */
    public function isOnline(): bool
    {
        return $this->is_online
            || ($this->last_active_at && $this->last_active_at->gt(now()->subMinutes(5)));
    }

    /**
     * Check if the user is not blocked by admin to send messages.
     */
    public function restrictedToMessage(): bool
    {
        return $this->can_message === false;
    }

    /**
     * Check if a notification type and event is enabled for the user.
     */
    public function wantsNotification(string $type, string $event): bool
    {
        $groups = config('notifications.groups', []);

        $targetGroup = null;
        foreach ($groups as $groupKey => $groupData) {
            if (in_array($event, $groupData['events'] ?? [])) {
                $targetGroup = $groupKey;
                break;
            }
        }

        if (!$targetGroup) {
            return true;
        }

        $preferences = $this->notification_preferences ?? [];

        return $preferences[$type][$targetGroup] ?? true;
    }

    // ===================================
    // ATTRIBUTE ACCESSORS
    // ===================================

    /**
     * Get the role label (User/Seller).
     *
     * Access via: $user->role_label
     */
    protected function roleLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->is_seller ? 'Seller' : 'User',
        );
    }

    /**
     * Get the CSS class for the role badge.
     *
     * Access via: $user->role_badge_class
     */
    protected function roleBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->is_seller ? 'badge-primary' : 'badge-secondary',
        );
    }

    /**
     * Get the user's full name or username.
     *
     * Access via: $user->full_name
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

                return 'Unknown User';
            }
        );
    }

    /**
     * Get the user's country name.
     *
     * Access via: $user->country_name
     */
    protected function countryName(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => isset($this->address['country'])
                ? countries($this->address['country'])
                : null
        );
    }

    /**
     * Get the user's location (City, Country).
     *
     * Access via: $user->location
     */
    protected function location(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $city = $this->address['city'] ?? null;
                $country = isset($this->address['country'])
                    ? countries($this->address['country'])
                    : null;

                if ($city && $country) {
                    return "{$city}, {$country}";
                }

                if ($country) {
                    return $country;
                }

                if ($city) {
                    return $city;
                }

                return null;
            }
        );
    }

    /**
     * Get the user's status name (Active/Suspended).
     *
     * Access via: $user->status_name
     */

    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => @$this->status ? @$this->status->label() : 'Unknown'
        );
    }

    /**
     * Get the user's avatar URL.
     *
     * Access via: $user->avatar_url
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
     * Get the user's profile cover URL.
     *
     * Access via: $user->profile_cover_url
     */
    protected function profileCoverUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $cover = $this->basic_info['cover'] ?? null;

                if (!empty($cover) && is_string($cover)) {
                    return asset($cover);
                }

                return null;
            }
        );
    }

    /**
     * Get the user's profile link.
     *
     * Access via: $user->profile_link
     */
    protected function profileLink(): Attribute
    {
        return Attribute::make(
            get: fn(): string => route('profile.index', $this->id)
        );
    }

    /**
     * Get the user's store link.
     *
     * Access via: $user->store_link
     */
    protected function storeLink(): Attribute
    {
        return Attribute::make(
            get: fn(): string => route('profile.store', $this->id)
        );
    }

    /**
     * Get the user's referral link.
     *
     * Access via: $user->referral_link
     */
    protected function referralLink(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->username) {
                    return route('home', 'ref=' . strtolower($this->username));
                }
                return route('home');
            }
        );
    }

    /**
     * Get the user who referred this user.
     *
     * Access via: $user->referred_by_user
     */
    protected function referredByUser(): Attribute
    {
        return Attribute::make(
            get: fn(): ?User => $this->referred_by?->referring_user
        );
    }

    /**
     * Get the user's last active time.
     *
     * Access via: $user->last_active
     */
    protected function lastActive(): Attribute
    {
        return Attribute::make(
            get: fn(): Carbon => $this->last_active_at
        );
    }

    /**
     * Get the last active time in ISO format.
     *
     * Access via: $user->last_active_formatted
     */
    protected function lastActiveFormatted(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->last_active_at?->toISOString()
        );
    }

    /**
     * Get the total earnings from all sources.
     *  Calculates the grand total of:
     * - Sales earnings (total_sales_amount)
     * - Referral earnings (total_referrals_earnings)
     * - Support earnings (from support_earnings table)
     * - Premium earnings (from premium_earnings table)
     *  @return float Total earnings with current formatting
     */
    protected function totalEarnings(): Attribute
    {
        return Attribute::make(
            get: function () {

                $salesEarnings = (float) ($this->total_sales_amount ?? 0);
                $referralEarnings = (float) ($this->total_referrals_earnings ?? 0);

                $supportEarnings = (float) DB::table('support_earnings')
                    ->where('seller_id', $this->id)
                    ->sum('seller_earning');

                $premiumEarnings = (float) DB::table('premium_earnings')
                    ->where('seller_id', $this->id)
                    ->sum('seller_earning');

                return round(
                    $salesEarnings + $referralEarnings + $supportEarnings + $premiumEarnings,
                    2
                );
            }
        );
    }

    // ===================================
    // ACCESSORS & MUTATORS
    // ===================================

    /**
     * Decrypt the Google 2FA secret when accessing.
     */
    public function getGoogle2faSecretAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    // ===================================
    // USER ACTIONS
    // ===================================

    /**
     * Log user login activity.
     */
    public function logLoginActivity(): void
    {
        $ip = getIp();
        $ipLookup = app(GeolocationService::class)->lookup($ip);

        // Device detection using DeviceDetector Facade
        $detector = DeviceDetector::detectRequest(request());
        $client = $detector->getClient();
        $os = $detector->getOs();

        // Create a new login activity entry for each login in user_login_activities table
        DB::table('user_login_activities')->insert([
            'user_id' => $this->id,
            'ip' => $ip,
            'country' => $ipLookup->country,
            'country_code' => $ipLookup->country_code,
            'timezone' => $ipLookup->timezone,
            'location' => $ipLookup->location,
            'latitude' => $ipLookup->latitude,
            'longitude' => $ipLookup->longitude,
            'browser' => $client['name'] ?? 'Unknown',
            'browser_version' => $client['version'] ?? null,
            'os' => $os['name'] ?? 'Unknown',
            'os_version' => $os['version'] ?? null,
            'device_type' => $detector->getDeviceName() ?: 'desktop',
            'device_brand' => $detector->getBrandName() ?: null,
            'device_model' => $detector->getModel() ?: null,
            'is_bot' => $detector->isBot() ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Mark the user as offline.
     */
    public function markAsOffline(): void
    {
        $this->timestamps = false;
        $this->updateQuietly(['is_online' => false]);
    }

    /**
     * Empty the user's cart.
     */
    public function emptyCart(): void
    {
        $this->cartProducts()->delete();
    }

    // ===================================
    // RESOURCE CLEANUP
    // ===================================

    /**
     * Delete all resources associated with the user.
     */
    /**
     * Delete all user-related resources and files.
     *
     * Wrapped in a database transaction for data integrity.
     */
    public function deleteResources(): void
    {
        DB::transaction(function () {
            // Delete relationships
            $this->followers()->delete();
            $this->followings()->delete();
            $this->idVerifications()->delete();
            $this->productComments()->delete();
            $this->productReviews()->delete();
            $this->products()->delete();
            $this->transactions()->delete();
            $this->tickets()->delete();

            // Remove files (outside transaction since they're file operations)
            if ($this->avatar) {
                removeFile($this->avatar);
            }

            if (!empty($this->basic_info['cover'])) {
                removeFile($this->basic_info['cover']);
            }
        });
    }

    /**
     * @var bool Flag to prevent duplicate notification sending in the same request
     */
    protected static bool $emailNotificationSent = false;

    /**
     * Send the email verification notification using OTP via Notification facade.
     */
    public function sendEmailVerificationNotification(): void
    {
        // Prevent duplicate sending in the same request
        if (static::$emailNotificationSent) {
            return;
        }

        static::$emailNotificationSent = true;

        $otp = $this->generateEmailOtp();
        Notification::sendEmailVerificationNotification($this, $otp);
    }

    /**
     * Send the password reset notification using OTP via Notification facade.
     *
     * @param string|null $token
     */
    public function sendPasswordResetNotification($token = null): void
    {
        $otp = $this->generateEmailOtp();
        Notification::sendPasswordResetNotification($this, $otp, (string)$token);
    }

    /**
     * Send email change OTP notification via Notification facade.
     */
    public function sendEmailChangeNotification($email): void
    {
        $otp = $this->generateEmailOtp();
        Notification::sendEmailChangeNotification($this, $email, $otp);
    }

    // ===================================
    // RELATIONSHIPS
    // ===================================

    /**
     * Get the ID verifications for the user.
     */
    public function idVerifications(): HasMany
    {
        return $this->hasMany(IdVerification::class);
    }

    /**
     * Get the tickets created by the user.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the users who are following this user.
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'following_id');
    }

    /**
     * Get the users that this user is following.
     */
    public function followings(): HasMany
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    /**
     * Get the users that this user is following (many-to-many).
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Get the users who are following this user (many-to-many).
     */
    public function followedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Get all products created by the user.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Get active products created by the user.
     */
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id')
            ->whereNotIn('status', [
                ProductStatus::PENDING->value,
                ProductStatus::REJECTED->value,
            ]);
    }

    /**
     * Get the product comments created by the user.
     */
    public function productComments(): HasMany
    {
        return $this->hasMany(ProductComment::class);
    }

    /**
     * Get the product reviews created by the user.
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get the cart products for the user.
     */
    public function cartProducts(): HasMany
    {
        return $this->hasMany(CartProduct::class);
    }

    /**
     * Get the badges earned by the user.
     */
    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get the purchases made by the user.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the user's level.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(SellerLevel::class);
    }

    /**
     * Get the admin who deleted this user.
     * Returns null if user deleted themselves.
     */
    public function deletedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'deleted_by');
    }

    /**
     * Get the referrals made by this user (as seller).
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'seller_id');
    }

    /**
     * Get the referral record for this user (as referred user).
     */
    public function referral(): HasOne
    {
        return $this->hasOne(Referral::class, 'user_id');
    }

    /**
     * Get the user who referred this user.
     */
    public function referred_by(): HasOne
    {
        return $this->hasOne(Referral::class, 'user_id');
    }

    /**
     * Get the payout method for the user.
     */
    public function payoutMethod(): BelongsTo
    {
        return $this->belongsTo(PayoutMethod::class, 'payout_method_id');
    }

    /**
     * Get the payouts for the user.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'seller_id');
    }

    /**
     * Get the reviews on the user's products.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'seller_id');
    }

    /**
     * Get the user's favorite products.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the refunds for the user.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get the refunds for the user as a seller.
     */
    public function refundsAsSeller(): HasMany
    {
        return $this->hasMany(Refund::class, 'seller_id');
    }

    /**
     * Get the user's active premium membership.
     */
    public function premium(): HasOne
    {
        return $this->hasOne(Premium::class);
    }

    /**
     * Get all premium memberships for the user.
     */
    public function premiums(): HasMany
    {
        return $this->hasMany(Premium::class);
    }
}
