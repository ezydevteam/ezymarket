@extends('admin.layouts.full')
@section('section', translate('Reports'))
@section('title', translate('Reported Product Comments'))
@section('content')
    <x-datatable id="commentReportsTable" :items="$commentReports">
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Commentator') }}</th>
                <th>{{ translate('Reporter') }}</th>
                <th class="text-center">{{ translate('Reason') }}</th>
                <th class="text-center">{{ translate('Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($commentReports as $report)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $report->id }}">
                    </td>
                    <td>
                        <x-user :user="$report->commentReply->user" />
                    </td>
                    <td>
                        <x-user :user="$report->user" />
                    </td>
                    <td class="text-center">
                        <span role="button" data-bs-toggle="modal" data-bs-target="#commentReportDetailsModal-{{ $report->id }}">{!! $report->reason_badge !!}</span>
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
                                        href="{{ route('products.comment', [
                                            $report->commentReply->comment->product->slug,
                                            $report->commentReply->comment->product->id,
                                            $report->commentReply->comment->id,
                                        ]) }}"
                                        target="_blank">
                                        <i class="bi bi-box-arrow-up-right text-primary me-1"></i>
                                        {{ translate('View Comment') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#commentReportDetailsModal-{{ $report->id }}">
                                        <i class="bi bi-eye text-info me-1"></i>
                                        {{ translate('View Details') }}
                                    </a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-success action-confirm"
                                        href="{{ route('admin.reports.comment-reports.keep', $report->id) }}"
                                        data-method="POST"
                                        data-confirm="{{ translate('Are you sure you want to keep this comment? The report will be dismissed.') }}">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ translate('Keep Comment') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger action-confirm"
                                        href="{{ route('admin.reports.comment-reports.delete', $report->id) }}"
                                        data-method="DELETE"
                                        data-confirm="{{ translate('Are you sure you want to delete this comment? This action cannot be undone.') }}">
                                        <i class="bi bi-trash me-1"></i>
                                        {{ translate('Delete Comment') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @include('admin.reports.comment-reports.partials.details-modal', ['report' => $report])
            @endforeach
        </tbody>
    </x-datatable>
@endsection

@push('scripts_libs')
    <script>
    "use strict";
    config.translates.searchPlaceholder = "{{ translate('Search Comment Reports') }}";

    (() => {
        let columnIndex = 0;
        const columns = {
            checkbox: columnIndex++,
            commentator: columnIndex++,
            reporter: columnIndex++,
            reason: columnIndex++,
            date: columnIndex++,
            actions: columnIndex++
        };

        const filterConfig = {
            autoApply: false,
            filters: [
                {
                    type: 'select',
                    column: columns.reason,
                    label: '{{ translate("Reason") }}',
                    width: 6,
                    options: [
                        @foreach (\App\Models\Product\ProductCommentReport::getReasonOptions() as $key => $value)
                            { value: '{{ $value }}', label: '{{ $value }}' },
                        @endforeach
                    ]
                },
                {
                    type: 'daterange',
                    column: columns.date,
                    label: '',
                    width: 6
                }
            ]
        };

        const tableElement = document.getElementById('commentReportsTable');
        if (tableElement) {
            $(tableElement).attr('data-export', true);
            $(tableElement).attr('data-ajax-filter', true);
            $(tableElement).data('filter-config', filterConfig);

            const bulkActions = [
                {
                    text: '<i class="bi bi-check-circle text-success me-2"></i>{{ translate("Keep Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.reports.comment-reports.bulk-keep') }}",
                            confirmMessage: "{{ translate('Are you sure you want to keep the selected reported comments? The reports will be dismissed.') }}"
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
                            url: "{{ route('admin.reports.comment-reports.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected reported comments?') }}"
                        });
                    }
                }
            ];

            $(tableElement).data('bulk-actions', bulkActions);
        }
    })();
    </script>
@endpush
