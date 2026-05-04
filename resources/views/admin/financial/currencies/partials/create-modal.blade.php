@php
    $modalId = 'createCurrencyModal';
    $formId = 'createCurrencyForm';
@endphp

<x-modal
    id="{{ $modalId }}"
    :title="translate('Create New Currency')"
    size="lg"
    icon="bi-cash-coin"
    scrollable="true"
>
    <form id="{{ $formId }}" action="{{ route('admin.financial.currencies.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            {{-- Currency Icon Upload --}}
            <div class="col-12">
                <label class="form-label">{{ translate('Currency Icon') }}<span class="text-danger ms-1">*</span></label>
                <div class="text-center bg-light p-3 rounded">
                    <div class="mb-3">
                        <img id="attach-image-preview-create" src="{{ asset('images/currencies/usd.png') }}" width="64" class="rounded">
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary attach-image-button" data-id="create">
                        <i class="bi bi-camera me-2"></i>{{ translate('Choose Icon') }}
                    </button>
                    <input id="attach-image-targeted-input-create" type="file" name="icon" accept="image/*" hidden>
                    <small class="text-muted d-block mt-2">{{ translate('Recommended: 64x64px | Allowed: PNG, JPG, JPEG, WEBP') }}</small>
                </div>
            </div>

            {{-- Currency Code --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Currency Code') }}
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                    <input type="text" name="code" class="form-control form-control-md text-uppercase"
                           placeholder="USD" maxlength="10" required>
                </div>
            </div>

            {{-- Currency Symbol --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Currency Symbol') }}
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">{{ defaultCurrency()->symbol }}</span>
                    <input type="text" name="symbol" class="form-control form-control-md"
                        placeholder="$" maxlength="10" required>
                </div>
            </div>

            {{-- Country --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Country') }}
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-globe"></i></span>
                    <input type="text" name="country" class="form-control form-control-md"
                        placeholder="{{ translate('e.g., United States') }}" maxlength="100" required>
                </div>
            </div>

            {{-- Currency Position --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Symbol Position') }}
                    <span class="text-danger">*</span>
                </label>
                <select name="position" class="form-select form-select-md" required>
                    @foreach (\App\Models\Financial\Currency::getCurrencyPositionOptions() as $positionKey => $positionValue)
                        <option value="{{ $positionKey }}">
                            {{ $positionValue }}
                            @if($positionKey == \App\Models\Financial\Currency::BEFORE_PRICE)
                                ({{ translate('Example') }}: $100)
                            @else
                                ({{ translate('Example') }}: 100$)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Exchange Rate --}}
            <div class="col-12">
                <label class="form-label">
                    {{ translate('Exchange Rate') }}
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                    <input type="number"
                        name="rate"
                        class="form-control form-control-md"
                        placeholder="0.000000"
                        step="0.000001"
                        min="0.000001"
                        required>
                </div>
                 <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ translate('Example: If 1') }} {{ defaultCurrency()->code }} = 0.85 EUR, {{ translate('enter') }} 0.85
                </div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
            {{ translate('Cancel') }}
        </button>
        <button type="submit" form="{{ $formId }}" class="btn btn-primary ms-3" id="createCurrencyBtn">
            <i class="bi bi-check-lg me-1"></i>{{ translate('Create') }}
        </button>
    </x-slot>
</x-modal>
