@extends('admin.layouts.app')
@section('title', translate('Pages'))
@section('container', 'container-max-xxl')
@section('content')
    <x-datatable
        id="pagesTable"
        :items="$pages"
        :empty-title="translate('No Pages Found')"
        :empty-desc="translate('Create your first page to get started')"
        :empty-icon="'bi-file-text'"
        :title="translate('All Pages')"
        :description="translate('Manage all pages')"
        :search-placeholder="translate('Search pages')"
        :custom-buttons="[
            [
                'text' => translate('Create Page'),
                'icon' => 'bi-plus-lg',
                'class' => 'btn btn-primary',
                'link' => route('admin.pages.create')
            ]
        ]"
        :bulk-delete-btn="[
            'text' => translate('Delete Selected'),
            'url' => route('admin.pages.bulk-destroy'),
            'confirm' => translate('Are you sure you want to delete the selected pages?'),
        ]"
    >
        <thead>
            <tr>
                <th class="no-sort">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Page Name') }}</th>
                <th class="text-center">{{ translate('Layout') }}</th>
                <th class="text-center">{{ translate('Total Views') }}</th>
                <th class="text-center">{{ translate('Created Date') }}</th>
                <th class="text-end no-sort">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pages as $page)
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $page->id }}">
                    </td>
                    <td>
                        <a href="{{ route('admin.pages.edit', $page->id) }}"
                            class="text-dark fw-semibold">{{ $page->title }}</a>
                    </td>
                    <td class="text-center text-muted">{{ $page->getLayout()->label() }}</td>
                    <td class="text-center"><span class="status-badge border text-gray-700">{{ numberFormat($page->total_views) }}</span></td>
                    <td class="text-center text-muted">{{ dateFormat($page->created_at) }}</td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="{{ $page->link }}"
                                    target="_blank"
                                    icon="bi bi-eye">
                                    {{ translate('Preview') }}
                                </x-dropdown.item>
                                <x-dropdown.item
                                    href="{{ route('admin.pages.edit', $page->id) }}"
                                    icon="bi bi-pencil-square">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    type="button"
                                    data-action="{{ route('admin.pages.destroy', $page->id) }}"
                                    icon="bi bi-trash"
                                    class="text-danger action-confirm"
                                    data-method="DELETE"
                                    data-text="{{ translate('Are you sure you want to delete this page?') }}">
                                    {{ translate('Delete') }}
                                </x-dropdown.item>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>
@endsection
