{{-- User Activity Timeline --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3 border-bottom-dashed pb-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('User Activity Timeline') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Recent performance and interactions across the platform') }}</p>
                </div>
            </div>
            <div class="d-none d-sm-block">
                <span class="badge bg-light text-muted fw-normal ls-1 px-3 py-2 border rounded-pill small">
                    {{ translate('Last 1 Year') }}
                </span>
            </div>
        </div>

        @if($activities->isEmpty())
        <div class="py-5 text-center bg-light-subtle rounded-4 border border-dashed">
            <div class="icon-circle icon-circle-lg bg-light text-muted mx-auto mb-3">
                <i class="bi bi-clock-history fs-3"></i>
            </div>
            <h6 class="text-muted fw-bold mb-1">{{ translate('No Activities Found') }}</h6>
            <p class="text-muted small mb-0">{{ translate('There is no notable activity recorded for this user in the last month.') }}</p>
        </div>
        @else
        <div class="modern-timeline ps-4 border-start">
            @foreach($activities as $activity)
            <div class="timeline-item position-relative pb-5">
                <span class="position-absolute start-0 top-0 translate-middle-x icon-circle icon-circle-xs bg-{{ $activity['color'] }} shadow-{{ $activity['color'] }}-subtle text-white" style="margin-left: -24px;">
                    <i class="{{ $activity['icon'] }} small"></i>
                </span>
                <div class="timeline-body ps-3">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                        <h6 class="fw-bold text-dark mb-0">{{ $activity['title'] }}</h6>
                        <span class="text-muted small-8 fw-medium">{{ timeAgo($activity['time']) }}</span>
                    </div>
                    <p class="text-muted mb-3 fs-6-5">{{ $activity['description'] }}</p>

                    @if($activity['meta'])
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($activity['meta'] as $key => $value)
                        <div class="px-2 py-1 bg-light-subtle border rounded-2 small-7 fw-bold text-secondary uppercase ls-1">
                            <span class="text-muted fw-normal me-1">{{ $key }}:</span>
                            {{ $value }}
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($activities->hasPages())
        <div class="mt-4 d-flex justify-content-center ajax-pagination">
            {{ $activities->links() }}
        </div>
        @endif
    </div>
</div>
