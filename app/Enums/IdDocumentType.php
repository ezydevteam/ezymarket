<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * ID Document Type Enum
 *
 * Defines all accepted document types for identity verification.
 * Used for document type validation and display.
 *
 * @package App\Enums
 */
enum IdDocumentType: string
{
    case NATIONAL_ID = 'national_id';
    case PASSPORT = 'passport';

    /**
     * Get a human-readable label for the document type.
     */
    public function label(): string
    {
        return match ($this) {
            self::NATIONAL_ID => translate('National ID'),
            self::PASSPORT => translate('Passport'),
        };
    }

    /**
     * Get the icon class for the document type.
     */
    public function icon(): string
    {
        return match ($this) {
            self::NATIONAL_ID => 'bi-person-vcard',
            self::PASSPORT => 'bi-passport',
        };
    }

    /**
     * Get a description for the document type.
     */
    public function description(): string
    {
        return match ($this) {
            self::NATIONAL_ID => translate('Government-issued national identity card'),
            self::PASSPORT => translate('International travel passport document'),
        };
    }

    /**
     * Get all document types as an array of values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all document types as an associative array (value => label).
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Try to create an instance from a string value.
     */
    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }
}
