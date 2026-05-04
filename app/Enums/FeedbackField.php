<?php

namespace App\Enums;

enum FeedbackField: string
{
    case TECHNICAL = 'technical';
    case UI_UX = 'ui_ux';
    case FEATURE_REQUEST = 'feature_request';
    case BUG_REPORT = 'bug_report';
    case CONTENT = 'content';
    case PERFORMANCE = 'performance';
    case SECURITY = 'security';
    case REQUEST_PRODUCT = 'request-product';
    case OTHER = 'other';

    /**
     * Get the human-readable label for the field.
     */
    public function label(): string
    {
        return match($this) {
            self::TECHNICAL => translate('Technical Issue'),
            self::UI_UX => translate('UI/UX Problem'),
            self::FEATURE_REQUEST => translate('Feature Request'),
            self::BUG_REPORT => translate('Bug Report'),
            self::CONTENT => translate('Content Issue'),
            self::PERFORMANCE => translate('Performance Issue'),
            self::SECURITY => translate('Security Concern'),
            self::REQUEST_PRODUCT => translate('Request a Missing product'),
            self::OTHER => translate('Other'),
        };
    }

    /**
     * Get the badge CSS class for the field.
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::TECHNICAL => 'text-danger',
            self::UI_UX => 'text-orange',
            self::FEATURE_REQUEST => 'text-success',
            self::BUG_REPORT => 'text-danger',
            self::CONTENT => 'text-primary',
            self::PERFORMANCE => 'text-info',
            self::SECURITY => 'text-danger',
            self::REQUEST_PRODUCT => 'text-info',
            self::OTHER => 'text-secondary',
        };
    }

    /**
     * Get the full HTML badge for the field.
     */
    public function badge(): string
    {
        return '<span class="' . $this->badgeClass() . '">' . $this->label() . '</span>';
    }

    /**
     * Get all field labels as an associative array.
     */
    public static function labels(): array
    {
        return [
            self::TECHNICAL->value => self::TECHNICAL->label(),
            self::UI_UX->value => self::UI_UX->label(),
            self::FEATURE_REQUEST->value => self::FEATURE_REQUEST->label(),
            self::BUG_REPORT->value => self::BUG_REPORT->label(),
            self::CONTENT->value => self::CONTENT->label(),
            self::PERFORMANCE->value => self::PERFORMANCE->label(),
            self::SECURITY->value => self::SECURITY->label(),
            self::REQUEST_PRODUCT->value => self::REQUEST_PRODUCT->label(),
            self::OTHER->value => self::OTHER->label(),
        ];
    }
}
