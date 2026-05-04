@forelse ($productChangelogs as $changelog)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-gray-900">{{ translate('Version :version', ['version' => $changelog->version]) }}</h6>
                        <small class="text-muted">{{ dateFormat($changelog->created_at) }}</small>
                    </div>
                </div>
                <span class="badge bg-light text-dark border">{{ timeAgo($changelog->created_at) }}</span>
            </div>

            <div class="changelog-body">
                <code class="text-gray-700">{!! sanitizeHtml($changelog->log, true) !!}</code>
            </div>
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <x-empty :message="translate('No changelogs found for this product.')" :size="'lg'" :icon="'bi-journal-x'" />
        </div>
    </div>
@endforelse

@if ($productChangelogs->hasPages())
    <div class="mt-4 ajax-pagination">
        {{ $productChangelogs->appends(['tab' => 'changelogs'])->links() }}
    </div>
@endif
