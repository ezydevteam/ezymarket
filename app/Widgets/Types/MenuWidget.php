<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\{Menu, WidgetInstance};
use App\Enums\Menu\MenuLocation;

/**
 * Menu Widget
 *
 * Display a navigation menu.
 */
class MenuWidget extends BaseWidget
{
    protected string $slug = 'menu';
    protected string $title = 'Menu';
    protected string $description = 'Display a navigation menu';
    protected string $icon = 'bi bi-list';
    protected string $view = 'widgets.types.menu';
    protected string $settingsView = 'admin.appearance.widgets.settings.menu';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'menu_location' => MenuLocation::FOOTER->value,
            'show_title' => true,
            'style' => 'style-1',
            'menu_class' => '',
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        $locations = [];
        foreach (MenuLocation::cases() as $location) {
            $locations[$location->value] = $location->label();
        }

        return [
             [
                'name' => 'style',
                'type' => 'select',
                'label' => translate('Display Style'),
                'options' => [
                    'style-1' => translate('Classic'),
                    'style-2' => translate('Modern'),
                ],
                'default' => 'style-1',
            ],
            [
                'name' => 'menu_location',
                'type' => 'select',
                'label' => translate('Select Menu'),
                'options' => $locations,
                'required' => true,
            ],
            [
                'name' => 'menu_class',
                'type' => 'text',
                'label' => translate('Custom CSS Class'),
                'placeholder' => 'my-custom-class',
                'required' => false,
            ],
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Show Widget Title'),
                'default' => true,
            ],
        ];
    }

    /**
     * Render the widget output.
     */
    public function render(WidgetInstance $instance): string
    {
        $settings = $instance->settings ?? $this->getDefaultSettings();
        $menuLocation = $settings['menu_location'] ?? MenuLocation::FOOTER->value;

        $menuItems = Menu::where('location', $menuLocation)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order_id')
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->orderBy('order_id');
            }])
            ->get();

        return view($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'settings' => $settings,
            'title' => $instance->display_title,
            'menuItems' => $menuItems,
        ])->render();
    }
}
