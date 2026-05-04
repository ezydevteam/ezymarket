{{-- Calendar Widget --}}
@php
    // Standard positioning and padding logic
    $cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
    $titleStyle = $widgetSettings['title_style'] ?? 'default';
    $titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3');
    $contentPadding = ($cardStyle === 'none') ? 'p-0' : (in_array($titleStyle, ['style_1', 'style_2']) ? 'p-3' : 'p-0');
@endphp
<div class="widget-calendar {{ $titlePadding }}">
    @include('widgets.partials.widget-title', [
        'title' => $title ?? '',
        'widgetSettings' => $widgetSettings ?? []
    ])
    <div class="widget-content {{ $contentPadding }}">
        <div class="calendar-widget">
            <div class="calendar-header text-center mb-3">
                <h6 class="fw-bold mb-0">{{ $calendar['currentMonth'] }}</h6>
            </div>
            <table class="table table-sm table-borderless calendar-table mb-0">
                <thead>
                    <tr class="text-center text-muted small">
                        @if($calendar['startOnMonday'])
                            <th>{{ translate('Mo') }}</th>
                            <th>{{ translate('Tu') }}</th>
                            <th>{{ translate('We') }}</th>
                            <th>{{ translate('Th') }}</th>
                            <th>{{ translate('Fr') }}</th>
                            <th>{{ translate('Sa') }}</th>
                            <th>{{ translate('Su') }}</th>
                        @else
                            <th>{{ translate('Su') }}</th>
                            <th>{{ translate('Mo') }}</th>
                            <th>{{ translate('Tu') }}</th>
                            <th>{{ translate('We') }}</th>
                            <th>{{ translate('Th') }}</th>
                            <th>{{ translate('Fr') }}</th>
                            <th>{{ translate('Sa') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $day = 1;
                        $totalDays = $calendar['daysInMonth'];
                        $firstDay = $calendar['firstDayOfWeek'];
                        $highlightToday = $widgetSettings['highlight_today'] ?? true;
                        $today = $calendar['today'];
                    @endphp
                    @for($week = 0; $week < 6; $week++)
                        @if($day <= $totalDays)
                            <tr class="text-center">
                                @for($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++)
                                    @if(($week === 0 && $dayOfWeek < $firstDay) || $day > $totalDays)
                                        <td></td>
                                    @else
                                        <td class="{{ ($highlightToday && $day === $today) ? 'bg-primary text-white rounded-2' : '' }}">
                                            {{ $day }}
                                        </td>
                                        @php $day++; @endphp
                                    @endif
                                @endfor
                            </tr>
                        @endif
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
