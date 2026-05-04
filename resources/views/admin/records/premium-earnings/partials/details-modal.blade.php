<x-modal
    id="premiumEarningDetailsModal-{{ $earning->id }}"
    :title="translate('Premium Earning Details')"
    icon="bi-wallet2"
    size="md"
    scrollable="true"
    autoOpen="true"
>
    <div class="list-group list-group-flush">
        <div class="list-group-item px-0 pb-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-hash me-2"></i>
                    <strong>{{ translate('Earning ID') }}</strong>
                </div>
                <div class="col-auto">
                    <span>#{{ $earning->id }}</span>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-box me-2"></i>
                    <strong>{{ translate('Product') }}</strong>
                </div>
                <div class="col-auto">
                    @if($earning->product)
                        <a href="{{ route('admin.products.show', $earning->product->id) }}" class="text-dark hover-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            {{ $earning->product->name }}
                        </a>
                    @else
                        <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
         <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-currency-dollar me-2"></i>
                    <strong>{{ translate('Product Price') }}</strong>
                </div>
                <div class="col-auto">
                    <strong>{{ getAmount($earning->price) }}</strong>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-shop me-2"></i>
                    <strong>{{ translate('Seller') }}</strong>
                </div>
                <div class="col-auto">
                    @if($earning->seller)
                        <a href="{{ route('admin.roles.users.edit', $earning->seller->id) }}" class="text-dark hover-primary" target="_blank">
                            {{ $earning->seller->full_name }}
                            @if($earning->seller->trashed())
                                <span class="badge bg-danger ms-1">{{ translate('Deleted') }}</span>
                            @endif
                        </a>
                    @else
                        <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-award me-2"></i>
                    <strong>{{ translate('Premium Plan') }}</strong>
                </div>
                <div class="col-auto">
                    @if($earning->premium && $earning->premium->plan)
                        <a href="{{ route('admin.premium.plans.index', ['plan' => $earning->premium->plan->id]) }}"
                            class="text-primary"
                            target="_blank">
                            {{ $earning->premium->plan->name }} ({{ $earning->premium->plan->interval_name }})
                        </a>
                    @else
                        <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-cash me-2"></i>
                    <strong>{{ translate('Plan Price') }}</strong>
                </div>
                <div class="col-auto">
                    @if($earning->premium && $earning->premium->plan)
                        <span class="text-dark fw-medium">
                            {{ getAmount($earning->premium->plan->price) }}
                        </span>
                    @else
                        <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-download me-2"></i>
                    <strong>{{ translate('Download Limit') }}</strong>
                </div>
                <div class="col-auto">
                    @if($earning->premium && $earning->premium->plan)
                        <span class="text-dark fw-medium">
                            {{ $earning->premium->plan->download_label }}
                        </span>
                    @else
                        <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-cash-coin me-2"></i>
                    <strong>{{ translate('Seller Earning') }}</strong>
                </div>
                <div class="col-auto">
                    <strong class="text-success">{{ getAmount($earning->seller_earning) }}</strong> ({{ $earning->percentage }}%)
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 pt-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-calendar3 me-2"></i>
                    <strong>{{ translate('Date') }}</strong>
                </div>
                <div class="col-auto">
                    <span>{{ dateFormat($earning->created_at) }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button"
            class="btn btn-cancel flex-fill"
            data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>
            {{ translate('Close') }}
        </button>
        <button data-action="{{ route('admin.records.premium-earnings.destroy', $earning->id) }}"
            class="btn btn-danger action-confirm flex-fill"
            data-method="DELETE"
            data-confirm="{{ translate('Are you sure to delete this record? This action cannot be undone.') }}">
            <i class="bi bi-trash me-2"></i>
            {{ translate('Delete') }}
        </button>
    </x-slot>
</x-modal>
