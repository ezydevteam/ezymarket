<x-modal :title="translate('Payout Details')" size="md" icon="bi-wallet2" :scrollable="true" :content-only="true"
    id="detailsModalContent">
    <x-archived-alert :model="$payout" :restoreRoute="route('admin.financial.payouts.restore', $payout->id)"
        :deleteRoute="route('admin.financial.payouts.destroy', $payout->id)" />

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top text-start">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">{{
                    translate('Payout Request') }}</span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $payout->id }}</h4>
            </div>
            <div class="text-end">
                <div class="mb-2">{!! $payout->status->badge() !!}</div>
                <div class="badge bg-secondary-subtle text-secondary px-3 py-1">{{ $payout->payoutMethod->name ??
                    translate('Manual') }}</div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Requested Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($payout->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Transfer Method') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-wallet2 me-1 text-primary"></i>
                        {{ $payout->payoutMethod->name ?? translate('N/A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3 text-start">
        {{-- Seller Section --}}
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                <i class="bi bi-person-badge-fill me-1"></i>{{ translate('Seller Information') }}
            </h6>
            <div class="p-3 border rounded-3 bg-white hover-shadow transition-all">
                <x-user :user="$payout->seller" avatarSize="md" />
            </div>
        </div>

        {{-- Destination Details --}}
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                <i class="bi bi-credit-card-2-front me-1"></i>{{ translate('Payout Destination') }}
            </h6>
            <div class="p-3 bg-light rounded-3 border">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-gray-800 small">{{ translate('Account Information') }}</span>
                    <span class="fw-bold text-dark font-monospace">{{ hideInDemo($payout->account) }}</span>
                </div>
                @if($payout->payoutMethod)
                <div class="d-flex justify-content-between align-items-center mt-2 small">
                    <span class="text-gray-800">{{ translate('Gateway Details') }}</span>
                    <span>{{ $payout->payoutMethod->name }} (UID: {{ $payout->payoutMethod->id }})</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Financial Breakdown --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-currency-exchange me-1"></i>{{ translate('Financial Summary') }}
        </h6>
        <div class="bg-primary-subtle p-4 rounded-3 border border-primary-subtle">
            <div class="space-y-3">
                <div class="d-flex justify-content-between align-items-baseline">
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark fs-15">{{ translate('Gross Withdrawal') }}</span>
                        <small class="text-muted">{{ translate('Initial request amount') }}</small>
                    </div>
                    <span class="fw-bold text-dark fs-16">{{ getAmount($payout->amount) }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-baseline">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-danger fs-14">{{ translate('Processing Fees') }}</span>
                        <small class="text-muted">{{ translate('Platform deduction') }}</small>
                    </div>
                    <span class="fw-bold text-danger fs-15">- {{ getAmount($payout->fees) }}</span>
                </div>

                <hr class="my-3 border-dark opacity-10">

                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-primary fs-16">{{ translate('Net Transferable') }}</span>
                        <small class="text-muted">{{ translate('Amount to be disbursed') }}</small>
                    </div>
                    <span class="fw-bold text-primary fs-4 lh-1">{{ getAmount($payout->net_amount) }}</span>
                </div>
            </div>
        </div>

        {{-- Admin Note History --}}
        @if($payout->admin_note)
        <div class="mt-4">
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-2 letter-spacing-1">
                <i class="bi bi-sticky-fill me-1"></i>{{ translate('Administrative Note') }}
            </h6>
            <div class="p-3 bg-white border border-dashed rounded-3 italic text-muted fs-14">
                {{ $payout->admin_note }}
            </div>
        </div>
        @endif

        {{-- Process Form Section --}}
        @if (!$payout->isCancelled() && !$payout->isReturned() && !$payout->isCompleted())
        <div id="payoutProcessForm" class="mt-4 pt-4 border-top d-none">
            <form action="{{ route('admin.financial.payouts.update-status', $payout->id) }}" method="POST"
                class="ajax-form">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted text-uppercase">{{ translate('Action Status') }}</label>
                        <select name="status" class="form-select selectpicker" required>
                            @foreach ($payout->getStatusOptions() as $value => $label)
                            @if($value == \App\Enums\PayoutStatus::RECALLED->value) @continue @endif
                            <option value="{{ $value }}" @selected($value == $payout->status->value)>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted text-uppercase">{{ translate('Admin Note') }} <span class="text-danger">*</span></label>
                        <textarea name="admin_note" class="form-control" rows="3" required
                            placeholder="{{ translate('Specify reason or additional instructions for the seller...') }}">{{ $payout->admin_note }}</textarea>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary btn-md w-100 shadow-none py-2 action-confirm"
                            data-confirm="{{ translate('Are you sure you want to execute this status change?') }}">
                            <i class="bi bi-send-check-fill me-1"></i>
                            {{ translate('Submit') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="w-100">
            <div class="d-flex flex-wrap gap-2 w-100">
                <button type="button" class="btn btn-cancel btn-md flex-fill"
                    data-bs-dismiss="modal">
                    {{ translate('Dismiss') }}
                </button>
                @if (!$payout->isCancelled() && !$payout->isReturned() && !$payout->isCompleted())
                <button type="button" class="btn btn-primary btn-md flex-fill shadow-sm"
                    data-slide-toggle="#payoutProcessForm">
                    <i class="bi bi-gear-fill me-1"></i>
                    {{ translate('Take Action') }}
                </button>
                @endif
            </div>
        </div>
        </x-slot>
</x-modal>
