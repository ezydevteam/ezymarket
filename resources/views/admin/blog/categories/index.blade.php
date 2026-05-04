@extends('admin.layouts.full')
@section('section', translate('Blog'))
@section('title', translate('Blog Categories'))
@section('content')
    <x-datatable
        id="blogCategoryTable"
        :items="$categories"
        tableClass="datatable2"
        emptyMessage="{{ translate('No categories found!') }}"
        emptyDescription="{{ translate('Create your first blog category to get started') }}"
        emptyIcon="bi-tag"
        emptyButton="{{ translate('Create Category') }}"
        emptyButtonModal="#createCategoryModal"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Name') }}</th>
                <th class="text-center">{{ translate('Articles') }}</th>
                <th class="text-center">{{ translate('Views') }}</th>
                <th class="text-center">{{ translate('Created Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td class="no-export">
                        <input type="checkbox"
                            class="form-check-input row-checkbox"
                            name="ids[]"
                            value="{{ $category->id }}">
                    </td>
                    <td>
                        <span class="text-truncate"
                            role="button"
                            data-bs-toggle="modal"
                            data-bs-target="#editCategoryModal-{{ $category->id }}">
                            {{ $category->name }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-text-primary">
                            {{ $category->articles_count }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-text-dark">
                            {{ numberFormat($category->total_views ?? 0) }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{ dateFormat($category->created_at) }}
                    </td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="{{ $category->view_link }}"
                                    target="_blank"
                                    icon="bi-eye"
                                    iconClass="text-primary me-2">
                                    {{ translate('Preview') }}
                                </x-dropdown.item>
                                <x-dropdown.item
                                    type="button"
                                    icon="bi-pencil-square"
                                    iconClass="text-primary me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCategoryModal-{{ $category->id }}">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.blog.categories.destroy', $category->id) }}"
                                    icon="bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure want to delete this blog category? This action can not be undone.') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    {{-- Create Modal --}}
    @include('admin.blog.categories.partials.create-modal')

    {{-- Edit Modals --}}
    @foreach ($categories as $category)
        @include('admin.blog.categories.partials.edit-modal', ['category' => $category])
    @endforeach
@endsection

@push('top_scripts')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Blog Categories') }}";
        let GET_SLUG_URL = "{{ route('admin.blog.categories.slug') }}";
    </script>
@endpush

@push('scripts_libs')
    <script>
        const tableElement = document.getElementById('blogCategoryTable');
        if (tableElement) {
            const bulkActions = [
                {
                    text: '<i class="bi bi-trash me-1"></i> {{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger bulk-action-delete',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.blog.categories.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected categories?') }}"
                        });
                    }
                }
            ];

            $(tableElement).data('bulk-actions', bulkActions);

            const customButtons = [
                {
                    text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("New Category") }}',
                    className: 'btn btn-primary',
                    action: function(e, dt, node, config) {
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


















