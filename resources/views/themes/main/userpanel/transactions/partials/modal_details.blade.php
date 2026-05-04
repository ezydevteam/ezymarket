<x-modal :content-only="true" :title="translate('Transaction Information')" :icon="'bi-receipt'" :body-class="'p-0'"
    :scrollable="true">
    @themeInclude('userpanel.partials.restored-notice', ['model' => $trx, 'type' => 'transaction'])

    <div class="p-4 bg-light border-bottom border-light">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-gray-700 small d-block mb-1">{{ translate('Ref #') . $trx->id }}</span>
                {!! $trx->status_badge !!}
            </div>
            <div class="text-end">
                <span class="text-gray-700 small d-block mb-1">{{ translate('Transaction Date') }}</span>
                <span class="fw-medium text-dark">{{ dateFormat($trx->created_at) }}</span>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-borderless mb-0">
            <tbody>
                <tr class="border-bottom border-light">
                    <td class="p-4 text-gray-700">{{ translate('Transaction Type') }}</td>
                    <td class="p-4 fw-medium text-end">{{ $trx->type->label() }}</td>
                </tr>
                <tr class="border-bottom border-light">
                    <td class="p-4 text-gray-700">{{ translate('Payment Method') }}</td>
                    <td class="p-4 fw-medium text-end">{{ $trx->payment_gateway_name ?: translate('N/A') }}</td>
                </tr>

                @if ($trx->isTypePurchase())
                @foreach ($trx->trxProducts as $trxProduct)
                <tr class="border-bottom border-light">
                    <td class="p-4">
                        <span class="d-block fw-bold text-dark">{{ $trxProduct->product->name }}</span>
                        <span class="small text-muted">{{ $trxProduct->isRegularLicense() ? translate('Regular License')
                            : translate('Extended License') }} x {{ $trxProduct->quantity }}</span>
                    </td>
                    <td class="p-4 fw-medium text-end align-middle">{{ getAmount($trxProduct->total) }}</td>
                </tr>
                @endforeach
                @else
                <tr class="border-bottom border-light">
                    <td class="p-4 text-gray-700">{{ translate('Description') }}</td>
                    <td class="p-4 fw-medium text-end">{{ $trx->type->label() }}</td>
                </tr>
                @endif

                @if ($trx->hasFees() || $trx->hasTax())
                <tr class="border-bottom border-light">
                    <td class="p-4 text-gray-700">{{ translate('Subtotal') }}</td>
                    <td class="p-4 fw-medium text-end">{{ getAmount($trx->amount) }}</td>
                </tr>
                @if ($trx->hasTax())
                <tr class="border-bottom border-light">
                    <td class="p-4 text-gray-700">{{ $trx->tax->name }} ({{ $trx->tax->rate }}%)</td>
                    <td class="p-4 fw-medium text-end">{{ getAmount($trx->tax->amount) }}</td>
                </tr>
                @endif
                @if ($trx->hasFees())
                <tr class="border-bottom border-light">
                    <td class="p-4 text-gray-700">{{ translate('Processing Fees') }}</td>
                    <td class="p-4 fw-medium text-end">{{ getAmount($trx->fees) }}</td>
                </tr>
                @endif
                @endif

                <tr class="bg-light-subtle">
                    <td class="p-4 fw-semibold text-gray-700 text-uppercase">{{ translate('Total Amount') }}</td>
                    <td class="p-4 fw-bold text-primary text-end fs-5">{{ getAmount($trx->total) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($trx->isCancelled() && $trx->reason)
    <div class="p-4 bg-danger-subtle border-top border-danger border-opacity-10">
        <span class="text-danger small d-block mb-1 fw-bold text-uppercase">{{ translate('Cancellation Reason')
            }}</span>
        <p class="mb-0 small text-dark fst-italic">{{ $trx->reason }}</p>
    </div>
    @endif

    <x-slot name="footer">
        <button type="button" class="btn btn-outline-secondary flex-fill text-uppercase" data-bs-dismiss="modal">
            {{ translate('Close') }}
        </button>
        @if ($trx->isPaid())
        <a href="{{ route('user.transaction.invoice', $trx->id) }}" target="_blank"
            class="btn btn-primary flex-fill text-uppercase">
            <i class="bi bi-printer me-2"></i>{{ translate('Invoice') }}
        </a>
        @endif
    </x-slot>
</x-modal>
