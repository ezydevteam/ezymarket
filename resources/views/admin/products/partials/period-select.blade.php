<select id="period-select" class="form-select border-secondary selectpicker w-auto" data-size="6">
    @foreach (getAvailablePeriods($product) as $periodOption)
        @if ($periodOption['key'] === 'separator')
            <option disabled>{{ $periodOption['value'] }}</option>
        @else
            <option value="{{ url()->current() . '?tab=statistics&period=' . $periodOption['key'] }}"
                @selected($currentPeriod == $periodOption['key'])>
                {{ $periodOption['value'] }}
            </option>
        @endif
    @endforeach
</select>
