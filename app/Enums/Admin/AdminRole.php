<?php

declare(strict_types=1);

namespace App\Enums\Admin;

/**
 * Admin Role Enumeration
 *
 * Defines all available admin panel roles with their permissions level.
 */
enum AdminRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case ACCOUNTANT = 'accountant';
    case REVIEWER = 'reviewer';

    /**
     * Get the human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => translate('Admin'),
            self::MANAGER => translate('Manager'),
            self::ACCOUNTANT => translate('Accountant'),
            self::REVIEWER => translate('Product Reviewer'),
        };
    }

    /**
     * Get the description for the role.
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => translate('Full system access and control'),
            self::MANAGER => translate('Manage everything except admin & staff members'),
            self::ACCOUNTANT => translate('View and manage analytics & finance'),
            self::REVIEWER => translate('Review and manage product submissions'),
        };
    }

    /**
     * Get the color for the role.
     */
    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'primary',
            self::MANAGER => 'primary',
            self::ACCOUNTANT => 'info',
            self::REVIEWER => 'orange',
        };
    }

    /**
     * Get the landing page for the role.
     */
    public function landingPage(): string
    {
        return match ($this) {
            self::ADMIN => '/admin/dashboard',
            self::MANAGER => '/admin/dashboard',
            self::ACCOUNTANT => '/admin/analytics/sales',
            self::REVIEWER => '/admin/products',
        };
    }

    /**
     * Check if role needs category assignment.
     */
    public function needsCategoryAssignment(): bool
    {
        return $this === self::REVIEWER;
    }

    /**
     * Get all available roles.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $role) {
            $options[$role->value] = $role->label();
        }
        return $options;
    }

    /**
     * Get roles that can be assigned by the current role.
     *
     * @return array<AdminRole>
     */
    public function assignableRoles(): array
    {
        return match ($this) {
            self::ADMIN => self::cases(),
            default => [],
        };
    }
}
