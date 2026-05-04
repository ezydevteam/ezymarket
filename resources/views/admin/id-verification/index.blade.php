@extends('admin.layouts.full')
@section('title', translate('ID Verifications'))
@section('container', 'container-max-xxl')
@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                       <div>
                            <h6 class="card-title">
                                {{ translate('Pending') }}
                            </h6>
                            <div class="card-count">
                                {{ numberFormat($counters['pending']) }}
                            </div>
                       </div>
                       <a class="text-reset" href="{{ route('admin.id-verification.index', ['status' => 'pending']) }}">
                            <div class="card-icon bg-text-orange">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </a>
                    </div>
                    <label class="card-label">{{ translate('Pending Requests') }}</label>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                        <div>
                            <h6 class="card-title">
                                {{ translate('Approved') }}
                            </h6>
                            <div class="card-count">
                                {{ numberFormat($counters['approved']) }}
                            </div>
                        </div>
                        <a class="text-reset" href="{{ route('admin.id-verification.index', ['status' => 'approved']) }}">
                            <div class="card-icon bg-text-green">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </a>
                    </div>
                    <label class="card-label">{{ translate('Approved Requests') }}</label>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                        <div>
                            <h6 class="card-title">
                                {{ translate('Rejected') }}
                            </h6>
                            <div class="card-count">
                                {{ numberFormat($counters['rejected']) }}
                            </div>
                        </div>
                        <a class="text-reset" href="{{ route('admin.id-verification.index', ['status' => 'rejected']) }}">
                            <div class="card-icon bg-text-red">
                                    <i class="bi bi-x-circle"></i>
                            </div>
                        </a>
                    </div>
                    <label class="card-label">{{ translate('Rejected Requests') }}</label>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card counter-card">
                <div class="card-body">
                    <div class="card-info">
                       <div>
                            <h6 class="card-title">
                                {{ translate('Verified Users') }}
                            </h6>
                            <div class="card-count">
                                {{ numberFormat($counters['verified_users']) }}
                            </div>
                       </div>
                       <a class="text-reset" href="{{ route('admin.id-verification.index') }}">
                            <div class="card-icon bg-text-primary">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                        </a>
                    </div>
                    <label class="card-label">{{ translate('Total Verified Users') }}</label>
                </div>
            </div>
        </div>
    </div>
    <x-datatable
        id="idVerificationTable"
        :items="$idVerifications"
        tableClass="datatable2"
        emptyMessage="{{ translate('No ID verification requests found!') }}"
        emptyDescription="{{ translate('Verification requests will appear here when users submit their identity documents') }}"
        emptyIcon="bi-vcard"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('User details') }}</th>
                <th class="text-center">{{ translate('Document Type') }}</th>
                <th class="text-center">{{ translate('Document Number') }}</th>
                <th class="text-center">{{ translate('Current Status') }}</th>
                <th class="text-center">{{ translate('Submited Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($idVerifications as $idVerification)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $idVerification->id }}">
                    </td>
                    <td>
                       <x-user :user="$idVerification->user"/>
                    </td>
                    <td class="text-center">
                        {{ $idVerification->getDocumentType() }}
                    </td>
                    <td class="text-center">{{ $idVerification->document_number }}</td>
                    <td class="text-center">
                        <span role="button" class="badge {{ $idVerification->status_badge_class }} view-verification-details" data-id="{{ $idVerification->id }}">
                            <i class="bi {{ $idVerification->status_icon }} me-1"></i>
                            {{ $idVerification->status_name }}
                        </span>
                    </td>
                    <td class="text-center">{{ dateFormat($idVerification->created_at) }}</td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    type="button"
                                    class="view-verification-details"
                                    data-id="{{ $idVerification->id }}"
                                    icon="bi bi-eye"
                                    iconClass="text-primary me-2">
                                    {{ translate('View Details') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.id-verification.destroy', $idVerification->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure want to delete this ID verification request? This action can not be undone.') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Review Modal (AJAX Loaded) --}}
    <x-modal
        id="verificationReviewModal"
        title="{{ translate('Review Verification') }}"
        size="xl"
        icon="bi bi-vcard">
        <x-loader id="modalLoader" centered />
        <div id="modalContent" class="d-none"></div>
    </x-modal>
@endsection

@push('scripts_libs')
    <script>
    "use strict";
    config.translates.searchPlaceholder = "{{ translate('Search ID Verifications') }}";

    (() => {

        let columnIndex = 0;
        const columns = {
            checkbox: columnIndex++,
            userDetails: columnIndex++,
            documentType: columnIndex++,
            documentNumber: columnIndex++,
            currentStatus: columnIndex++,
            submitedDate: columnIndex++,
            actions: columnIndex++
        };

        const filterConfig = {
            autoApply: false,
            filters: [
                {
                    type: 'select',
                    column: columns.documentType,
                    label: '{{ translate("Document Type") }}',
                    width: 4,
                    options: [
                        { value: '{{ translate("National ID") }}', label: '{{ translate("National ID") }}' },
                        { value: '{{ translate("Passport") }}', label: '{{ translate("Passport") }}' }
                    ]
                },
                {
                    type: 'select',
                    column: columns.currentStatus,
                    label: '{{ translate("Status") }}',
                    width: 4,
                    options: [
                        { value: '{{ translate("Pending") }}', label: '{{ translate("Pending") }}' },
                        { value: '{{ translate("Approved") }}', label: '{{ translate("Approved") }}' },
                        { value: '{{ translate("Rejected") }}', label: '{{ translate("Rejected") }}' }
                    ]
                },
                {
                    type: 'daterange',
                    column: columns.submitedDate,
                    label: '{{ translate("Submitted") }}',
                    width: 4
                }
            ]
        };

        const tableElement = document.getElementById('idVerificationTable');
        if (tableElement) {
            $(tableElement).attr('data-export', true);
            $(tableElement).attr('data-ajax-filter', true);
            $(tableElement).data('filter-config', filterConfig);

            // Define bulk actions as dropdown menu items
            const bulkActions = [
                {
                    text: '<i class="bi bi-check-circle text-success me-2"></i>{{ translate("Approve Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.id-verification.bulk-approve') }}",
                            confirmMessage: "{{ translate('Are you sure you want to approve the selected verifications?') }}"
                        });
                    }
                },
                {
                    text: '<i class="bi bi-x-circle text-orange me-2"></i>{{ translate("Reject Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        const reason = prompt("{{ translate('Please enter rejection reason:') }}");
                        if (reason) {
                            bulkAction({
                                url: "{{ route('admin.id-verification.bulk-reject') }}",
                                data: { rejection_reason: reason },
                                confirmMessage: "{{ translate('Are you sure you want to reject the selected verifications?') }}"
                            });
                        }
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
                            url: "{{ route('admin.id-verification.index') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected requests?') }}"
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

        // Initialize AJAX modal content loader for verification details
        initAjaxModalContent({
            triggerSelector: '.view-verification-details',
            modalId: 'verificationReviewModal',
            urlBuilder: (id) => `{{ url('admin/id-verification') }}/${id}`,
            errorMessage: '{{ translate("Failed to load verification details") }}',
            iconClass: 'bi-person-vcard'
        });
    </script>
@endpush

















