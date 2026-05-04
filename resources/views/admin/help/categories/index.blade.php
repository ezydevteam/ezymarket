@extends('admin.layouts.app')
@section('section', translate('Help Center'))
@section('title', translate('Help Center Categories'))
@section('create', route('admin.help.categories.create'))
@section('container', 'container-max-lg')
@section('content')
    <div class="card">
        <div class="card-header p-3 border-bottom-small">
            <form action="{{ request()->url() }}" method="GET">
                <div class="row g-3">
                    <div class="col-12 col-lg-10">
                        <input type="text" name="search" class="form-control" placeholder="{{ translate('Search...') }}"
                            value="{{ request()->input('search') ?? '' }}">
                    </div>
                    <div class="col">
                        <button class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="col">
                        <a href="{{ url()->current() }}" class="btn btn-secondary w-100">{{ translate('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
        <x-datatable :items="$categories" tableClass="sortable-table"
            emptyMessage="{{ translate('No categories found') }}"
            emptyDescription="{{ translate('All help center categories will appear here') }}"
            emptyIcon="bi-tags">
            <thead>
                <tr>
                    <th><i class="fa-solid fa-hashtag"></i></th>
                    <th>{{ translate('Name') }}</th>
                    <th class="text-center">{{ translate('Views') }}</th>
                    <th class="text-center">{{ translate('Published date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="sortable-table-tbody">
                @foreach ($categories as $category)
                            <tr data-id="{{ $category->id }}">
                                <td>
                                    <span class="sortable-table-handle me-2 text-muted">
                                        <i class="fas fa-arrows-alt fa-lg"></i>
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.help.categories.edit', $category->id) }}" class="text-dark">
                                        <i class="fa-solid fa-tag me-2"></i>
                                        <span>{{ $category->name }}</span>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark">{{ $category->total_views }}</span>
                                </td>
                                <td class="text-center">
                                    {{ dateFormat($category->created_at) }}
                                </td>
                                <td>
                                    <div class="text-end">
                                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                            <x-dropdown.item href="{{ $category->view_link }}"
                                                icon="bi-eye" iconClass="text-primary" target="_blank">
                                                {{ translate('View') }}
                                            </x-dropdown.item>
                                            <x-dropdown.item href="{{ route('admin.help.categories.edit', $category->id) }}"
                                                icon="bi-pencil" iconClass="text-primary">
                                                {{ translate('Edit') }}
                                            </x-dropdown.item>
                                            <x-dropdown.item type="divider" />
                                            <x-dropdown.item href="{{ route('admin.help.categories.destroy', $category->id) }}"
                                                icon="bi-trash" color="danger"
                                                data-method="DELETE"
                                                data-confirm="{{ translate('Are you sure you want to delete this category?') }}">
                                                {{ translate('Delete') }}
                                            </x-dropdown.item>
                                        </x-dropdown>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-datatable>
    </div>
    @push('top_scripts')
        <script>
            const sortingRoute = "{{ route('admin.help.categories.sortable') }}";
        </script>
    @endpush
    @push('styles_libs')
        <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" />
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
    @endpush
@endsection


















