<x-modal :title="translate('Referral Earning Details')" icon="bi-people" :scrollable="true" :content-only="true"
    id="referralDetailsContent">

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">
                    {{ translate('Referral Record') }}
                </span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $earning->id }}</h4>
            </div>
            <div class="text-end">
                <div class="d-flex flex-column align-items-end gap-1">
                    <span class="status-badge {{ $earning->status->badgeClass() }}">
                        {{ $earning->status->label() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Earning & Quick Stats --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Transaction Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($earning->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Referral ID') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-person-badge me-1 text-primary"></i>
                        #{{ $earning->referral_id }}
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="px-3 py-2 border rounded bg-white shadow-sm">
                    <small class="text-muted d-block mb-1">{{ translate('Earning Amount') }}</small>
                    <div class="fw-bold text-primary fs-18">
                        {{ getAmount((float) $earning->seller_earning) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3">
        {{-- Parties Information --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                    <i class="bi bi-person-fill me-1"></i>{{ translate('Referred User') }}
                </h6>
                <div class="p-3 border rounded-3 bg-white">
                    <x-user :user="$earning->referral?->user" avatarSize="sm" />
                </div>
            </div>
            <div class="col-12">
                <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                    <i class="bi bi-megaphone me-1"></i>{{ translate('Referred By') }}
                </h6>
                <div class="p-3 border rounded-3 bg-white">
                    <x-user :user="$earning->seller" avatarSize="sm" />
                </div>
            </div>
        </div>

        {{-- Product Info (From Sale) --}}
        @if($earning->sale)
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                <i class="bi bi-bag-check me-1"></i>{{ translate('Related Sale & Product') }}
            </h6>
            <div class="bg-light p-3 rounded-3 border">
                <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-0 border-bottom border-dashed">
                    <div class="d-flex gap-2">
                        <div class="bg-white rounded p-2 border shadow-xs">
                            <img src="{{ $earning->sale?->product?->thumbnail_url }}" alt="{{ $earning->sale?->product?->name }}"
                                class="image-fluid image-md">
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-14 mb-0">{{ $earning->sale?->product?->name }}</div>
                            <small class="text-muted">
                                {{ $earning->sale?->license_type_name }} &bull; {{ getAmount((float) $earning->sale?->price) }}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-dark fs-16">{{ getAmount((float) $earning->sale?->price) }}</div>
                        <small class="text-muted d-block">{{ translate('Sale Total') }}</small>
                    </div>
                </div>

                <div class="row g-2 small text-muted">
                    <div class="col-6">
                        <i class="bi bi-receipt me-1"></i>{{ translate('Transaction ID') }}:
                        <span class="text-dark fw-medium">#{{ $earning->sale?->transaction_id }}</span>
                    </div>
                    <div class="col-6 text-end">
                        <i class="bi bi-shield-check me-1"></i>{{ translate('Sale Status') }}:
                        <span class="badge {{ $earning->sale?->status_badge_class }} fs-10 py-1">{{ $earning->sale?->status_name }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            {{ translate('Dismiss') }}
        </button>
        @if ($earning->sale)
            <a href="{{ route('admin.records.sales.index', ['id' => $earning->sale_id]) }}"
                class="btn btn-primary flex-fill" target="_blank">
                <i class="bi bi-receipt me-2"></i>{{ translate('View Sale') }}
            </a>
        @endif
    </x-slot>
</x-modal>
