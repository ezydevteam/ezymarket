@if ($changelogs->count() > 0)
<div class="row row-cols-1 g-3">
    @foreach ($changelogs as $changelog)
    <div class="col">
        <div class="changelogs">
            <div class="mb-2">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <p class="fw-medium fs-6 mb-0">
                            <i class="bi bi-arrow-repeat me-1"></i>{{ translate(':version',
                            ['version' => $changelog->version]) }}
                        </p>
                    </div>
                    <div class="col-auto text-gray-700">
                        <small><i class="bi bi-clock-history me-1"></i>{{ dateFormat($changelog->created_at) }}</small>
                    </div>
                </div>
            </div>
            <pre>{{ $changelog->log }}</pre>
        </div>
    </div>
    @endforeach
</div>
{{ $changelogs->links() }}
@else
<div class="card-v card-bg text-center py-5">
    <i class="bi bi-arrow-repeat d-block mb-4 fs-2 text-muted"></i>
    <p class="mb-0">{{ translate('This product has no changelogs') }}</p>
</div>
@endif
