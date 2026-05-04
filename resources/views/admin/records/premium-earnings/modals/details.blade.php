<x-modal :title="translate('Premium Earning Details')" icon="bi-award" :scrollable="true" :content-only="true"
    id="premiumDetailsContent">

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">
                    {{ translate('Premium Earning Record') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $earning->id }}</h4>
            </div>
            <div class="text-end">
                <span class="badge bg-primary-subtle text-primary py-2 px-3 fs-12">
                    <i class="bi bi-star-fill me-1"></i>{{ $earning->premium?->plan?->name ?? translate('Premium Plan') }}
                </span>
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Distribution Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($earning->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Commission') }}</small>
                    <div class="fw-semibold text-dark fs-14 text-uppercase">
                        <i class="bi bi-percent me-1 text-primary"></i>
                        {{ $earning->percentage }}%
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="px-3 py-3 border rounded bg-primary-subtle shadow-sm border-primary-subtle">
                    <div class="row align-items-center">
                        <div class="col-7">
                            <small class="text-muted d-block mb-1">{{ translate('Seller Earning') }}</small>
                            <div class="h3 fw-bold text-primary mb-0">{{ getAmount((float) $earning->seller_earning) }}</div>
                        </div>
                        <div class="col-5 text-end">
                            <small class="text-muted d-block mb-1">{{ translate('Package Price') }}</small>
                            <div class="fw-bold text-dark fs-16">{{ getAmount((float) $earning->price) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3">
        {{-- Seller Information --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-person-fill me-1"></i>{{ translate('Seller Information') }}
        </h6>
        <div class="p-3 border rounded-3 bg-white mb-4">
            <x-user :user="$earning->seller" avatarSize="sm" />
        </div>

        {{-- Product Info --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-box-seam me-1"></i>{{ translate('Associated Product') }}
        </h6>
        <div class="bg-light p-3 rounded-3 border">
            <div class="d-flex gap-3">
                <div class="bg-white rounded p-2 border shadow-xs">
                    <img src="{{ $earning->product?->thumbnail_url }}" alt="{{ $earning->product?->name }}"
                        class="image-fluid image-md rounded">
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark fs-15 mb-1">{{ $earning->product?->name }}</div>
                    <div class="d-flex gap-3 small text-muted">
                        <span><i class="bi bi-tag me-1"></i>{{ $earning->product?->category?->name }}</span>
                        <span><i class="bi bi-upc-scan me-1"></i>ID: #{{ $earning->product_id }}</span>
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.products.show', $earning->product_id) }}"
                       class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                        <i class="bi bi-eye me-1"></i>{{ translate('View') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            {{ translate('Dismiss') }}
        </button>
        <a href="{{ route('admin.roles.users.edit', $earning->seller_id) }}"
            class="btn btn-primary flex-fill" target="_blank">
            <i class="bi bi-person-gear me-2"></i>{{ translate('Seller Profile') }}
        </a>
    </x-slot>
</x-modal>
