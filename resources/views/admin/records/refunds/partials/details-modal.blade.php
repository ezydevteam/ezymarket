<x-modal
    id="refundDetailsModal-{{ $refund->id }}"
    :title="translate('Refund Details')"
    icon="bi-arrow-return-left"
    size="md"
    scrollable="true"
    autoOpen="true"
>
    <x-archived-alert :model="$refund" 
        :restoreRoute="route('admin.records.refunds.restore', $refund->id)" 
        :deleteRoute="route('admin.records.refunds.destroy', $refund->id)" />
    <div class="list-group list-group-flush">
        <div class="list-group-item px-0 pb-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-hash me-2"></i>
                    <strong>{{ translate('Refund ID') }}</strong>
                </div>
                <div class="col-auto">
                    <span>#{{ $refund->id }}</span>
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
                    @if($refund->purchase)
                        <a href="{{ route('admin.records.purchases.index', ['purchase' => $refund->purchase->id]) }}"
                            class="text-dark hover-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            #{{ $refund->purchase->id }}
                        </a>
                    @else
                        <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
        @if($refund->purchase && $refund->purchase->product)
            <div class="list-group-item px-0 py-3">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <i class="bi bi-box me-2"></i>
                        <strong>{{ translate('Product') }}</strong>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.products.show', $refund->purchase->product->id) }}" class="text-dark hover-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            {{ $refund->purchase->product->name }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
        <div class="list-group-item px-0 py-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-cart me-2"></i>
                    <strong>{{ translate('Buyer') }}</strong>
                </div>
                <div class="col-auto">
                    @if($refund->user)
                        <a href="{{ route('admin.roles.users.edit', $refund->user->id) }}" class="text-dark hover-primary" target="_blank">
                            {{ $refund->user->full_name }}
                            @if($refund->user->trashed())
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
                    <i class="bi bi-shop me-2"></i>
                    <strong>{{ translate('Seller') }}</strong>
                </div>
                <div class="col-auto">
                    @if($refund->seller)
                        <a href="{{ route('admin.roles.users.edit', $refund->seller->id) }}" class="text-dark hover-primary" target="_blank">
                            {{ $refund->seller->full_name }}
                            @if($refund->seller->trashed())
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
                    <strong>{{ translate('Amount') }}</strong>
                </div>
                <div class="col-auto">
                    <strong>{{ getAmount($refund->purchase->sale->price ?? 0) }}</strong>
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
                    <span class="badge {{ $refund->status_badge_class }}">
                        <i class="bi {{ $refund->status_icon }} me-1"></i>
                        {{ $refund->status_name }}
                    </span>
                </div>
            </div>
        </div>
        <div class="list-group-item px-0 pt-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <i class="bi bi-calendar3 me-2"></i>
                    <strong>{{ translate('Request Date') }}</strong>
                </div>
                <div class="col-auto">
                    <span>{{ dateFormat($refund->created_at) }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <a href="{{ route('admin.records.refunds.destroy', $refund->id) }}" class="btn btn-danger action-confirm flex-fill" data-method="DELETE" data-confirm="{{ translate('Are you sure to delete this refund?') }}">{{ translate('Delete Refund') }}</a>
        <button type="button" class="btn bg-text-primary hover-opacity flex-fill" data-bs-toggle="modal" data-bs-target="#sendEmailModal-{{ $refund->id }}">{{ translate('Send Mail') }}</button>
        <button type="button" class="btn bg-text-secondary hover-opacity flex-fill" data-bs-toggle="modal" data-bs-target="#conversationModal-{{ $refund->id }}">
            <i class="bi bi-chat-dots me-1"></i>
            {{ translate('Conversation') }}
        </button>
    </x-slot>
</x-modal>
