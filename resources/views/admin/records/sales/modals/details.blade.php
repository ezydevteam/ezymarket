<x-modal :title="translate('Sale Details')" icon="bi-receipt" :scrollable="true" :content-only="true"
    id="saleDetailsContent">

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">
                    {{ translate('Sale Record') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $sale->id }}</h4>
            </div>
            <div class="text-end">
                <div class="mb-2">
                    <span class="status-badge {{ $sale->status_badge_class }}">
                        {{ $sale->status_name }}
                    </span>
                </div>
                <div>
                    <span class="status-badge {{ $sale->license_type_badge_class }}">
                        {{ $sale->license_type_name }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($sale->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Transaction') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-receipt me-1 text-primary"></i>
                        #{{ $sale->transaction_id }}
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
                <div class="p-3 border rounded-3 bg-white hover-shadow transition-all">
                    <x-user :user="$sale->user" avatarSize="sm" />
                </div>
            </div>
            <div class="col-6">
                <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                    <i class="bi bi-shop me-1"></i>{{ translate('Seller') }}
                </h6>
                <div class="p-3 border rounded-3 bg-white hover-shadow transition-all">
                    <x-user :user="$sale->seller" avatarSize="sm" />
                </div>
            </div>
        </div>

        {{-- Product Info & Financial Summary --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-list-check me-1"></i>{{ translate('Financial Summary') }}
        </h6>
        <div class="bg-light p-3 rounded-3 border">
            <div class="d-flex justify-content-between align-items-start mb-3 last-child-mb-0">
                <div class="d-flex gap-2">
                    <div class="img-fluid img-fluid-md bg-white rounded p-2 border shadow-xs flex-shrink-0">
                        <img src="{{ $sale->product?->thumbnail_url }}" alt="{{ $sale->product?->name }}">
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-14 mb-0">{{ $sale->product?->name }}</div>
                        <small class="text-muted">
                            {{ $sale->license_type_name }} &bull; {{ getAmount($sale->price) }}
                        </small>
                    </div>
                </div>
                <div class="fw-bold text-dark">{{ getAmount($sale->price) }}</div>
            </div>

            <hr class="my-3 opacity-10">

            {{-- Calculations --}}
            <div class="space-y-2">
                <div class="d-flex justify-content-between text-gray-800 fs-14">
                    <span>{{ translate('Gross Sale Price') }}</span>
                    <span>{{ getAmount($sale->price) }}</span>
                </div>
                <div class="d-flex justify-content-between text-gray-800 fs-14">
                    <span>{{ translate('Platform Fees') }}</span>
                    <span class="text-danger">- {{ getAmount($sale->buyer_fee + $sale->seller_fee) }}</span>
                </div>
                @if ($sale->seller_tax)
                <div class="d-flex justify-content-between text-gray-800 fs-14">
                    <span>{{ $sale->seller_tax->name }} ({{ $sale->seller_tax->rate }}%)</span>
                    <span class="text-danger">- {{ getAmount($sale->seller_tax->amount) }}</span>
                </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="fw-bold text-dark fs-16">{{ translate('Seller Net Earnings') }}</span>
                    <span class="fw-bold text-primary fs-4">{{ getAmount($sale->seller_earning) }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            {{ translate('Dismiss') }}
        </button>
        @if ($sale->purchase)
        <a href="{{ route('admin.records.purchases.index', ['id' => $sale->purchase->id]) }}"
            class="btn btn-primary flex-fill">
            <i class="bi bi-receipt me-2"></i>{{ translate('View Purchase') }}
        </a>
        @endif
    </x-slot>
</x-modal>
