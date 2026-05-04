<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Statement Type Enum
 *
 * Defines the transaction types for financial statements.
 * Credit represents money added to balance, Debit represents money deducted.
 *
 * @package App\Enums
 */
enum StatementType: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';

    /**
     * Get all available options as an associative array
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::CREDIT->value => self::CREDIT->label(),
            self::DEBIT->value => self::DEBIT->label(),
        ];
    }

    /**
     * Get the human-readable label for the statement type
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'Credit',
            self::DEBIT => 'Debit',
        };
    }

    /**
     * Get the Bootstrap color class for the statement type
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::CREDIT => 'success',
            self::DEBIT => 'danger',
        };
    }

    /**
     * Get the Bootstrap badge class for the statement type
     *
     * @return string
     */
    public function badge(): string
    {
        return match ($this) {
            self::CREDIT => 'bg-success-subtle text-success',
            self::DEBIT => 'bg-danger-subtle text-danger',
        };
    }

    /**
     * Get the Bootstrap icon class for the statement type
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::CREDIT => 'bi-plus-circle',
            self::DEBIT => 'bi-dash-circle',
        };
    }

    /**
     * Get the sign prefix for the statement type
     *
     * @return string
     */
    public function sign(): string
    {
        return match ($this) {
            self::CREDIT => '+',
            self::DEBIT => '-',
        };
    }
}
