@props([
    'class' => 'card border-0 shadow-sm rounded-4 my-5',
    'titleClass' => 'fw-semibold mb-2',
    'size' => 'col-lg-8 mx-auto',
    'title' => null,
    'desc' => null,
    'icon' => 'bi-search',
    'iconColor' => 'primary',
    'btnText' => null,
    'btnLink' => null,
    'btnIcon' => 'bi-plus-lg',
    'btnClass' => 'btn btn-outline-secondary btn-padding',
    'btnModal' => null,
    'btnModalText' => null,
    'btnModalClass' => 'btn btn-primary btn-padding',
    'btnModalAction' => null,
])

<div class="empty-card {{ $class }} {{ $size }}">
    <div class="card-body text-center py-lg-5">
        <div class="text-{{ $iconColor }} fs-1 mb-3">
            <i class="bi {{ $icon }}"></i>
        </div>
        <h5 class="{{ $titleClass }}">{{ translate( $title ?? 'No Data Found!') }}</h5>
        @if ($desc)
            <p class="text-muted mb-0">
                {{ translate( $desc ?? "It seems that the section is empty or your search didn't fetch any results") }}
            </p>
        @endif
        @if($btnText || $btnLink || $btnModal)
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
                @if($btnModal)
                    <button type="button" class="{{ $btnModalClass }}" data-bs-toggle="modal"
                        data-bs-target="{{ $btnModal }}" {{ $btnModalAction ? 'data-action="' . $btnModalAction . '"' : '' }}>
                        <i class="{{ $btnIcon }} me-2"></i>{{ translate($btnModalText) }}
                    </button>
                @elseif($btnLink)
                    <a href="{{ $btnLink }}" class="{{ $btnClass }}">
                        <i class="{{ $btnIcon }} me-2"></i>{{ translate($btnText) }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
