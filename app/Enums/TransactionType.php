<?php

namespace App\Enums;

enum TransactionType: string
{
    case PURCHASE = 'purchase';
    case SUPPORT_PURCHASE = 'support_purchase';
    case SUPPORT_EXTEND = 'support_extend';
    case DEPOSIT = 'deposit';
    case PREMIUM = 'premium';

    /**
     * Get the label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => translate('Purchase'),
            self::SUPPORT_PURCHASE => translate('Support Purchase'),
            self::SUPPORT_EXTEND => translate('Support Extend'),
            self::DEPOSIT => translate('Deposit'),
            self::PREMIUM => translate('Premium'),
        };
    }

    /**
     * Get the badge color for the type.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PURCHASE => 'text-primary',
            self::SUPPORT_PURCHASE => 'text-info',
            self::SUPPORT_EXTEND => 'text-purple',
            self::DEPOSIT => 'text-orange',
            self::PREMIUM => 'text-success',
        };
    }

    /**
     * Get formatted badge HTML.
     */
    public function badge(): string
    {
        return '<div class="' . $this->badgeClass() . '">' . $this->label() . '</div>';
    }

    /**
     * Get all type labels.
     */
    public static function labels(): array
    {
        $data = [
            self::PURCHASE->value => self::PURCHASE->label(),
            self::SUPPORT_PURCHASE->value => self::SUPPORT_PURCHASE->label(),
            self::SUPPORT_EXTEND->value => self::SUPPORT_EXTEND->label(),
            self::DEPOSIT->value => self::DEPOSIT->label(),
        ];

        if (get_license_type(2) && @settings('premium')->status) {
            $data[self::PREMIUM->value] = self::PREMIUM->label();
        }

        return $data;
    }
}
