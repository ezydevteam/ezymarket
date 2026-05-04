@extends('admin.layouts.app')
@section('section', translate('Categories'))
@section('title', translate('Product Sub Categories'))
@section('container', 'container-max-xxl')
@section('content')
    <x-datatable
        id="subCategoriesTable"
        table-class="sortable-table table-hover"
        :items="$subCategoriesCount"
        :title="translate('Sub Categories')"
        :description="translate('Manage sub-categories to organize your products')"
        :columns="$columns"
        :ajax-url="route('admin.products.categories.sub-categories.index')"
        :server-side="true"
        :searchPlaceholder="translate('Search sub-categories...')"
        :sorting-route="route('admin.products.categories.sub-categories.sortable')"
        :custom-buttons="[
            [
                'text' => translate('Create Sub Category'),
                'icon' => 'bi-plus-lg',
                'class' => 'btn btn-primary',
                'type' => 'modal',
                'target' => '#createSubCategoryModal',
                'action' => route('admin.products.categories.sub-categories.create.modal')
            ]
        ]"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.products.categories.sub-categories.bulk-destroy'),
            'confirm' => translate('Are you sure you want to delete the selected sub-categories?'),
        ]"
        :empty-title="translate('No Sub-Categories Found')"
        :empty-desc="translate('Create your first sub-category to organize products.')"
        :empty-icon="'bi-folder2-open'"
        :empty-btn-modal="'#createSubCategoryModal'"
        :empty-btn-modal-text="translate('Create Sub Category')"
        :empty-btn-modal-action="route('admin.products.categories.sub-categories.create.modal')"
    >
    </x-datatable>

    <x-modal id="createSubCategoryModal" :header="false" size="lg" />
    <x-modal id="editSubCategoryModal" :header="false" size="lg" />
@endsection

@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
@endpush
