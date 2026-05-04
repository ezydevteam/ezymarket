@props([
    'title',
    'count',
    'percent' => null,
    'icon',
    'color' => 'primary',
    'link' => null,
    'comparisonText' => translate('vs last week')
])

<div class="stats-metric-card text-{{ $color }} h-100">
    @if($link)
    <a href="{{ $link }}" class="text-reset text-decoration-none h-100 d-block">
    @endif
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="text-uppercase mb-0 text-truncate fs-13 fw-semibold text-muted">{{ $title }}</h5>
            <div class="icon-circle icon-circle-md bg-{{ $color }}-subtle text-{{ $color }}">
                <i class="bi {{ $icon }} fs-4"></i>
            </div>
        </div>
        <h2 class="mb-0 fw-bold text-dark">{{ $count }}</h2>
        
        @if($percent !== null)
            <div class="stats-comparison mt-2 d-flex align-items-center gap-1 fs-14 fw-semibold {{ $percent > 0 ? 'text-success' : ($percent < 0 ? 'text-danger' : 'text-muted') }}">
                <i class="bi {{ $percent > 0 ? 'bi-arrow-up-short' : ($percent < 0 ? 'bi-arrow-down-short' : 'bi-dash') }} fs-5"></i>
                <span>{{ abs($percent) }}%</span>
                <span class="text-muted text-lowercase fw-normal ms-1 small">{{ $comparisonText }}</span>
            </div>
        @endif
    @if($link)
    </a>
    @endif
</div>
