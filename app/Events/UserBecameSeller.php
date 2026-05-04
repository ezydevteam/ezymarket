<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use App\Enums\User\SellerType;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a user becomes a seller.
 *
 * This event is dispatched when a user's is_seller status changes to SELLER.
 * Listeners can handle sending welcome emails, setting up seller dashboard, etc.
 */
class UserBecameSeller
{
    use SerializesModels;

    /**
     * The user who became a seller.
     *
     * @var \App\Models\User
     */
    public User $user;

    /**
     * The seller type (exclusive or non-exclusive).
     *
     * @var \App\Enums\User\SellerType|null
     */
    public ?SellerType $sellerType;

    /**
     * Whether this is an upgrade to exclusive.
     *
     * @var bool
     */
    public bool $isExclusiveUpgrade;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user The user who became a seller
     * @param \App\Enums\User\SellerType|null $sellerType The seller type
     * @param bool $isExclusiveUpgrade Whether this is an upgrade to exclusive
     */
    public function __construct(User $user, ?SellerType $sellerType = null, bool $isExclusiveUpgrade = false)
    {
        $this->user = $user;
        $this->sellerType = $sellerType ?? $user->seller_type;
        $this->isExclusiveUpgrade = $isExclusiveUpgrade;
    }
}
