@php
$productReports = \App\Models\Product\ProductReport::where('product_id', $report->product_id)
->with('user')
->orderBy('created_at', 'desc')
->get();
@endphp
<x-modal id="reportHistoryModal-{{ $report->id }}" :title="'Report History - ' . $report->product->name" size="lg"
    scrollable="true" :icon="'bi bi-clock-history'">
    <div class="table-responsive">
        <table class="table ezydev-table">
            <thead>
                <tr>
                    <th>{{ translate('ID') }}</th>
                    <th>{{ translate('Reported By') }}</th>
                    <th class="text-center">{{ translate('Reason') }}</th>
                    <th class="text-center">{{ translate('Status') }}</th>
                    <th class="text-center">{{ translate('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productReports as $productReport)
                <tr class="{{ $productReport->id === $report->id ? 'table-active' : '' }}">
                    <td>
                        #{{ $productReport->id }}
                    </td>
                    <td>
                        <x-user :user="$productReport->user" :showEmail="false" />
                    </td>
                    <td class="text-center">{!! $productReport->reason_badge !!}</td>
                    <td class="text-center">{!! $productReport->status_badge !!}</td>
                    <td class="text-center text-muted small">{{ dateFormat($productReport->created_at) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        {{ translate('No reports found for this product') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot name="footer">
        @if($productReports->count() > 0)
        <div class="alert alert-info py-1 my-0 flex-fill">
            <i class="bi bi-info-circle me-2"></i>
            {{ translate('Total Reports') }}: <strong>{{ numberFormat($productReports->count()) }}</strong>
        </div>
        @endif
        <button type="button" class="btn btn-cancel flex-fill" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>{{ translate('Close') }}
        </button>
    </x-slot>
</x-modal>
