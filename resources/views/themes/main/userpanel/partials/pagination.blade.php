@if ($items->total() > 0)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
        <div class="order-lg-2">
            {{ $items->links() }}
        </div>
        <div class="order-lg-1 small">
            {{ translate('Showing') }}
            <span class="fw-semibold text-dark">{{ $items->firstItem() ?? 0 }}</span>
            {{ translate('to') }}
            <span class="fw-semibold text-dark">{{ $items->lastItem() ?? 0 }}</span>
            {{ translate('of') }}
            <span class="fw-semibold text-dark">{{ $items->total() }}</span>
            {{ translate('totals') }}
        </div>
    </div>
@endif
