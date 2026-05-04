<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active Theme
    |--------------------------------------------------------------------------
    |
    | This option determines the active theme for your application's frontend.
    | The theme name should match a directory in the public/themes/ folder.
    |
    | Example: If set to 'main', the system will look for public/themes/main/
    |
    | You can change the default theme by setting DEFAULT_THEME in your .env file.
    |
    | Controlled by: DEFAULT_THEME in .env file
    | Default: 'main'
    |
     */

    'active' => env('DEFAULT_THEME', 'main'),

    /*
    |--------------------------------------------------------------------------
    | Theme Stylesheets
    |--------------------------------------------------------------------------
    |
    | Define the paths to theme-specific stylesheets.
    | These paths are relative to: public/themes/{active-theme}/
    |
    | - colors: Color scheme/palette CSS file
    | - custom_css: Additional custom CSS for theme modifications
    |
    | Example full paths:
    | - public/themes/main/assets/css/colors.css
    | - public/themes/main/assets/css/custom.css
    |
     */

    'style' => [
        'colors' => 'assets/css/colors.css',
        'custom_css' => 'assets/css/custom.css',
    ],

];



















