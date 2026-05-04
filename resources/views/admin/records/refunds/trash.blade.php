@extends('admin.layouts.full')
@section('section', translate('Records'))
@section('title', translate('Trashed Refunds'))
@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                        <div>
                            <h6 class="card-title">{{ translate('Trashed Refunds') }}</h6>
                            <div class="card-count">{{ numberFormat($refunds->count()) }}</div>
                        </div>
                        <div class="card-icon bg-text-red">
                            <i class="bi bi-trash"></i>
                        </div>
                    </div>
                    <label class="card-label">{{ translate('Total Administrative Trash') }}</label>
                </div>
            </div>
        </div>
    </div>
    <x-datatable
        id="refundsTrashTable"
        :items="$refunds"
        tableClass="datatable2"
        emptyMessage="{{ translate('Trash is empty') }}"
        emptyDescription="{{ translate('Refunds deleted by administrators will appear here') }}"
        emptyIcon="bi-trash"
        data-index-url="{{ route('admin.records.refunds.trash.index') }}"
        data-translates-json="{{ json_encode([
            'status' => translate('Status'),
            'restoreSelected' => translate('Restore Selected'),
            'restoreConfirm' => translate('Are you sure you want to restore the selected refunds?'),
            'deletePermanentSelected' => translate('Permanently Delete Selected'),
            'deletePermanentConfirm' => translate('Are you sure you want to permanently delete the selected refunds? This action cannot be undone.'),
        ]) }}"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Product') }}</th>
                <th>{{ translate('Buyer') }}</th>
                <th>{{ translate('Seller') }}</th>
                <th class="text-center">{{ translate('Amount') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Deletion Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($refunds as $refund)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $refund->id }}">
                    </td>
                    <td>
                        @if($refund->purchase && $refund->purchase->product)
                        <div class="d-flex align-items-center gap-3">
                            <div class="image-fluid image-md rounded">
                                <img src="{{ $refund->purchase->product->thumbnail_url }}" alt="{{ $refund->purchase->product->name }}">
                            </div>
                            <div>
                                <span class="text-reset fw-medium d-block">
                                    {{ truncateText($refund->purchase->product->name, 40) }}
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-receipt"></i> Purchase #{{ $refund->purchase->id }}
                                </small>
                            </div>
                        </div>
                        @else
                            <span class="text-muted">{{ translate('N/A') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($refund->user)
                            <div class="user-box d-flex align-items-center gap-3">
                                <div class="image-fluid image-md rounded transition-all">
                                    <img src="{{ $refund->user->avatar_url }}" alt="{{ $refund->user->username }}">
                                </div>
                                <span>
                                    <span class="text-reset fw-medium transition-all">{{ $refund->user->full_name }}</span>
                                    <p class="text-muted small mb-0">
                                        {{ hideInDemo($refund->user->email) }}
                                    </p>
                                </span>
                            </div>
                        @else
                            <span class="text-muted">{{ translate('N/A') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($refund->seller)
                            <div class="user-box d-flex align-items-center gap-3">
                                <div class="image-fluid image-md rounded transition-all">
                                    <img src="{{ $refund->seller->avatar_url }}" alt="{{ $refund->seller->username }}">
                                </div>
                                <span>
                                    <span class="text-reset fw-medium transition-all">{{ $refund->seller->full_name }}</span>
                                    <p class="text-muted small mb-0">
                                        {{ hideInDemo($refund->seller->email) }}
                                    </p>
                                </span>
                            </div>
                        @else
                            <span class="text-muted">{{ translate('N/A') }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ getAmount($refund->purchase->sale->price ?? 0) }}</strong>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $refund->status_badge_class }}">
                            {{ $refund->status_name }}
                        </span>
                    </td>
                    <td class="text-center text-muted">{{ dateFormat($refund->deleted_at) }}</td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="{{ route('admin.records.refunds.trash.restore', $refund->id) }}"
                                    icon="bi bi-arrow-counterclockwise"
                                    iconClass="text-success me-2"
                                    class="action-confirm"
                                    data-method="POST"
                                    data-confirm="{{ translate('Are you sure you want to restore this refund?') }}">
                                    {{ translate('Restore') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.records.refunds.trash.permanently-delete', $refund->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure you want to permanently delete this refund? This action cannot be undone.') }}">
                                    {{ translate('Permanently Delete') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>
@endsection
@push('scripts_libs')
    <script>
    "use strict";
    (() => {
        const tableElement = document.getElementById('refundsTrashTable');
        if (!tableElement) return;

        const data = $(tableElement).data();
        const translates = data.translatesJson;

        const bulkActions = [
            {
                text: `<i class="bi bi-arrow-counterclockwise text-success me-2"></i>${translates.restoreSelected}`,
                className: 'dropdown-item',
                action: function(e, dt, node, config) {
                    bulkAction({
                        url: "{{ route('admin.records.refunds.trash.bulk-restore') }}",
                        method: 'POST',
                        confirmMessage: translates.restoreConfirm
                    });
                }
            },
            {
                className: 'dropdown-item border-top my-1 p-0',
            },
            {
                text: `<i class="bi bi-trash text-danger me-2"></i>${translates.deletePermanentSelected}`,
                className: 'dropdown-item text-danger',
                action: function(e, dt, node, config) {
                    bulkAction({
                        url: "{{ route('admin.records.refunds.bulk-delete') }}",
                        method: 'DELETE',
                        confirmMessage: translates.deletePermanentConfirm
                    });
                }
            }
        ];

        $(tableElement).data('bulk-actions', bulkActions);
    })();
    </script>
@endpush
