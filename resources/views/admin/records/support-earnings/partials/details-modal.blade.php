<x-modal
    id="supportEarningDetailsModal-{{ $supportEarning->id }}"
    :title="translate('Support Earning Details')"
    icon="bi-life-preserver"
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
                    <span>#{{ $supportEarning->id }}</span>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-receipt me-2"></i>
                    <strong>{{ translate('Purchase') }}</strong>
                </div>
                <div class="col-auto">
                    @if($supportEarning->purchase)
                        <a href="{{ route('admin.records.purchases.index', ['purchase' => $supportEarning->purchase->id]) }}" class="text-dark hover-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            #{{ $supportEarning->purchase->id }}
                        </a>
                    @else
                        <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
        @if($supportEarning->purchase && $supportEarning->purchase->product)
            <div class="list-group-item px-0 py-3">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <i class="bi bi-box me-2"></i>
                        <strong>{{ translate('Product') }}</strong>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.products.show', $supportEarning->purchase->product->id) }}" class="text-dark hover-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            {{ $supportEarning->purchase->product->name }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-person-badge me-2"></i>
                    <strong>{{ translate('Name') }}</strong>
                </div>
                <div class="col-auto">
                    <span>{{ $supportEarning->name }}</span>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-card-heading me-2"></i>
                    <strong>{{ translate('Title') }}</strong>
                </div>
                <div class="col-auto">
                    <span>{{ $supportEarning->title }}</span>
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
                    @if($supportEarning->seller)
                        <a href="{{ route('admin.roles.users.edit', $supportEarning->seller->id) }}" class="text-dark hover-primary" target="_blank">
                            {{ $supportEarning->seller->full_name }}
                            @if($supportEarning->seller->trashed())
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
                    <i class="bi bi-currency-dollar me-2"></i>
                    <strong>{{ translate('Price') }}</strong>
                </div>
                <div class="col-auto">
                    <strong>{{ getAmount($supportEarning->price) }}</strong>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-wallet2 me-2"></i>
                    <strong>{{ translate('Seller Earning') }}</strong>
                </div>
                <div class="col-auto">
                    <strong class="text-success">{{ getAmount($supportEarning->seller_earning) }}</strong>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>{{ translate('Status') }}</strong>
                </div>
                <div class="col-auto">
                    <span class="badge {{ $supportEarning->status->badgeClass() }}">
                        <i class="bi {{ $supportEarning->status->icon() }} me-1"></i>
                        {{ $supportEarning->status->label() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-calendar-check me-2"></i>
                    <strong>{{ translate('Support Expiry') }}</strong>
                </div>
                <div class="col-auto">
                    <span>{{ $supportEarning->support_expiry_at ? dateFormat($supportEarning->support_expiry_at) : '--' }}</span>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 pt-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-calendar3 me-2"></i>
                    <strong>{{ translate('Created Date') }}</strong>
                </div>
                <div class="col-auto">
                    <span>{{ dateFormat($supportEarning->created_at) }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">{{ translate('Close') }}</button>
        @if($supportEarning->purchase)
            <a class="btn btn-outline-primary flex-fill"
                href="{{ route('admin.records.purchases.index', ['purchase' => $supportEarning->purchase->id]) }}" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i>
                {{ translate('View Purchase') }}
            </a>
        @endif
    </x-slot>
</x-modal>
