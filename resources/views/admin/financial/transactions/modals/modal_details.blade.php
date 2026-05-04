<x-modal :title="translate('Transaction Details')" size="md" icon="bi-receipt" :scrollable="true" :content-only="true"
    id="detailsModalContent">
    <x-archived-alert :model="$trx" :restoreRoute="route('admin.financial.transactions.restore', $trx->id)"
        :deleteRoute="route('admin.financial.transactions.destroy', $trx->id)" />

    {{-- Header Section --}}
    <div class="py-3 border-bottom bg-light-subtle rounded-top">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="text-muted text-uppercase fw-semibold mb-1 d-block fs-12 letter-spacing-1">{{
                    translate('Transaction') }}</span>
                <h4 class="fw-bold mb-0 text-dark">#{{ $trx->id }}</h4>
            </div>
            <div class="text-end">
                <div class="mb-2">{!! $trx->status_badge !!}</div>
                <div>{!! $trx->type_badge !!}</div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Date') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-calendar3 me-1 text-primary"></i>
                        {{ dateFormat($trx->created_at) }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="px-3 py-2 border rounded bg-white shadow-sm h-100">
                    <small class="text-muted d-block mb-1">{{ translate('Gateway') }}</small>
                    <div class="fw-semibold text-dark fs-14">
                        <i class="bi bi-credit-card me-1 text-primary"></i>
                        {{ $trx->paymentGateway?->name }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3">
        {{-- User Section --}}
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
                <i class="bi bi-person-fill me-1"></i>{{ translate('User Information') }}
            </h6>
            <div class="p-3 border rounded-3 bg-white hover-shadow transition-all">
                <x-user :user="$trx->user" avatarSize="md" />
            </div>
        </div>

        {{-- Financial Breakdown --}}
        <h6 class="fw-bold text-uppercase fs-12 text-muted mb-3 letter-spacing-1">
            <i class="bi bi-list-check me-1"></i>{{ translate('Financial Summary') }}
        </h6>
        <div class="bg-light p-3 rounded-3 border">
            @if ($trx->isTypePurchase())
            @foreach ($trx->trxProducts as $trxProduct)
            <div class="d-flex justify-content-between align-items-start mb-3 last-child-mb-0">
                <div class="d-flex gap-2">
                    <div class="bg-white rounded p-2 border shadow-xs flex-shrink-0"
                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-14 mb-0">{{ $trxProduct->product->name }}</div>
                        <small class="text-muted">
                            {{ $trxProduct->license_label }} &bull; {{ getAmount($trxProduct->price) }} x {{
                            $trxProduct->quantity }}
                        </small>
                    </div>
                </div>
                <div class="fw-bold text-dark">{{ getAmount($trxProduct->total) }}</div>
            </div>
            @if ($trxProduct->support)
            <div class="ms-5 d-flex justify-content-between border-start ps-3 py-1 mb-3">
                <div class="small text-muted">
                    <i class="bi bi-life-preserver me-1"></i>
                    {{ translate('Support ' . $trxProduct->support->name) }}
                </div>
                <div class="small fw-semibold text-dark">{{ getAmount($trxProduct->support->total) }}</div>
            </div>
            @endif
            @endforeach
            @elseif($trx->isTypeDeposit())
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded p-2 border shadow-xs">
                        <i class="bi bi-wallet2 text-primary fs-5"></i>
                    </div>
                    <span class="fw-bold text-dark">{{ translate('Wallet Deposit') }}</span>
                </div>
                <span class="fw-bold text-dark fs-5">{{ getAmount($trx->amount) }}</span>
            </div>
            @elseif($trx->isTypePremium())
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded p-2 border shadow-xs">
                        <i class="bi bi-gem text-primary fs-5"></i>
                    </div>
                    <div>
                        <span class="fw-bold text-dark d-block">{{ $trx->premiumPlan?->name }}</span>
                        <small class="text-muted">{{ $trx->premiumPlan?->interval_name }}</small>
                    </div>
                </div>
                <span class="fw-bold text-dark fs-5">{{ getAmount($trx->amount) }}</span>
            </div>
            @endif

            <hr class="my-3 opacity-10">

            {{-- Calculations --}}
            <div class="space-y-2">
                @if($trx->hasFees() || $trx->hasTax())
                <div class="d-flex justify-content-between text-muted fs-14">
                    <span>{{ translate('Subtotal') }}</span>
                    <span>{{ getAmount($trx->amount) }}</span>
                </div>
                @if ($trx->hasTax())
                <div class="d-flex justify-content-between text-muted fs-14">
                    <span>{{ $trx->tax->name }} ({{ $trx->tax->rate }}%)</span>
                    <span>{{ getAmount($trx->tax->amount) }}</span>
                </div>
                @endif
                @if ($trx->hasFees())
                <div class="d-flex justify-content-between text-muted fs-14">
                    <span>{{ translate('Gateway Fees') }} ({{ $trx->paymentGateway->fees }}%)</span>
                    <span>{{ getAmount($trx->fees) }}</span>
                </div>
                @endif
                @endif

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="fw-bold text-dark fs-16">{{ translate('Total Amount') }}</span>
                    <span class="fw-bold text-success fs-4">{{ getAmount($trx->total) }}</span>
                </div>
            </div>
        </div>

        @if ($trx->isCancelled() && $trx->reason)
        <div class="alert alert-danger b-dashed-danger mb-0 mt-3">
            <h6 class="alert-heading fw-bold fs-14 mb-1">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ translate('Cancellation Reason') }}
            </h6>
            <p class="mb-0 fs-13 opacity-75">{{ $trx->reason }}</p>
        </div>
        @endif

        @if (!$trx->isCancelled())
        <div id="trxCancelForm" class="mt-3 d-none pt-3 border-top">
            <form action="{{ route('admin.financial.transactions.cancel', $trx->id) }}" method="POST"
                class="ajax-form" data-confirm="{{ translate('Are you sure to cancel this transaction?') }}">
                @csrf
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold small text-muted">{{ translate('Note the Reason') }} <span
                            class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control border-danger-subtle" rows="3" required
                        placeholder="{{ translate('Why is this transaction being cancelled?') }}"></textarea>
                </div>
                <button type="submit" class="btn btn-danger btn-md w-100">
                    <i class="bi bi-send-fill me-1"></i>
                    {{ translate('Submit Cancellation') }}
                </button>
            </form>
        </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="w-100">
            <div class="d-flex flex-wrap gap-2 w-100">
                @if (!$trx->isCancelled())
                <button type="button" class="btn btn-outline-danger btn-md flex-fill"
                    data-slide-toggle="#trxCancelForm" title="{{ translate('Cancel Transaction') }}">
                    <i class="bi bi-x-circle me-1"></i>
                    {{ translate('Cancel') }}
                </button>
                @if ($trx->isPending())
                <form action="{{ route('admin.financial.transactions.paid', $trx->id) }}" method="POST"
                    class="flex-fill ajax-form"
                    data-confirm="{{ translate('Are you sure you want to mark this as paid?') }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-md w-100">
                        <i class="bi bi-check-lg me-1"></i>
                        {{ translate('Mark as Paid') }}
                    </button>
                </form>
                @endif
                @endif
                @if ($trx->payment_proof)
                <a href="{{ route('admin.financial.transactions.payment-proof', $trx->id) }}" target="_blank"
                    class="btn btn-primary btn-md flex-fill" title="{{ translate('View Payment Proof') }}">
                    <i class="bi bi-file-earmark-image me-1"></i>
                    {{ translate('Proof') }}
                </a>
                @endif
            </div>
        </div>
    </x-slot>
</x-modal>
