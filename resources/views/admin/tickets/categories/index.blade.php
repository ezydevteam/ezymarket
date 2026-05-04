@extends('admin.layouts.full')
@section('section', translate('Tickets'))
@section('title', translate('Ticket Categories'))
@section('container', 'container-max-xxl')
@section('content')
    <x-datatable
        id="ticketCategoriesTable"
        :items="$categories"
        tableClass="datatable2 sortable-table"
        emptyMessage="{{ translate('No ticket categories found!') }}"
        emptyDescription="{{ translate('Create your first ticket category to organize support tickets') }}"
        emptyIcon="bi-folder"
    >
        <thead>
            <tr>
                <th class="no-sort">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox" title="{{ translate('Select all') }}">
                </th>
                <th class="no-sort"><i class="bi bi-sort-down fs-6"></i></th>
                <th>{{ translate('Category Name') }}</th>
                <th class="text-center">{{ translate('Total Tickets') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Published Date') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="sortable-table-tbody">
            @foreach ($categories as $category)
                <tr data-id="{{ $category->id }}">
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $category->id }}">
                    </td>
                    <td>
                        <span class="sortable-table-handle me-2 text-muted">
                            <i class="bi bi-arrows-move"></i>
                        </span>
                    </td>
                    <td>
                        <span role="button" data-bs-toggle="modal" data-bs-target="#editCategoryModal-{{ $category->id }}">{{ $category->name }}</span>
                    </td>
                    <td class="text-center">
                        {{ numberFormat($category->tickets_count ?? 0) }}
                    </td>
                    <td class="text-center">
                        {!! $category->status_badge !!}
                    </td>
                    <td class="text-center text-muted">
                        {{ dateFormat($category->created_at) }}
                    </td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="#"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCategoryModal-{{ $category->id }}"
                                    icon="bi bi-pencil-square"
                                    iconClass="text-primary me-2">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.tickets.categories.destroy', $category->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure want to delete this ticket category?') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
                {{-- Edit Modal --}}
                @include('admin.tickets.categories.partials.edit-modal')
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Create Category Modal --}}
    @include('admin.tickets.categories.partials.create-modal')

@endsection

@push('top_scripts')
    <script>
        const sortingRoute = "{{ route('admin.tickets.categories.sortable') }}";
    </script>
@endpush
@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush
@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>

    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Ticket Category') }}";

        const tableElement = document.getElementById('ticketCategoriesTable');
        if (tableElement) {
            const bulkActions = [
            {
                text: '<i class="bi bi-x-circle text-orange me-2"></i>{{ translate("Inactive Selected") }}',
                className: 'dropdown-item',
                action: function(e, dt, node, config) {
                    bulkAction({
                        url: "{{ route('admin.tickets.categories.bulk-inactive') }}",
                        confirmMessage: "{{ translate('Are you sure you want to inactive the selected ticket categories?') }}"
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
                        url: "{{ route('admin.tickets.categories.bulk-delete') }}",
                        method: 'DELETE',
                        confirmMessage: "{{ translate('Are you sure you want to delete the selected ticket categories?') }}"
                    });
                }
            }
        ];

        $(tableElement).data('bulk-actions', bulkActions);

        const customButtons = [
            {
                text: `<i class="bi bi-folder-plus me-1"></i>{{ translate('Add New Category') }}`,
                className: 'btn btn-primary',
                action: function () {
                    $('#createCategoryModal').modal('show');
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
        // Initialize create category modal form
        initAjaxModalForm({
            formSelector: '#createCategoryForm',
            modalSelector: '#createCategoryModal',
            submitButtonSelector: '#createCategoryBtn',
            loadingText: '{{ translate("Creating...") }}',
            successMessage: '{{ translate("Category Created Successfully") }}',
            reloadOnSuccess: true
        });

        // Initialize edit category modal forms
        @foreach ($categories as $category)
            initAjaxModalForm({
                formSelector: '#editCategoryForm-{{ $category->id }}',
                modalSelector: '#editCategoryModal-{{ $category->id }}',
                submitButtonSelector: '#editCategoryBtn-{{ $category->id }}',
                loadingText: '{{ translate("Updating...") }}',
                successMessage: '{{ translate("Category Updated Successfully") }}',
                reloadOnSuccess: true
            });
        @endforeach
    </script>
@endpush
















