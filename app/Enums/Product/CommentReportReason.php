<?php

namespace App\Enums\Product;

enum CommentReportReason: string
{
    case SPAM = 'spam';
    case HARASSMENT = 'harassment';
    case HATE_SPEECH = 'hate_speech';
    case MISINFORMATION = 'misinformation';
    case INAPPROPRIATE = 'inappropriate';
    case OFF_TOPIC = 'off_topic';
    case SELF_PROMOTION = 'self_promotion';
    case LINK_SHARING = 'link_sharing';
    case OTHER = 'other';

    /**
     * Get the label for the reason
     */
    public function label(): string
    {
        return match ($this) {
            self::SPAM => translate('Spam'),
            self::HARASSMENT => translate('Harassment or Bullying'),
            self::HATE_SPEECH => translate('Hate Speech'),
            self::MISINFORMATION => translate('Misinformation'),
            self::INAPPROPRIATE => translate('Inappropriate Content'),
            self::OFF_TOPIC => translate('Off-Topic'),
            self::SELF_PROMOTION => translate('Self Promotion'),
            self::LINK_SHARING => translate('Link Sharing'),
            self::OTHER => translate('Other'),
        };
    }

    /**
     * Get the badge class for the reason
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::SPAM => 'text-danger',
            self::HARASSMENT => 'text-danger',
            self::HATE_SPEECH => 'text-danger',
            self::MISINFORMATION => 'text-info',
            self::INAPPROPRIATE => 'text-secondary',
            self::OFF_TOPIC => 'text-muted',
            self::SELF_PROMOTION => 'text-orange',
            self::LINK_SHARING => 'text-primary',
            self::OTHER => 'text-dark',
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
     * Get all labels as array
     */
    public static function labels(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
