<x-modal :title="translate('Purchase Details')" icon="bi-bag-check" :scrollable="true" :content-only="true"
    id="purchaseDetailsContent">

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">
                    {{ translate('Purchase Record') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $purchase->id }}</h4>
            </div>
            <div class="text-end">
                <div class="d-flex flex-column align-items-end gap-1">
                    <span class="status-badge {{ $purchase->status_badge_class }}">
                        {{ $purchase->status_name }}
                    </span>
                    <span class="status-badge {{ $purchase->license_type_badge_class }}">
                        {{ $purchase->license_type_name }}
                    </span>
                </div>
            </div>
        </div>

        {{-- License & Quick Stats --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Purchase Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($purchase->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Transaction') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-receipt me-1 text-primary"></i>
                        #{{ $purchase->sale?->transaction_id }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Support Expiry') }}</small>
                    <div class="fw-semibold {{ $purchase->isSupportExpired() ? 'text-danger' : 'text-dark' }} fs-14">
                        <i
                            class="bi bi-shield-check me-1 {{ $purchase->isSupportExpired() ? 'text-danger' : 'text-primary' }}"></i>
                        {{ $purchase->support_expiry_at ? dateFormat($purchase->support_expiry_at) : translate('No
                        Support') }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Download Status') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        @if($purchase->is_downloaded)
                        <span class="status-badge bg-success-subtle text-success">
                            <i class="bi bi-cloud-check me-1"></i>{{ translate('Downloaded') }}
                        </span>
                        @else
                        <span class="status-badge bg-light text-muted">
                            <i class="bi bi-cloud me-1"></i>{{ translate('Not Downloaded') }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="px-3 py-2 border rounded bg-white shadow-sm">
                    <small class="text-muted d-block mb-1">{{ translate('Purchase Code') }}</small>
                    <div class="fw-semibold text-dark fs-14 d-flex align-items-center">
                        <i class="bi bi-key me-1 text-primary"></i>
                        <span class="text-truncate" id="purchaseCode-{{ $purchase->id }}">{{ $purchase->code }}</span>
                        <button type="button" class="btn-copy ms-auto border-0 bg-transparent p-0 text-primary"
                            data-clipboard-target="#purchaseCode-{{ $purchase->id }}"
                            title="{{ translate('Click to Copy') }}">
                            <i class="bi bi-clipboard fs-14"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3">
        {{-- Parties Information --}}
        <div class="row g-4 mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                    <i class="bi bi-person-fill me-1"></i>{{ translate('Buyer') }}
                </h6>
                <div class="p-3 border rounded-3 bg-white">
                    <x-user :user="$purchase->user" avatarSize="sm" />
                </div>
            </div>
            <div class="col-6">
                <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                    <i class="bi bi-shop me-1"></i>{{ translate('Seller') }}
                </h6>
                <div class="p-3 border rounded-3 bg-white">
                    <x-user :user="$purchase->sale?->seller" avatarSize="sm" />
                </div>
            </div>
        </div>

        {{-- Product Info & Financial Summary --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-list-check me-1"></i>{{ translate('Product Summary') }}
        </h6>
        <div class="bg-light p-3 rounded-3 border">
            <div class="d-flex justify-content-between align-items-start mb-0">
                <div class="d-flex gap-2">
                    <div class="bg-white rounded p-2 border shadow-xs">
                        <img src="{{ $purchase->product?->thumbnail_url }}" alt="{{ $purchase->product?->name }}"
                            class="image-fluid image-md">
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-14 mb-0">{{ $purchase->product?->name }}</div>
                        <small class="text-muted">
                            {{ $purchase->sale?->license_type_name }} &bull; {{ getAmount($purchase->sale?->price) }}
                        </small>
                    </div>
                </div>
                <div class="fw-bold text-dark fs-16">{{ getAmount($purchase->sale?->price) }}</div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            {{ translate('Dismiss') }}
        </button>
        @if ($purchase->sale)
        <a href="{{ route('admin.records.sales.index', ['id' => $purchase->sale_id]) }}"
            class="btn btn-primary flex-fill">
            <i class="bi bi-receipt me-2"></i>{{ translate('View Sale') }}
        </a>
        @endif
        </x-slot>
</x-modal>
