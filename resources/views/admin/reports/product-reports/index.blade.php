@extends('admin.layouts.full')
@section('section', translate('Reports'))
@section('title', translate('Reported Products'))
@section('content')
    <x-datatable id="productReportsTable" :items="$reports">
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Product') }}</th>
                <th>{{ translate('Reported By') }}</th>
                <th class="text-center">{{ translate('Reason') }}</th>
                <th class="text-center">{{ translate('Report Status') }}</th>
                <th class="text-center">{{ translate('Product Status') }}</th>
                <th class="text-center">{{ translate('Reported Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $report)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $report->id }}">
                    </td>
                    <td>
                       <x-product :product="$report->product" :showSeller="true" :showCategory="false" />
                    </td>
                    <td>
                       <x-user :user="$report->user" />
                    </td>
                    <td class="text-center">
                        {!! $report->reason_badge !!}
                    </td>
                    <td class="text-center">
                        <span role="button" data-bs-toggle="modal" data-bs-target="#reportDetailsModal-{{ $report->id }}">
                            {!! $report->status_badge !!}
                        </span>
                    </td>
                    <td class="text-center">
                        {!! $report->product?->status_badge !!}
                    </td>
                    <td class="text-center text-muted">{{ dateFormat($report->created_at) }}</td>
                    <td>
                        <div class="text-end">
                            <button type="button" class="btn-icon small"
                                data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="bi bi-three-dots-vertical text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-sm-end">
                                <li>
                                    <a class="dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#reportDetailsModal-{{ $report->id }}">
                                        <i class="bi bi-eye text-primary me-1"></i>
                                        {{ translate('View Details') }}
                                    </a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger action-confirm"
                                        href="{{ route('admin.reports.product-reports.destroy', $report->id) }}"
                                        data-method="DELETE"
                                        data-confirm="{{ translate('Are you sure you want to delete this report? This action cannot be undone.') }}">
                                        <i class="bi bi-trash me-1"></i>
                                        {{ translate('Delete') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Include modals for each report --}}
    @foreach($reports as $report)
        @include('admin.reports.product-reports.partials.details-modal', ['report' => $report])
        @include('admin.reports.product-reports.partials.history-modal', ['report' => $report])
    @endforeach

    {{-- Settings Modal --}}
    @include('admin.reports.product-reports.partials.settings-modal', [
        'productReportSettings' => $productReportSettings
    ])
@endsection

@push('scripts_libs')
    <script>
    "use strict";
    config.translates.searchPlaceholder = "{{ translate('Search Product Reports') }}";

    (() => {

        let columnIndex = 0;
        const columns = {
            checkbox: columnIndex++,
            product: columnIndex++,
            reportedBy: columnIndex++,
            reason: columnIndex++,
            reportStatus: columnIndex++,
            productStatus: columnIndex++,
            reportedAt: columnIndex++,
            actions: columnIndex++
        };

        const filterConfig = {
            autoApply: false,
            filters: [
                {
                    type: 'select',
                    column: columns.reason,
                    label: '{{ translate("Reason") }}',
                    width: 4,
                    options: [
                        @foreach (\App\Models\Product\ProductReport::getReasonOptions() as $key => $value)
                            { value: '{{ $value }}', label: '{{ $value }}' },
                        @endforeach
                    ]
                },
                {
                    type: 'select',
                    column: columns.reportStatus,
                    label: '{{ translate("Report Status") }}',
                    width: 4,
                    options: [
                        @foreach (\App\Models\Product\ProductReport::getStatusOptions() as $key => $value)
                            { value: '{{ $value }}', label: '{{ $value }}' },
                        @endforeach
                    ]
                },
                {
                    type: 'daterange',
                    column: columns.reportedAt,
                    label: '',
                    width: 4
                }
            ]
        };

        const tableElement = document.getElementById('productReportsTable');
        if (tableElement) {
            $(tableElement).attr('data-export', true);
            $(tableElement).attr('data-ajax-filter', true);
            $(tableElement).data('filter-config', filterConfig);

            // Define bulk actions as dropdown menu items
            const bulkActions = [
                {
                    text: '<i class="bi bi-check-circle text-success me-2"></i>{{ translate("Resolve Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.reports.product-reports.bulk-resolve') }}",
                            confirmMessage: "{{ translate('Are you sure you want to resolve the selected reports?') }}"
                        });
                    }
                },
                {
                    text: '<i class="bi bi-x-circle text-orange me-2"></i>{{ translate("Cancel Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.reports.product-reports.bulk-cancel') }}",
                            confirmMessage: "{{ translate('Are you sure you want to cancel the selected reports?') }}"
                        });
                    }
                },
                {
                    className: 'dropdown-item border-top my-1 p-0',
                },
                {
                    text: '<i class="bi bi-trash text-danger me-2"></i>{{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.reports.product-reports.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected reports?') }}"
                        });
                    }
                }
            ];

            $(tableElement).data('bulk-actions', bulkActions);

            const customButtons = [
                {
                    text: '<i class="bi bi-gear me-1"></i> {{ translate("Settings") }}',
                    className: 'btn bg-text-dark',
                    action: function(e, dt, node, config) {
                        $('#productReportSettingsModal').modal('show');
                    }
                }
            ];

            $(tableElement).data('custom-buttons', customButtons);
        }
    })();
    </script>
@endpush

@push('scripts')
    <script>
    "use strict";
    $(document).ready(function() {
        @foreach($reports as $report)
            // Initialize AJAX modal form for update status
            initAjaxModalForm({
                modalSelector: '#reportDetailsModal-{{ $report->id }}',
                formSelector: '#updateStatusForm-{{ $report->id }}',
                submitButtonSelector: '#updateStatusBtn-{{ $report->id }}',
                loadingText: '{{ translate("Updating...") }}',
            });
        @endforeach

        // Initialize AJAX modal form for settings
        initAjaxModalForm({
            modalSelector: '#productReportSettingsModal',
            formSelector: '#productReportSettingsForm',
            submitButtonSelector: '#productReportSettingsSubmitBtn',
            loadingText: '{{ translate("Saving...") }}',
        });
    });
    </script>
@endpush
