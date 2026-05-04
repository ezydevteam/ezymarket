<x-modal :title="translate('Support Earning Details')" icon="bi-life-preserver" :scrollable="true" :content-only="true"
    id="earningDetailsContent">

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">
                    {{ translate('Earning Record') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $earning->id }}</h4>
            </div>
            <div class="text-end">
                <div class="mb-2">
                    <span class="status-badge {{ $earning->status->badgeClass() }}">
                        {{ $earning->status->label() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Earning Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($earning->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Purchase ID') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-bag-check me-1 text-primary"></i>
                        #{{ $earning->purchase_id }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3">
        {{-- Seller Information --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-shop me-1"></i>{{ translate('Seller Information') }}
        </h6>
        <div class="p-3 border rounded-3 bg-white mb-4">
            <x-user :user="$earning->seller" avatarSize="sm" />
        </div>

        {{-- Product Info & Earning Summary --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-list-check me-1"></i>{{ translate('Related Product') }}
        </h6>
        <div class="bg-light p-3 rounded-3 border">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <div class="bg-white rounded p-2 border shadow-xs flex-shrink-0">
                        <img src="{{ $earning->purchase?->product?->thumbnail_url }}"
                            alt="{{ $earning->purchase?->product?->name }}" class="image-fluid image-md">
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-14 mb-0">{{ $earning->purchase?->product?->name }}</div>
                        <small class="text-muted">{{ translate('Support Extension/Renewal') }}</small>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted small mb-1">{{ translate('Seller Earning') }}</div>
                    <div class="fw-bold text-primary fs-16">{{ getAmount($earning->seller_earning) }}</div>
                </div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            {{ translate('Dismiss') }}
        </button>
        <a href="{{ route('admin.records.purchases.index', ['id' => $earning->purchase_id]) }}"
            class="btn btn-primary flex-fill">
            <i class="bi bi-bag-check me-2"></i>{{ translate('View Purchase') }}
        </a>
        </x-slot>
</x-modal>
