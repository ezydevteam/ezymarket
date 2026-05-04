@php
    $currencyId = $currency->id;
    $modalId = "editCurrencyModal-{$currencyId}";
    $formId = "editCurrencyForm-{$currencyId}";
@endphp

<x-modal
    id="{{ $modalId }}"
    :title="translate('Edit Currency')"
    size="lg"
    icon="bi-pencil-square"
    scrollable="true"
>
    <form id="{{ $formId }}" action="{{ route('admin.financial.currencies.update', $currencyId) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            {{-- Currency Icon Upload --}}
            <div class="col-12">
                <label class="form-label">{{ translate('Currency Icon') }}</label>
                <div class="text-center bg-light p-3 rounded">
                    <div class="mb-3">
                        <img id="attach-image-preview-{{ $currencyId }}" src="{{ $currency->icon_link }}" width="64" class="rounded">
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary attach-image-button" data-id="{{ $currencyId }}">
                        <i class="bi bi-camera me-1"></i>{{ translate('Change Icon') }}
                    </button>
                    <input id="attach-image-targeted-input-{{ $currencyId }}" type="file" name="icon" accept="image/*" hidden>
                    <small class="text-muted d-block mt-2">{{ translate('Recommended: 64x64px | Allowed: PNG, JPG, JPEG, WEBP') }}</small>
                </div>
            </div>

            {{-- Currency Code (Read-only) --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Currency Code') }}
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                    <input type="text" class="form-control form-control-md bg-light"
                           value="{{ $currency->code }}" disabled readonly>
                </div>
                <small class="text-muted">{{ translate('Currency code cannot be changed') }}</small>
            </div>

            {{-- Currency Symbol --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Currency Symbol') }}
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">{{ $currency->symbol }}</span>
                    <input type="text" name="symbol" class="form-control form-control-md"
                        value="{{ $currency->symbol }}" placeholder="$" maxlength="10" required>
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
                        value="{{ $currency->country }}" placeholder="{{ translate('e.g., United States') }}"
                        maxlength="100" required>
                </div>
            </div>

            {{-- Currency Position --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Symbol Position') }}
                    <span class="text-danger">*</span>
                </label>
                <select name="position" class="form-select form-select-md" required>
                    @foreach ($currency->getCurrencyPositionOptions() as $positionKey => $positionValue)
                        <option value="{{ $positionKey }}" @selected($currency->position == $positionKey)>
                            {{ $positionValue }}
                            ({{ translate('Example') }}: {{ $positionKey == '1' ? $currency->symbol . '100' : '100' . $currency->symbol }})
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
                    <span class="input-group-text">{{ $currency->symbol }}</span>
                    <input type="number"
                        name="rate"
                        class="form-control form-control-md"
                        value="{{ $currency->rate }}"
                        placeholder="0.000000"
                        step="0.000001"
                        min="0.000001"
                        required>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ translate('Example: If 1') }} {{ defaultCurrency()->code }} = 0.85 {{ $currency->code }}, {{ translate('enter') }} 0.85
                </div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
            {{ translate('Cancel') }}
        </button>
        <button type="submit" form="{{ $formId }}" class="btn btn-primary ms-3" id="editCurrencyBtn-{{ $currencyId }}">
            <i class="bi bi-check-lg me-1"></i>{{ translate('Update') }}
        </button>
    </x-slot>
</x-modal>
