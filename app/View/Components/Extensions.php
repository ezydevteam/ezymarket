<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Extensions - Third-Party Integrations Component
 *
 * Laravel Blade component that handles global third-party integrations and
 * compliance features that need to be present on every page.
 *
 * Responsibilities:
 * - GDPR Cookie Consent Banner (compliance)
 * - Google Analytics Integration (tracking)
 * - Tawk.to Live Chat Widget (support)
 *
 * Features:
 * - Conditional rendering based on settings/extensions
 * - Cookie consent with customizable policy link
 * - Analytics tracking with GTM data layer
 * - Live chat widget with async loading
 *
 * Usage:
 * ```blade
 * {{-- In layout/scripts include --}}
 * <x-extensions />
 * ```
 *
 * View File:
 * - Location: resources/views/themes/main/components/partials.blade.php
 * - Contains: GDPR banner, Google Analytics script, Tawk.to chat widget
 *
 * @package App\View\Components
 * @author Codebay Team
 * @version 1.0.0
 */
class Extensions extends Component
{
    /**
     * Render the third-party scripts component
     *
     * Returns the view containing all third-party integrations:
     * - GDPR cookie consent banner
     * - Google Analytics tracking code
     * - Tawk.to live chat widget
     *
     * @return View The component view
     */
    public function render(): View
    {
        return theme_view('components.extensions');
    }
}


















