<x-modal
    id="purchaseDetailsModal-{{ $purchase->id }}"
    :title="translate('Purchase Details')"
    icon="bi-receipt"
    size="md"
    scrollable="true"
    autoOpen="true"
>
    <div class="list-group list-group-flush">
        <div class="list-group-item px-0 pb-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-hash me-2"></i>
                    <strong>{{ translate('Purchase ID') }}</strong>
                </div>
                <div class="col-auto">
                    <span>#{{ $purchase->id }}</span>
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
                    @if($purchase->product)
                        <a href="{{ route('admin.products.show', $purchase->product->id) }}" class="text-dark hover-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            {{ $purchase->product->name }}
                        </a>
                    @else
                        <span class="text-muted">{{ translate('Product Deleted') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-award me-2"></i>
                    <strong>{{ translate('License Type') }}</strong>
                </div>
                <div class="col-auto">
                    <span class="badge {{ $purchase->license_type_badge_class }}">
                        <i class="bi {{ $purchase->license_type_icon }} me-1"></i>
                        {{ $purchase->license_type_short_name }}
                    </span>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-key me-2"></i>
                    <strong>{{ translate('License Code') }}</strong>
                </div>
                <div class="col-auto">
                    <code>{{ $purchase->code }}</code>
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
                    @if($purchase->seller)
                        <a href="{{ route('admin.roles.users.edit', $purchase->seller->id) }}" class="text-dark hover-primary" target="_blank">
                            {{ $purchase->seller->full_name }}
                            @if($purchase->seller->trashed())
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
                    <i class="bi bi-cart me-2"></i>
                    <strong>{{ translate('Buyer') }}</strong>
                </div>
                <div class="col-auto">
                    @if($purchase->user)
                        <a href="{{ route('admin.roles.users.edit', $purchase->user->id) }}" class="text-dark hover-primary" target="_blank">
                            {{ $purchase->user->full_name }}
                            @if($purchase->user->trashed())
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
                    <strong>{{ getAmount($purchase->sale->price ?? 0) }}</strong>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-download me-2"></i>
                    <strong>{{ translate('Downloaded') }}</strong>
                </div>
                <div class="col-auto">
                    @if($purchase->is_downloaded)
                        <span class="badge bg-success">{{ translate('Yes') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ translate('No') }}</span>
                    @endif
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
                    <span class="badge {{ $purchase->status_badge_class }}">
                        <i class="bi {{ $purchase->status_icon }} me-1"></i>
                        {{ $purchase->status_name }}
                    </span>
                </div>
            </div>
        </div>
        @if ($settings->product->support_status && $purchase->support_expiry_at)
            <div class="list-group-item px-0 py-3">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <i class="bi bi-life-preserver me-2"></i>
                        <strong>{{ translate('Support Expiry') }}</strong>
                    </div>
                    <div class="col-auto">
                        <span>{{ dateFormat($purchase->support_expiry_at) }}</span>
                    </div>
                </div>
            </div>
        @endif
        <div class="list-group-item px-0 pt-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-calendar3 me-2"></i>
                    <strong>{{ translate('Purchase Date') }}</strong>
                </div>
                <div class="col-auto">
                    <span>{{ dateFormat($purchase->created_at) }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">{{ translate('Close') }}</button>
        @if($purchase->sale)
            <a class="btn btn-outline-primary flex-fill"
                href="{{ route('admin.records.sales.index', ['sale' => $purchase->sale->id]) }}" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i>
                {{ translate('View Sale') }}
            </a>
        @endif
    </x-slot>
</x-modal>
