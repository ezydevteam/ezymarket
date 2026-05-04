<?php

declare(strict_types=1);

namespace App\Enums\Product;

/**
 * Product History Title Enum
 *
 * Defines the types of history events for a product.
 */
enum ProductHistoryTitle: string
{
    case SUBMISSION = 'submission';
    case TRUST_SUBMISSION = 'trust_submission';
    case SUBMISSION_APPROVED = 'submission_approved';
    case RESUBMISSION = 'resubmission';
    case RESUBMISSION_APPROVED = 'resubmission_approved';
    case REVISION_REQUIRED = 'revision_required';
    case REJECTION = 'rejection';
    case UPDATE_SUBMISSION = 'update_submission';
    case TRUST_UPDATE = 'trust_update';
    case UPDATE_APPROVED = 'update_approved';
    case UPDATE_REJECTED = 'update_rejected';

    /**
     * Get the professional, translated label for the history event.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUBMISSION => translate('Product submitted for review'),
            self::TRUST_SUBMISSION => translate('Product automatically approved (Auto submission enabled)'),
            self::SUBMISSION_APPROVED => translate('Product approved and is now live'),
            self::RESUBMISSION => translate('Product resubmitted for review'),
            self::RESUBMISSION_APPROVED => translate('Resubmission approved and product is now live'),
            self::REVISION_REQUIRED => translate('Revision required for this product'),
            self::REJECTION => translate('Product permanently rejected'),
            self::UPDATE_SUBMISSION => translate('Update submitted for review'),
            self::TRUST_UPDATE => translate('Update automatically approved (Auto update enabled)'),
            self::UPDATE_APPROVED => translate('Update approved and changes are live'),
            self::UPDATE_REJECTED => translate('Update rejected'),
        };
    }

    /**
     * Get the icon for the history event.
     */
    public function icon(): string
    {
        return match ($this) {
            self::SUBMISSION, self::RESUBMISSION => 'bi-send',
            self::TRUST_SUBMISSION, self::TRUST_UPDATE => 'bi-shield-check',
            self::SUBMISSION_APPROVED, self::RESUBMISSION_APPROVED, self::UPDATE_APPROVED => 'bi-check-circle-fill',
            self::REVISION_REQUIRED => 'bi-exclamation-triangle-fill',
            self::REJECTION, self::UPDATE_REJECTED => 'bi-x-circle-fill',
            self::UPDATE_SUBMISSION => 'bi-arrow-repeat',
        };
    }

    /**
     * Get the badge class for the history event.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::SUBMISSION_APPROVED, self::RESUBMISSION_APPROVED, self::UPDATE_APPROVED => 'bg-success-subtle text-success',
            self::REVISION_REQUIRED => 'bg-warning-subtle text-warning',
            self::REJECTION, self::UPDATE_REJECTED => 'bg-danger-subtle text-danger',
            self::TRUST_SUBMISSION, self::TRUST_UPDATE => 'bg-info-subtle text-info',
            default => 'bg-primary-subtle text-primary',
        };
    }

    /**
     * Get the full HTML badge for the history event.
     */
    public function badgeHTML(): string
    {
        return sprintf(
            '<span class="badge %s px-2 py-1 rounded-pill small fw-medium text-uppercase letter-spacing-1"><i class="bi %s me-1"></i> %s</span>',
            $this->badgeClass(),
            $this->icon(),
            $this->label()
        );
    }
}
