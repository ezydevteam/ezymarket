<x-modal :content-only="true" id="payoutModal" :title="translate('Payout Request')" :icon="'bi-wallet2'"
    :scrollable="true">
    @if (!@settings('payout')->status)
        <div class="text-center py-4">
            <div class="icon-circle icon-circle-lg bg-warning-subtle text-warning mx-auto mb-4">
                <i class="bi bi-exclamation-triangle fs-1"></i>
            </div>
            <h5 class="fw-bold mb-3">{{ translate('Payouts Temporarily Paused') }}</h5>
            <p class="text-muted px-4">
                {{ translate('The payout system is currently undergoing maintenance. Please contact support for more information or try again later.') }}
            </p>
            <div class="mt-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                    {{ translate('Close Window') }}
                </button>
            </div>
        </div>
    @elseif (authUser()->hasPayoutAccount() && authUser()->payoutMethod)
        @php
            $payoutMethod = authUser()->payoutMethod;
            $methodMinAmount = $payoutMethod->amount_limit['min'] ?? 0;
            $globalMinAmount = (float) (@settings('payout')->minimum ?? 0);
            $effectiveMinimum = max($methodMinAmount, $globalMinAmount);
            $effectiveMaximum = $payoutMethod->amount_limit['max']
                ? min($payoutMethod->amount_limit['max'], authUser()->balance)
                : authUser()->balance;
        @endphp

        @if (authUser()->balance >= $effectiveMinimum)
            <form action="{{ route('user.payout.store') }}" method="POST" id="payoutForm" class="ajax-form">
                @csrf

                <div class="modal-body p-0">
                    {{-- Amount Input Section --}}
                    <div class="userpanel-card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <label for="amount" class="form-label fw-bold small text-uppercase ls-1 text-gray-700 mb-3">
                                {{ translate('Withdrawal Amount') }}
                            </label>
                            <div class="input-group input-group-lg overflow-hidden">
                                <span class="input-group-text border-end-0 bg-light-subtle ps-4 text-primary fw-bold">
                                    {{ currentCurrency()->symbol }}
                                </span>
                                <input type="number" class="form-control form-control-lg" id="amount"
                                    name="amount" data-fees-type="{{ $payoutMethod->fees_type }}"
                                    data-fees-value="{{ $payoutMethod->fees_value ?? 0 }}"
                                    data-currency-symbol="{{ currentCurrency()->symbol }}"
                                    data-currency-position="{{ currentCurrency()->position }}"
                                    data-available="{{ authUser()->balance }}" data-min="{{ $effectiveMinimum }}"
                                    step="0.5" placeholder="0.00" autocomplete="off" required>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-gray-600">{{ translate('Balance Utilization') }}</span>
                                    <span class="fw-bold text-dark" id="payoutPercent">0%</span>
                                </div>
                                <div class="progress rounded-pill bg-white border" style="height: 8px;">
                                    <div id="payoutProgressBar" class="progress-bar rounded-pill transition-all bg-primary"
                                        role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-2 fs-12 text-gray-600">
                                    <span>{{ translate('Min: :amount', ['amount' => getAmount($effectiveMinimum)]) }}</span>
                                    <span>{{ translate('Available: :amount', ['amount' => getAmount(authUser()->balance)]) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payout Method info --}}
                    <div class="card mb-4 border-dashed border-secondary-subtle">
                        <div class="card-body d-flex align-items-center p-3 gap-3">
                            <div class="icon-circle icon-circle-md bg-secondary-subtle text-primary flex-grow-0">
                                <i class="bi bi-bank"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">{{ $payoutMethod->name }}</h6>
                                <p class="small mb-0">{{ translate('Account') }}: {{ authUser()->payout_account }}</p>
                            </div>
                            <a href="{{ route('user.settings.payout') }}"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-grow-0">
                                {{ translate('Edit') }}
                            </a>
                        </div>
                    </div>

                    {{-- Summary Card --}}
                    <div class="card bg-primary-subtle border-0 p-0">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small">{{ translate('Requested Amount') }}</span>
                                <span class="fw-semibold text-dark" id="displayAmount">{{ getAmount(0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-primary border-opacity-10">
                                <span class="small">
                                    {{ translate('Processing Fees') }}
                                    @if ($payoutMethod->fees_type === 'percentage')
                                        <span class="badge bg-danger-subtle text-danger ms-1 px-2">{{ $payoutMethod->fees_value }}%</span>
                                    @endif
                                </span>
                                <span class="fw-medium text-danger" id="displayFees">-{{ getAmount(0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="fw-bold text-dark">{{ translate('Total to Receive') }}</span>
                                <h4 class="fw-bolder text-primary mb-0" id="displayTotal">{{ getAmount(0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <x-slot:footer>
                <button type="button" class="btn btn-outline-secondary flex-fill text-uppercase" data-bs-dismiss="modal">
                    {{ translate('Cancel') }}
                </button>
                <button type="submit" form="payoutForm" class="btn btn-primary flex-fill action-confirm text-uppercase"
                    data-confirm="{{ translate('Are you sure you want to submit this payout request? The amount will be deducted from your wallet immediately.') }}">
                    <i class="bi bi-send-fill me-2"></i>{{ translate('Submit Request') }}
                </button>
            </x-slot:footer>
        @else
            <div class="text-center py-5">
                <div class="icon-circle-xl bg-danger-subtle text-danger mx-auto mb-4">
                    <i class="bi bi-wallet2 fs-1"></i>
                </div>
                <h5 class="fw-bold mb-3">{{ translate('Insufficient Wallet Funds') }}</h5>
                <p class="text-muted px-4">
                    {{ translate('Your current wallet balance (:balance) is below the minimum payout threshold (:minimum).', [
                        'balance' => getAmount(authUser()->balance),
                        'minimum' => getAmount($effectiveMinimum),
                    ]) }}
                </p>
                <div class="mt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        {{ translate('Close') }}
                    </button>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <div class="icon-circle icon-circle-lg bg-info-subtle text-info mx-auto mb-4">
                <i class="bi bi-wrench-adjustable-circle fs-1"></i>
            </div>
            <h5 class="fw-bold mb-3">{{ translate('Setup Required') }}</h5>
            <p class="text-gray-700 px-4">
                {{ translate('Please configure your preferred payout method in settings before requesting a withdrawal.') }}
            </p>
            <div class="mt-4">
                <a href="{{ route('user.settings.payout') }}" class="btn btn-primary rounded-pill px-4">
                    {{ translate('Configure Now') }}
                </a>
            </div>
        </div>
    @endif
</x-modal>
