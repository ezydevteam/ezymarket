@php
$gatewayId = $paymentGateway->id;
$modalId = "editPaymentGatewayModal-{$gatewayId}";
$formId = "editPaymentGatewayForm-{$gatewayId}";
@endphp

<x-modal id="{{ $modalId }}" :title="translate('Edit Payment Gateway')" size="lg" icon="bi-pencil-square"
    scrollable="true">
    <form id="{{ $formId }}" action="{{ route('admin.financial.payment-gateways.update', $gatewayId) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Logo Upload --}}
        <div class="mb-3">
            <label class="form-label">{{ translate('Gateway Logo') }}</label>
            <div class="text-center bg-light p-3 rounded">
                <div class="mb-3">
                    <img id="attach-image-preview-{{ $gatewayId }}" src="{{ $paymentGateway->logo_link }}" width="120px"
                        class="rounded">
                </div>
                <button type="button" class="btn btn-sm btn-secondary attach-image-button" data-id="{{ $gatewayId }}">
                    <i class="bi bi-camera me-1"></i>{{ translate('Choose Logo') }}
                </button>
                <input id="attach-image-targeted-input-{{ $gatewayId }}" type="file" name="gateway_logo"
                    accept="image/*" hidden>
                <small class="text-muted d-block mt-2">{{ translate('Allowed: PNG, JPG, JPEG, WEBP') }}</small>
            </div>
        </div>

        {{-- Name & Status --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <label class="form-label">{{ translate('Gateway Name') }}</label>
                <input type="text" name="gateway_name" class="form-control" value="{{ $paymentGateway->name }}"
                    required>
            </div>
            <div class="col-lg-4">
                <label class="form-label d-block">{{ translate('Status') }}</label>
                <div class="ezydev-switch-wrapper-xl">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active-{{ $gatewayId }}" class="ezydev-switch-input" type="checkbox" name="is_active"
                        value="1" {{ $paymentGateway->isActive() ? 'checked' : '' }}>
                    <label class="ezydev-switch-label" for="is_active-{{ $gatewayId }}">
                        <span class="ezydev-switch-slider">
                            <span class="ezydev-switch-button">
                                <span class="ezydev-switch-on">{{ translate('Active') }}</span>
                                <span class="ezydev-switch-off">{{ translate('Inactive') }}</span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        @if (!$paymentGateway->isAccountBalance())
        {{-- Mode & Fees --}}
        <div class="row g-3 mb-3">
            @if ($paymentGateway->mode)
            <div class="col-md-6">
                <label class="form-label">{{ translate('Mode') }}</label>
                <select name="gateway_mode" class="form-select">
                    @foreach ($paymentGateway->getModes() as $mode)
                    <option value="{{ $mode }}" {{ $paymentGateway->mode == $mode ? 'selected' : '' }}>
                        {{ ucfirst($mode) }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="{{ $paymentGateway->mode ? 'col-md-6' : 'col-md-12' }}">
                <label class="form-label">{{ translate('Processing Fees (%)') }}</label>
                <input type="number" name="gateway_fees" class="form-control" value="{{ $paymentGateway->fees }}"
                    min="0" max="100" step="0.01">
            </div>
        </div>

        @if (!$paymentGateway->isManual())
        {{-- Charge Currency Conversion --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ translate('Charge Currency (Optional)') }}</label>
                <input type="text" name="charge_currency" class="form-control"
                    value="{{ $paymentGateway->charge_currency }}" placeholder="{{ translate('e.g., USD') }}">
                <small class="text-muted">{{ translate('Leave empty if gateway supports your default currency')
                    }}</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ translate('Conversion Rate') }}</label>
                <input type="number" name="charge_rate" class="form-control" value="{{ $paymentGateway->charge_rate }}"
                    step="0.0001" placeholder="0.0000">
                <small class="text-muted">{{ translate('1 ') }}{{ defaultCurrency()->code }} = ? {{
                    $paymentGateway->charge_currency ?: '...' }}</small>
            </div>
        </div>

        {{-- Credentials --}}
        @if ($paymentGateway->credentials)
        <div class="mb-3">
            <label class="form-label fw-bold">{{ translate('API Credentials') }}</label>
            @foreach ($paymentGateway->credentials as $key => $value)
            <div class="mb-2">
                <label class="form-label text-capitalize small">{{ translate(str_replace('_', ' ', $key)) }}</label>
                <input type="text" name="gateway_credentials[{{ $key }}]" value="{{ hideInDemo($value) }}"
                    class="form-control">
            </div>
            @endforeach
        </div>
        @endif
        @else
        {{-- Instructions for Manual Gateways --}}
        <div class="mb-3">
            <label class="form-label">{{ translate('Payment Instructions') }}</label>
            <textarea name="payment_instructions" id="instructions-{{ $gatewayId }}" class="form-control"
                rows="5">{{ $paymentGateway->instructions }}</textarea>
            <small class="text-muted">{{ translate('Instructions shown to users during checkout') }}</small>
        </div>
        @endif
        @endif
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
        <button type="submit" id="editPaymentGatewayBtn-{{ $gatewayId }}" form="{{ $formId }}" class="btn btn-primary">
            {{ translate('Update Gateway') }}
        </button>
    </x-slot>
</x-modal>
