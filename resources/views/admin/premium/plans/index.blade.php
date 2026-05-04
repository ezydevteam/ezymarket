@extends('admin.layouts.full')
@section('title', translate('Premium Plans'))
@section('container', 'container-max-xxl')
@section('content')
    <x-datatable
        id="premiumPlansTable"
        :items="$premiumPlans"
        tableClass="datatable2 sortable-table"
        emptyMessage="{{ translate('No premium plans found!') }}"
        emptyDescription="{{ translate('Create your first premium plan to get started') }}"
        emptyIcon="bi-star"
    >
        <thead>
            <tr>
                <th class="no-sort">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th class="no-sort"><i class="bi bi-sort-down fs-6"></i></th>
                <th>{{ translate('Plan Name') }}</th>
                <th>{{ translate('Interval') }}</th>
                <th class="text-center">{{ translate('Price') }}</th>
                <th class="text-center">{{ translate('Download Limit') }}</th>
                <th class="text-center">{{ translate('Total Members') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="sortable-table-tbody">
                            @foreach ($premiumPlans as $premiumPlan)
                                <tr data-id="{{ $premiumPlan->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $premiumPlan->id }}">
                                    </td>
                                    <td>
                                        <span class="sortable-table-handle me-2 text-muted">
                                            <i class="bi bi-arrows-move"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <span role="button" class="fw-medium"  data-bs-toggle="modal" data-bs-target="#editPlanModal-{{ $premiumPlan->id }}">
                                            {{ $premiumPlan->name }}
                                            @if ($premiumPlan->isFeatured())
                                                <span class="badge bg-text-primary ms-1">{{ $premiumPlan->featured_label ?? translate('Featured') }}</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $premiumPlan->interval_name }}</td>
                                    <td class="text-center">
                                        {{ $premiumPlan->price_label }}</td>
                                    <td class="text-center">
                                        {{ $premiumPlan->download_label }}
                                    </td>
                                    <td class="text-center">
                                        {{ $premiumPlan->premiums_count }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-text-{{ ($premiumPlan->isActive()) ? 'green' : 'red' }}">{{ ($premiumPlan->isActive()) ? translate('Active') : translate('Inactive') }}</span>
                                    </td>
                                    <td>
                                        <div class="text-end">
                                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                                <x-dropdown.item
                                                    href="#"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPlanModal-{{ $premiumPlan->id }}"
                                                    icon="bi bi-pencil-square"
                                                    iconClass="text-primary me-2">
                                                    {{ translate('Edit Details') }}
                                                </x-dropdown.item>
                                                <x-dropdown.item type="divider" />
                                                <x-dropdown.item
                                                    href="{{ route('admin.premium.plans.destroy', $premiumPlan->id) }}"
                                                    icon="bi bi-trash"
                                                    color="danger"
                                                    class="action-confirm"
                                                    data-method="DELETE"
                                                    data-confirm="{{ translate('Are you sure want to delete this subscription plan? This action can not be undone.') }}">
                                                    {{ translate('Delete') }}
                                                </x-dropdown.item>
                                            </x-dropdown>
                                        </div>
                                    </td>
                                </tr>
                                 {{-- Edit Modal --}}
                                 @include('admin.premium.plans.partials.edit-modal')
                            @endforeach
                        </tbody>
                    </x-datatable>

    {{-- Create Modal --}}
    @include('admin.premium.plans.partials.create-modal')

@endsection

@push('top_scripts')
    <script>
        "use strict";
        const sortingRoute = "{{ route('admin.premium.plans.sortable') }}";
        config.translates.searchPlaceholder = "{{ translate('Search Premium Plans') }}";
    </script>
@endpush
@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush
@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
    <script>
        "use strict";
        const tableElement = document.getElementById('premiumPlansTable');
        if (tableElement) {
            const bulkActions = [
                {
                    text: '<i class="bi bi-check-circle text-success me-2"></i>{{ translate("Active Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.premium.plans.bulk-active') }}",
                            confirmMessage: "{{ translate('Are you sure you want to activate the selected plans?') }}"
                        });
                    }
                },
                {
                    text: '<i class="bi bi-x-circle text-orange me-2"></i>{{ translate("Inactive Selected") }}',
                    className: 'dropdown-item',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.premium.plans.bulk-inactive') }}",
                            confirmMessage: "{{ translate('Are you sure you want to deactivated the selected plans?') }}"
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
                            url: "{{ route('admin.premium.plans.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected plans?') }}"
                        });
                    }
                }
            ];

            $(tableElement).data('bulk-actions', bulkActions);

            const customButtons = [
                {
                    text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("Create New Plan") }}',
                    className: 'btn btn-primary',
                    action: function(e, dt, node, config) {
                        $('#createPlanModal').modal('show');
                    }
                }
            ];

            $(tableElement).data('custom-buttons', customButtons);
        }
    </script>
@endpush

@push('scripts')
    <script>
    "use strict";
        // Initialize create ticket modal form
        initAjaxModalForm({
            formSelector: '#createPlanForm',
            modalSelector: '#createPlanModal',
            submitButtonSelector: '#createPlanBtn',
            loadingText: '{{ translate("Creating...") }}',
        });

        // Initialize edit plan modal
        @foreach ($premiumPlans as $premiumPlan)
        initAjaxModalForm({
            formSelector: '#editPlanForm-{{ $premiumPlan->id }}',
            modalSelector: '#editPlanModal-{{ $premiumPlan->id }}',
            submitButtonSelector: '#editPlanBtn-{{ $premiumPlan->id }}',
            loadingText: '{{ translate("Updating...") }}',
        });
        @endforeach

        // Featured label show/hide
        $(document).on('change', '.featured-toggle', function() {
            const modal = $(this).closest('.modal');
            const labelSection = modal.find('.featured-label-section');

            if (this.checked) {
                labelSection.slideDown(300);
            } else {
                labelSection.slideUp(300);
            }
        });
    </script>
@endpush
