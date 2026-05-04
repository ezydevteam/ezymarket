<?php

namespace App\Widgets\Types;

use App\Widgets\BaseWidget;
use App\Models\Appearance\WidgetInstance;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

/**
 * Calendar Widget
 *
 * Displays a simple calendar for the current month.
 */
class CalendarWidget extends BaseWidget
{
    protected string $slug = 'calendar';
    protected string $title = 'Calendar';
    protected string $description = 'Displays a monthly calendar';
    protected string $icon = 'bi bi-calendar3';
    protected string $view = 'widgets.types.calendar';

    /**
     * Get the default settings for this widget.
     */
    public function getDefaultSettings(): array
    {
        return [
            'show_title' => true,
            'highlight_today' => true,
            'start_on_monday' => false,
        ];
    }

    /**
     * Get the settings form fields configuration.
     */
    public function getSettingsFields(): array
    {
        return [
            [
                'name' => 'show_title',
                'type' => 'checkbox',
                'label' => translate('Show Widget Title'),
                'default' => true,
            ],
            [
                'name' => 'highlight_today',
                'type' => 'checkbox',
                'label' => translate('Highlight Today'),
                'default' => true,
            ],
            [
                'name' => 'start_on_monday',
                'type' => 'checkbox',
                'label' => translate('Start Week on Monday'),
                'default' => false,
            ],
        ];
    }

    /**
     * Render the widget output.
     */
    public function render(WidgetInstance $instance): string
    {
        $widgetSettings = $instance->settings ?? $this->getDefaultSettings();
        if (is_object($widgetSettings)) {
            $widgetSettings = (array) $widgetSettings;
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $startOnMonday = $widgetSettings['start_on_monday'] ?? false;

        // Get day of week for first day (0 = Sunday, 1 = Monday, etc.)
        $firstDayOfWeek = $startOfMonth->dayOfWeek;

        // Adjust for Monday start if needed
        if ($startOnMonday) {
            $firstDayOfWeek = $firstDayOfWeek === 0 ? 6 : $firstDayOfWeek - 1;
        }

        $calendarData = [
            'currentMonth' => $now->format('F Y'),
            'today' => $now->day,
            'daysInMonth' => $endOfMonth->day,
            'firstDayOfWeek' => $firstDayOfWeek,
            'startOnMonday' => $startOnMonday,
        ];

        return View::make($this->view, [
            'widget' => $this,
            'instance' => $instance,
            'widgetSettings' => $widgetSettings,
            'title' => $instance->display_title,
            'calendar' => $calendarData,
        ])->render();
    }
}
