<div class="text-end">
    @php $product = $purchase->product; @endphp
    @if ($product->isDeleted())
        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 fw-medium">
            {{ translate('Deleted') }}
        </span>
    @else
        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn btn-sm btn-icon">
            <x-dropdown.item
                :href="$product->isMainFileExternal() ? ($product->main_file['path'] ?? '') : route('user.purchase.download', $purchase->id)"
                :target="$product->isMainFileExternal() ? '_blank' : null"
                icon="bi-download"
                class="py-2">
                {{ translate('Download') }}
            </x-dropdown.item>

            <x-dropdown.item
                type="button"
                class="py-2"
                data-bs-toggle="modal"
                data-bs-target="#purchaseCodeModal"
                data-action="{{ route('user.purchase.modal.code', $purchase->id) }}"
                icon="bi-key">
                {{ translate('Purchase Code') }}
            </x-dropdown.item>

            <x-dropdown.item
                :href="route('user.purchase.license', $purchase->id)"
                target="_blank"
                icon="bi-file-earmark-text"
                class="py-2">
                {{ translate('License PDF') }}
            </x-dropdown.item>

            @if (@settings('actions')->refunds)
                <x-dropdown.item type="divider" />
                <x-dropdown.item
                    type="button"
                    color="danger"
                    class="py-2"
                    data-bs-toggle="modal"
                    data-bs-target="#createRefundModal"
                    data-action="{{ route('user.refund.modal.create', ['purchase' => $purchase->id]) }}"
                    icon="bi-arrow-counterclockwise">
                    {{ translate('Request Refund') }}
                </x-dropdown.item>
            @endif
        </x-dropdown>
    @endif
</div>

