<x-modal :content-only="true" :title="translate('Request Refund')" icon="bi-arrow-counterclockwise text-danger">
    <form action="{{ route('user.refund.store') }}" method="POST" class="ajax-form" id="createRefundForm">
        @csrf
        <div class="row g-4">
            <!-- Purchase Selection -->
            <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 small mb-2">{{ translate('Select Purchased Product') }} <span class="text-danger">*</span></label>
                <select name="purchase" class="form-select form-select-md selectpicker" placeholder="{{ translate('Choose a purchase...') }}" data-live-search="true">
                    @foreach ($purchases as $purchase)
                        <option value="{{ $purchase->id }}" {{ (isset($selectedPurchaseId) && $selectedPurchaseId == $purchase->id) ? 'selected' : '' }}>
                            {{ truncateText($purchase->product->name, 50) }} (#{{ $purchase->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Subject -->
            <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 small mb-2">{{ translate('Subject') }} <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control form-control-md"
                        placeholder="{{ translate('Brief subject for your refund request') }}">
            </div>

            <!-- Reason -->
            <div class="col-12">
                <label class="form-label fw-semibold text-gray-700 small mb-2">{{ translate('Reason for Refund') }} <span class="text-danger">*</span></label>
                <textarea name="reason" rows="4" class="form-control form-control-md"
                            placeholder="{{ translate('Describe why you are requesting a refund...') }}"></textarea>
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="btn btn-outline-dark flex-fill text-uppercase" data-bs-dismiss="modal">
                {{ translate('Cancel') }}
            </button>
            <button type="submit" form="createRefundForm" class="btn btn-danger flex-fill text-uppercase">
                <i class="bi bi-send me-2"></i>{{ translate('Submit Request') }}
            </button>
        </x-slot>
    </form>
</x-modal>
