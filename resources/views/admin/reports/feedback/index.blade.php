@extends('admin.layouts.full')
@section('section', translate('Reports'))
@section('title', translate('User Feedback'))
@section('content')
    <x-datatable id="feedbackTable" :items="$feedbacks">
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('User') }}</th>
                <th class="text-center">{{ translate('Field') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($feedbacks as $feedback)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $feedback->id }}">
                    </td>
                    <td>
                        @if($feedback->user)
                            <x-user :user="$feedback->user" />
                        @else
                            <span class="text-muted">{{ translate('Guest') }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {!! $feedback->field_badge !!}
                    </td>
                    <td class="text-center">
                        <span role="button" data-bs-toggle="modal" data-bs-target="#feedbackDetailsModal-{{ $feedback->id }}">
                            {!! $feedback->status_badge !!}
                        </span>
                    </td>
                    <td class="text-center text-muted">{{ dateFormat($feedback->created_at) }}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button type="button" class="btn-icon small"
                                data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="bi bi-three-dots-vertical text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-sm-end">
                                <li>
                                    <a class="dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#feedbackDetailsModal-{{ $feedback->id }}">
                                        <i class="bi bi-eye text-primary me-1"></i>
                                        {{ translate('View Details') }}
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider" />
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger action-confirm"
                                        href="{{ route('admin.reports.feedback.destroy', $feedback->id) }}"
                                        data-method="DELETE"
                                        data-confirm="{{ translate('Are you sure you want to delete this feedback? This action cannot be undone.') }}">
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
    @foreach($feedbacks as $feedback)
        @include('admin.reports.feedback.partials.details-modal', ['feedback' => $feedback])
    @endforeach
@endsection

@push('scripts_libs')
    <script>
    "use strict";
    config.translates.searchPlaceholder = "{{ translate('Search Feedback') }}";

    (() => {
        let columnIndex = 0;
        const columns = {
            checkbox: columnIndex++,
            user: columnIndex++,
            field: columnIndex++,
            status: columnIndex++,
            date: columnIndex++,
            actions: columnIndex++
        };

        const filterConfig = {
            autoApply: false,
            filters: [
                {
                    type: 'select',
                    column: columns.status,
                    label: '{{ translate("Status") }}',
                    width: 4,
                    options: [
                        @foreach (\App\Models\Feedback::getStatusOptions() as $key => $value)
                            { value: '{{ $value }}', label: '{{ $value }}' },
                        @endforeach
                    ]
                },
                {
                    type: 'select',
                    column: columns.field,
                    label: '{{ translate("Field") }}',
                    width: 4,
                    options: [
                        @foreach (\App\Models\Feedback::getFeedbackFields() as $key => $value)
                            { value: '{{ $value }}', label: '{{ $value }}' },
                        @endforeach
                    ]
                },
                {
                    type: 'daterange',
                    column: columns.date,
                    label: '',
                    width: 4
                }
            ]
        };

        const tableElement = document.getElementById('feedbackTable');
        if (tableElement) {
            $(tableElement).attr('data-export', true);
            $(tableElement).attr('data-ajax-filter', true);
            $(tableElement).data('filter-config', filterConfig);

            const bulkActions = [
                {
                    text: '<i class="bi bi-check-circle text-primary me-2"></i>{{ translate("Reviewed Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.reports.feedback.bulk-review') }}",
                            confirmMessage: "{{ translate('Are you sure you want to mark as reviewed the selected feedbacks?') }}"
                        });
                    }
                },
                {
                    text: '<i class="bi bi-check2-square text-success me-2"></i>{{ translate("Resolved Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.reports.feedback.bulk-resolve') }}",
                            confirmMessage: "{{ translate('Are you sure you want to mark as resolved the selected feedbacks?') }}"
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
                            url: "{{ route('admin.reports.feedback.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected feedbacks?') }}"
                        });
                    }
                }
            ];

            $(tableElement).data('bulk-actions', bulkActions);
        }
    })();
    </script>
@endpush

@push('scripts')
    <script>
    "use strict";
    $(document).ready(function() {
        @foreach($feedbacks as $feedback)
            // Initialize AJAX modal form for update status
            initAjaxModalForm({
                modalSelector: '#feedbackDetailsModal-{{ $feedback->id }}',
                formSelector: '#updateFeedbackStatusForm-{{ $feedback->id }}',
                submitButtonSelector: '#updateFeedbackStatusBtn-{{ $feedback->id }}',
                loadingText: "{{ translate('Updating...') }}",
            });
        @endforeach
    });
    </script>
@endpush
