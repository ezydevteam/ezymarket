@php
    $modalId = 'createSellerTaxModal';
    $formId = 'createSellerTaxForm';
@endphp

<x-modal
    id="{{ $modalId }}"
    :title="translate('New Seller Tax')"
    size="md"
    icon="bi-bag-check"
>
    <form id="{{ $formId }}" action="{{ route('admin.financial.seller-taxes.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ translate('Name') }}<span class="text-danger ms-1">*</span></label>
                <input type="text" name="name" class="form-control form-control-md" required autofocus />
            </div>

            <div class="col-12">
                <label class="form-label">{{ translate('Tax Rate') }}<span class="text-danger ms-1">*</span></label>
                <div class="input-group">
                    <input type="number" name="percentage" class="form-control form-control-md"
                        placeholder="0.00" min="0.01" max="100" step="0.01" required />
                    <span class="input-group-text px-3"><i class="bi bi-percent"></i></span>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">{{ translate('Countries') }}<span class="text-danger ms-1">*</span></label>
                <select name="countries[]" class="form-select form-select-md selectpicker" multiple data-live-search="true"
                    title="--Select--" required>
                    @foreach (countries() as $countryCode => $countryName)
                        <option value="{{ $countryCode }}">{{ $countryName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
            {{ translate('Cancel') }}
        </button>
        <button type="submit" form="{{ $formId }}" class="btn btn-primary ms-3" id="createSellerTaxBtn">
            <i class="bi bi-check-lg me-1"></i>{{ translate('Create') }}
        </button>
    </x-slot>
</x-modal>
