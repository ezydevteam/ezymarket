<x-modal id="editPayoutMethodModal-{{ $payoutMethod->id }}"
    :title="translate('Edit Payout Method #:id', ['id' => $payoutMethod->id])" size="lg" icon="bi-pencil-square"
    scrollable="true">
    <form id="editPayoutMethodForm-{{ $payoutMethod->id }}"
        action="{{ route('admin.financial.payout-methods.update', $payoutMethod->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Method Name & Status --}}
        <div class="row g-3 mb-3 align-items-center">
            <div class="col-lg-8">
                <label class="form-label" for="name-{{ $payoutMethod->id }}">
                    {{ translate('Method Name') }}
                    <span class="text-danger">*</span>
                </label>
                <input type="text" name="name" id="name-{{ $payoutMethod->id }}" class="form-control"
                    value="{{ $payoutMethod->name }}"
                    placeholder="{{ translate('e.g., Bank Transfer, PayPal, Crypto') }}" required>
            </div>
            <div class="col-lg-4">
                <label class="form-label d-block">{{ translate('Status') }}</label>
                <div class="ezydev-switch-wrapper-xl">
                    <input type="checkbox" class="ezydev-switch-input" name="is_active"
                        id="edit-switch-is-active-{{ $payoutMethod->id }}" value="1" {{ $payoutMethod->isActive() ?
                    'checked' : '' }}>
                    <label class="ezydev-switch-label" for="edit-switch-is-active-{{ $payoutMethod->id }}">
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

        {{-- Amount Limits --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label" for="min_amount-{{ $payoutMethod->id }}">
                    {{ translate('Minimum Amount') }}
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">{{ defaultCurrency()->symbol }}</span>
                    <input type="number" name="min_amount" id="min_amount-{{ $payoutMethod->id }}" class="form-control"
                        value="{{ $payoutMethod->min_amount }}" step="0.01" min="0" required>
                </div>
                <small class="text-muted">{{ translate('Minimum payout amount') }}</small>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="max_amount-{{ $payoutMethod->id }}">
                    {{ translate('Maximum Amount') }}
                </label>
                <div class="input-group">
                    <span class="input-group-text">{{ defaultCurrency()->symbol }}</span>
                    <input type="number" name="max_amount" id="max_amount-{{ $payoutMethod->id }}" class="form-control"
                        value="{{ $payoutMethod->max_amount > 0 ? $payoutMethod->max_amount : '' }}" step="0.01" min="0"
                        placeholder="{{ translate('Leave empty for unlimited') }}">
                </div>
                <small class="text-muted">{{ translate('Maximum payout amount (optional)') }}</small>
            </div>
        </div>

        {{-- Monthly Limit & Processing Fees --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label" for="monthly_limit-{{ $payoutMethod->id }}">
                    {{ translate('Monthly Payout Limit') }}
                </label>
                <input type="number" name="monthly_limit" id="monthly_limit-{{ $payoutMethod->id }}"
                    class="form-control"
                    value="{{ $payoutMethod->monthly_limits > 0 ? $payoutMethod->monthly_limits : '' }}" min="1"
                    placeholder="{{ translate('Leave empty for unlimited') }}">
                <small class="text-muted">{{ translate('Number of payouts allowed per month (leave empty for
                    unlimited)') }}</small>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="edit_fees_type_{{ $payoutMethod->id }}">
                    {{ translate('Fee Type') }}
                </label>
                <select name="fees_type" id="edit_fees_type_{{ $payoutMethod->id }}"
                    class="form-select fees-type-select" data-target-prefix="edit-fees-prefix-{{ $payoutMethod->id }}"
                    data-target-hint="edit-fees-hint-{{ $payoutMethod->id }}"
                    data-target-wrapper="edit-fees-value-wrapper-{{ $payoutMethod->id }}">
                    <option value="">{{ translate('No Fees') }}</option>
                    <option value="percentage" @selected($payoutMethod->fees_type === 'percentage')>{{
                        translate('Percentage') }}</option>
                    <option value="fixed" @selected($payoutMethod->fees_type === 'fixed')>{{ translate('Fixed Amount')
                        }}</option>
                </select>
                <small class="text-muted">{{ translate('Processing fees for each payout') }}</small>
            </div>
        </div>

        {{-- Fee Value --}}
        <div class="mb-3 fees-value-wrapper" id="edit-fees-value-wrapper-{{ $payoutMethod->id }}"
            style="{{ $payoutMethod->fees_type ? '' : 'display: none;' }}">
            <label class="form-label" for="fees_value-{{ $payoutMethod->id }}">
                {{ translate('Fee Value') }}
            </label>
            <div class="input-group">
                <span class="input-group-text" id="edit-fees-prefix-{{ $payoutMethod->id }}">
                    {{ $payoutMethod->fees_type === 'percentage' ? '%' : defaultCurrency()->symbol }}
                </span>
                <input type="number" name="fees_value" id="fees_value-{{ $payoutMethod->id }}" class="form-control"
                    value="{{ $payoutMethod->fees_value > 0 ? $payoutMethod->fees_value : '' }}" step="0.01" min="0"
                    placeholder="0">
            </div>
            <small class="text-muted" id="edit-fees-hint-{{ $payoutMethod->id }}">
                {{ $payoutMethod->fees_type === 'percentage' ? translate("Enter percentage (e.g., 2.5 for 2.5%)") :
                translate("Enter fixed amount (e.g., 10 for $10)") }}
            </small>
        </div>

        {{-- Instructions --}}
        <div class="mb-2">
            <label class="form-label" for="instructions-{{ $payoutMethod->id }}">
                {{ translate('Instructions') }}
            </label>
            <textarea name="instructions" id="instructions-{{ $payoutMethod->id }}" class="form-control"
                rows="4">{{ $payoutMethod->instructions }}</textarea>
            <small class="text-muted">{{ translate('Payout instructions and requirements for users') }}</small>
        </div>

        <x-slot name="footer">
            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
            <button type="submit" id="editPayoutMethodBtn-{{ $payoutMethod->id }}"
                form="editPayoutMethodForm-{{ $payoutMethod->id }}" class="btn btn-primary">{{ translate('Update
                Method') }}</button>
        </x-slot>
    </form>
</x-modal>
