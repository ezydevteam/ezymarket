<?php

declare(strict_types=1);

namespace App\Enums\Product;

/**
 * Product Status Enum
 *
 * Defines the lifecycle states of a product from submission to approval/rejection.
 *
 * @package App\Enums\Product
 */
enum ProductStatus: string
{
    /**
     * Product is a draft (not yet submitted)
     */
    case DRAFT = 'draft';

    /**
     * Product submitted and awaiting review
     */
    case PENDING = 'pending';

    /**
     * Product rejected but can be resubmitted after fixes
     */
    case NEEDS_REVISION = 'needs_revision';

    /**
     * Product resubmitted after revision request
     */
    case RESUBMITTED = 'resubmitted';

    /**
     * Product approved and live on marketplace
     */
    case APPROVED = 'approved';

    /**
     * Product permanently rejected (cannot be resubmitted)
     */
    case REJECTED = 'rejected';

    /**
     * Product temporarily restricted (hidden from marketplace)
     */
    case RESTRICTED = 'restricted';

    /**
     * Get all available status options
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::DRAFT->value => translate('Draft'),
            self::PENDING->value => translate('Pending'),
            self::NEEDS_REVISION->value => translate('Needs Revision'),
            self::RESUBMITTED->value => translate('Resubmitted'),
            self::APPROVED->value => translate('Approved'),
            self::REJECTED->value => translate('Rejected'),
            self::RESTRICTED->value => translate('Restricted'),
        ];
    }

    /**
     * Get the translated label for the status
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => translate('Draft'),
            self::PENDING => translate('Pending'),
            self::NEEDS_REVISION => translate('Needs Revision'),
            self::RESUBMITTED => translate('Resubmitted'),
            self::APPROVED => translate('Approved'),
            self::REJECTED => translate('Rejected'),
            self::RESTRICTED => translate('Restricted'),
        };
    }

    /**
     * Get the badge class for the status
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-dark-subtle text-dark',
            self::PENDING => 'bg-warning-subtle text-warning',
            self::NEEDS_REVISION => 'bg-info-subtle text-info',
            self::RESUBMITTED => 'bg-primary-subtle text-primary',
            self::APPROVED => 'bg-success-subtle text-success',
            self::REJECTED => 'bg-danger-subtle text-danger',
            self::RESTRICTED => 'bg-secondary-subtle text-secondary',
        };
    }

    /**
     * Get the icon for the status
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'bi-pencil-square',
            self::PENDING => 'bi-hourglass-split',
            self::NEEDS_REVISION => 'bi-exclamation-circle',
            self::RESUBMITTED => 'bi-redo',
            self::APPROVED => 'bi-check-circle',
            self::REJECTED => 'bi-times-circle',
            self::RESTRICTED => 'bi-ban',
        };
    }

    /**
     * Get all status labels (alias for options)
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::DRAFT->value => translate('Draft'),
            self::PENDING->value => translate('Pending'),
            self::NEEDS_REVISION->value => translate('Needs Revision'),
            self::RESUBMITTED->value => translate('Resubmitted'),
            self::APPROVED->value => translate('Approved'),
            self::REJECTED->value => translate('Rejected'),
            self::RESTRICTED->value => translate('Restricted'),
        ];
    }
}
