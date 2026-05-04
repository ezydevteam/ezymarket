<x-modal id="createPayoutMethodModal" :title="translate('New Payout Method')" size="lg" icon="bi-plus-circle"
    scrollable="true">
    <form id="createPayoutMethodForm" action="{{ route('admin.financial.payout-methods.create') }}" method="POST"
        class="ajax-form">
        @csrf

        {{-- Method Name & Status --}}
        <div class="row g-3 mb-3 align-items-center">
            <div class="col-lg-8">
                <label class="form-label" for="name">
                    {{ translate('Method Name') }}
                    <span class="text-danger">*</span>
                </label>
                <input type="text" name="name" id="name" class="form-control"
                    placeholder="{{ translate('e.g., Bank Transfer, PayPal, Crypto') }}" required>
            </div>
            <div class="col-lg-4">
                <label class="form-label d-block">{{ translate('Status') }}</label>
                <div class="ezydev-switch-wrapper-xl">
                    <input type="checkbox" class="ezydev-switch-input" name="is_active" id="create-switch-is-active"
                        value="1" checked>
                    <label class="ezydev-switch-label" for="create-switch-is-active">
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
                <label class="form-label" for="min_amount">
                    {{ translate('Minimum Amount') }}
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">{{ defaultCurrency()->symbol }}</span>
                    <input type="number" name="min_amount" id="min_amount" class="form-control" value="0" step="0.01"
                        min="0" required>
                </div>
                <small class="text-muted">{{ translate('Minimum payout amount') }}</small>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="max_amount">
                    {{ translate('Maximum Amount') }}
                </label>
                <div class="input-group">
                    <span class="input-group-text">{{ defaultCurrency()->symbol }}</span>
                    <input type="number" name="max_amount" id="max_amount" class="form-control" step="0.01" min="0"
                        placeholder="{{ translate('Leave empty for unlimited') }}">
                </div>
                <small class="text-muted">{{ translate('Maximum payout amount (optional)') }}</small>
            </div>
        </div>

        {{-- Monthly Limit & Processing Fees --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label" for="monthly_limit">
                    {{ translate('Monthly Payout Limit') }}
                </label>
                <input type="number" name="monthly_limit" id="monthly_limit" class="form-control" min="1"
                    placeholder="{{ translate('Leave empty for unlimited') }}">
                <small class="text-muted">{{ translate('Number of payouts allowed per month (leave empty for
                    unlimited)') }}</small>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="create_fees_type">
                    {{ translate('Fee Type') }}
                </label>
                <select name="fees_type" id="create_fees_type" class="form-select fees-type-select"
                    data-target-prefix="create-fees-prefix" data-target-hint="create-fees-hint"
                    data-target-wrapper="create-fees-value-wrapper">
                    <option value="">{{ translate('No Fees') }}</option>
                    <option value="percentage">{{ translate('Percentage') }}</option>
                    <option value="fixed">{{ translate('Fixed Amount') }}</option>
                </select>
                <small class="text-muted">{{ translate('Processing fees for each payout') }}</small>
            </div>
        </div>

        {{-- Fee Value --}}
        <div class="mb-3 fees-value-wrapper" id="create-fees-value-wrapper" style="display: none;">
            <label class="form-label" for="fees_value">
                {{ translate('Fee Value') }}
            </label>
            <div class="input-group">
                <span class="input-group-text" id="create-fees-prefix">{{ defaultCurrency()->symbol }}</span>
                <input type="number" name="fees_value" id="fees_value" class="form-control" step="0.01" min="0"
                    placeholder="0">
            </div>
            <small class="text-muted" id="create-fees-hint">{{ translate('Enter percentage or fixed amount') }}</small>
        </div>

        {{-- Instructions --}}
        <div class="mb-2">
            <label class="form-label" for="instructions">
                {{ translate('Instructions') }}
            </label>
            <textarea name="instructions" id="create_instructions" class="form-control" rows="4"></textarea>
            <small class="text-muted">{{ translate('Payout instructions and requirements for users') }}</small>
        </div>
    </form>
    <x-slot name="footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
        <button type="submit" id="createPayoutMethodBtn" form="createPayoutMethodForm" class="btn btn-primary">{{
            translate('Create Method') }}</button>
    </x-slot>
</x-modal>
