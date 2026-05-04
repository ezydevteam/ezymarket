<?php

namespace App\Enums\Product;

enum ProductReportReason: string
{
    case COPYRIGHT = 'copyright';
    case INAPPROPRIATE = 'inappropriate';
    case MISLEADING = 'misleading';
    case QUALITY = 'quality';
    case SPAM = 'spam';
    case OFFENSIVE = 'offensive';
    case MALWARE = 'malware';
    case OTHER = 'other';

    /**
     * Get the label for the reason
     */
    public function label(): string
    {
        return match ($this) {
            self::COPYRIGHT => translate('Copyright Infringement'),
            self::INAPPROPRIATE => translate('Inappropriate Content'),
            self::MISLEADING => translate('Misleading Information'),
            self::QUALITY => translate('Poor Quality'),
            self::SPAM => translate('Spam or Scam'),
            self::OFFENSIVE => translate('Offensive Content'),
            self::MALWARE => translate('Contains Malware'),
            self::OTHER => translate('Other'),
        };
    }

    /**
     * Get the badge class for the reason
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::COPYRIGHT => 'text-danger',
            self::INAPPROPRIATE => 'text-secondary',
            self::MISLEADING => 'text-info',
            self::QUALITY => 'text-orange',
            self::SPAM => 'text-danger',
            self::OFFENSIVE => 'text-dark',
            self::MALWARE => 'text-danger',
            self::OTHER => 'text-orange',
        };
    }

    /**
     * Get the badge HTML for the reason
     */
    public function badge(): string
    {
        return '<span class="' . $this->badgeClass() . '">' . $this->label() . '</span>';
    }

    /**
     * Get the short label for the reason (used in compact displays)
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::COPYRIGHT => translate('Copyright'),
            self::INAPPROPRIATE => translate('Inappropriate'),
            self::MISLEADING => translate('Misleading'),
            self::QUALITY => translate('Poor Quality'),
            self::SPAM => translate('Spam'),
            self::OFFENSIVE => translate('Offensive'),
            self::MALWARE => translate('Malware'),
            self::OTHER => translate('Other'),
        };
    }

    /**
     * Check if the reason is critical (requires immediate attention)
     */
    public function isCritical(): bool
    {
        return in_array($this, [self::COPYRIGHT, self::MALWARE, self::SPAM]);
    }

    /**
     * Get all reason labels as an array
     */
    public static function labels(): array
    {
        return [
            self::COPYRIGHT->value => self::COPYRIGHT->label(),
            self::INAPPROPRIATE->value => self::INAPPROPRIATE->label(),
            self::MISLEADING->value => self::MISLEADING->label(),
            self::QUALITY->value => self::QUALITY->label(),
            self::SPAM->value => self::SPAM->label(),
            self::OFFENSIVE->value => self::OFFENSIVE->label(),
            self::MALWARE->value => self::MALWARE->label(),
            self::OTHER->value => self::OTHER->label(),
        ];
    }
}
