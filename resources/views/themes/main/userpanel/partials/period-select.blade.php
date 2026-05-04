@php $periods = getAvailablePeriods($entity ?? authUser()); @endphp
<div class="period-select">
    <select id="period-select" class="form-select selectpicker" data-size="6">
        @foreach ($periods as $period)
            @if($period['key'] == 'separator')
                <option disabled>{{ $period['value'] }}</option>
            @else
                <option value="{{ url()->current() . '?period=' . $period['key'] }}" @selected($currentPeriod == $period['key'])>
                    {{ $period['value'] }}
                </option>
            @endif
        @endforeach
    </select>
</div>
