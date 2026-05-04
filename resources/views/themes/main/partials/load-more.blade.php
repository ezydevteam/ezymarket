@if ($items->hasMorePages())
    <div class="text-center mt-5">
        <button class="btn {{ $class ?? 'btn-outline-primary' }} rounded-pill px-4 load-more-btn"
            data-url="{{ $items->nextPageUrl() }}"
            data-target="{{ $target }}">
            <span class="load-more-icon me-2"><i class="bi {{ $icon ?? 'bi-arrow-repeat' }}"></i></span>
            <span class="load-more-text">{{ translate($label ?? 'Load More') }}</span>
            <span class="load-more-loader d-none">
                <span class="spinner-border spinner-border-sm" role="status"></span>
                <span class="ms-2">{{ translate('Loading...') }}</span>
            </span>
        </button>
    </div>
@endif
