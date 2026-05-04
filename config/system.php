<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vendor/Seller Information
    |--------------------------------------------------------------------------
    |
    | This section defines the details of the application vendor/seller.
    | Used for licensing, support contact, and about pages.
    |
    | - name: The vendor's company or individual name
    | - email: Support email address
    | - website: Official vendor website URL
    | - profile: Marketplace profile URL (e.g., CodeCanyon, ThemeForest)
    |
     */

    'author' => [
        'name' => 'Codebay',
        'email' => 'support@code-bay.net',
        'website' => 'https://code-bay.net',
        'profile' => 'https://codecanyon.net/user/codebay',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Information
    |--------------------------------------------------------------------------
    |
    | Define core information about the application product.
    |
    | - alias: Product name/alias used in branding
    | - version: Current version number (Semantic Versioning recommended)
    |
     */

    'product' => [
        'alias' => 'EasyMarket',
        'version' => '1.0.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    |
    | Enable or disable demo mode for the system.
    | When enabled, certain destructive actions (delete, update sensitive data)
    | will be blocked. Useful for public demos or testing environments.
    |
    | Controlled by: SYSTEM_DEMO_MODE in .env file
    | Default: false (disabled)
    |
     */

    'demo_mode' => env('SYSTEM_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | License Configuration
    |--------------------------------------------------------------------------
    |
    | Configure license validation settings for the application.
    |
    | - api: License validation API endpoint
    | - type: License type (1 = Regular License, 2 = Extended License)
    |
    | Controlled by: SYSTEM_LICENSE_TYPE in .env file
    | Default: 1 (Regular License)
    |
     */

    'license' => [
        'api' => 'http://em.code-bay.net/api/v1/license',
        'type' => env('SYSTEM_LICENSE_TYPE', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Installation Step Flags
    |--------------------------------------------------------------------------
    |
    | Track the progress of the application installation process.
    | Each flag should be set to true/1 once the corresponding step is completed.
    |
    | Installation Steps:
    | 1. requirements: Server requirements check
    | 2. file_permissions: File/directory permissions verification
    | 3. license: License key validation
    | 4. database_info: Database connection configuration
    | 5. database_import: Database schema and data import
    | 6. complete: Installation fully completed
    |
    | All flags are controlled by their respective INSTALL_* variables in .env
    | Default: false (not completed)
    |
     */

    'install' => [
        'requirements' => env('INSTALL_REQUIREMENTS', false),
        'file_permissions' => env('INSTALL_FILE_PERMISSIONS', false),
        'license' => env('INSTALL_LICENSE', false),
        'database_info' => env('INSTALL_DATABASE_INFO', false),
        'database_import' => env('INSTALL_DATABASE_IMPORT', false),
        'complete' => env('INSTALL_COMPLETE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Configure settings for the admin dashboard/panel.
    | All staff roles (admin, manager, accountant, reviewer) use this panel.
    |
    | - path: URL path to access admin panel (e.g., 'admin' => /admin)
    | - roles: Available admin roles with their configurations
    | - colors: Path to admin panel color scheme CSS file (relative to public/)
    | - custom_css: Path to admin custom CSS file for additional styling
    |
    | The 'path' is controlled by: SYSTEM_ADMIN_PATH in .env file
    | Default path: 'admin'
    |
     */

    'admin' => [
        'path' => env('SYSTEM_ADMIN_PATH', 'admin'),

        'roles' => [
            'admin' => [
                'label' => 'Administrator',
                'description' => 'Full system access and control',
                'landing' => '/admin/dashboard',
            ],
            'manager' => [
                'label' => 'Manager',
                'description' => 'Manage everything except admin & staff members',
                'landing' => '/admin/dashboard',
            ],
            'accountant' => [
                'label' => 'Accountant',
                'description' => 'View and manage finances and analytics',
                'landing' => '/admin/analytics/sales',
            ],
            'reviewer' => [
                'label' => 'Product Reviewer',
                'description' => 'Review and manage product submissions',
                'landing' => '/admin/products',
            ],
        ],

        'colors' => 'vendor/admin/css/colors.css',
        'fonts' => 'vendor/admin/css/fonts.css',
        'custom_css' => 'vendor/admin/css/custom.css',
    ],

];


















