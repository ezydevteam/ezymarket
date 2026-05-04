@if ($user->isSeller())
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-credit-card fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Payout Details') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Manage user payout methods and account settings') }}
                    </p>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary px-4 fw-bold" form="payoutUpdateForm">
                    <i class="bi bi-save me-1"></i>
                    {{ translate('Save Changes') }}
                </button>
            </div>
        </div>

        <form id="payoutUpdateForm" action="{{ route('admin.roles.users.payout.update', $user->id) }}" method="POST"
            class="ajax-form">
            @csrf
            <div class="row g-4">
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('Payout Method') }}</label>
                    <select name="payout_method" class="form-select form-control-lg selectpicker" data-live-search="true"
                        placeholder="{{ translate('Select Method') }}">
                        @foreach ($payoutMethods as $payoutMethod)
                        <option value="{{ $payoutMethod->id }}" @selected($payoutMethod->id == $user->payout_method_id)>
                            {{ $payoutMethod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">{{ translate('Payout Account / Details') }} <span
                            class="text-danger">*</span></label>
                    <textarea name="payout_account" class="form-control" rows="3"
                        placeholder="{{ translate('Enter payout account details (e.g. Email, IBAN, etc.)') }}">{{ $user->payout_account }}</textarea>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-bank2 fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('Update Balance') }}</h5>
                    <p class="text-muted small mb-0">{!! translate('Current balance <strong
                            class="text-success">:amount</strong>',
                        ['amount' => getAmount($user->balance)]) !!}</p>
                </div>
            </div>
        </div>

        <form id="userBalanceForm" action="{{ route('admin.roles.users.wallet.update', $user->id) }}" method="POST"
            class="ajax-form">
            @csrf
            <div class="row mb-4 g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ translate('Action Type') }}</label>
                    <select name="type" id="balanceType" class="form-select selectpicker"
                        placeholder="{{ translate('Select Type') }}" required>
                        <option value="credit" selected>{{ translate('Credit (Add Funds)') }}</option>
                        <option value="debit">{{ translate('Debit (Subtract Funds)') }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ translate('Amount') }} <span
                            class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">{{ currency_symbol() }}</span>
                        <input type="number" step="any" name="amount" class="form-control"
                            placeholder="{{ translate('0.00') }}" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">{{ translate('Reference / Note') }} <span
                            class="text-danger">*</span></label>
                    <textarea name="note" class="form-control" rows="3"
                        placeholder="{{ translate('Internal note for this transaction') }}" required></textarea>
                </div>
            </div>
            <button type="submit" id="balanceSubmitBtn" class="btn btn-primary fw-bold px-4 action-confirm"
                data-confirm="{{ translate('Are you sure you want to update this user balance?') }}">
                <i class="bi bi-save me-1"></i> {{ translate('Update Balance') }}
            </button>
        </form>
    </div>
</div>
