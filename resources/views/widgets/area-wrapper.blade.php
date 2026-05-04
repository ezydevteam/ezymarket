<div class="widget-area widget-area-{{ $area->value }}"
    data-area="{{ $area->value }}">
    @if ($content)
        {!! $content !!}
    @else
        <p class="text-dark mb-0">{{ translate('No widget set yet.') }}</p>
    @endif
</div>
