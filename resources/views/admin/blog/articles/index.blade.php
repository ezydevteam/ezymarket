@extends('admin.layouts.full')
@section('section', translate('Blog'))
@section('title', translate('Blog Articles'))
@section('content')
    <x-datatable
        id="blogArticleTable"
        :items="$articles"
        tableClass="datatable2"
        emptyMessage="{{ translate('No blog articles found!') }}"
        emptyDescription="{{ translate('Create your first blog article to get started') }}"
        emptyIcon="bi-file-text"
        emptyButton="{{ translate('Create Article') }}"
        emptyButtonRoute="{{ route('admin.blog.articles.create') }}"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Article') }}</th>
                <th class="text-center">{{ translate('Category') }}</th>
                <th class="text-center">{{ translate('Comments') }}</th>
                <th class="text-center">{{ translate('Views') }}</th>
                <th class="text-center">{{ translate('Create Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($articles as $article)
                <tr>
                    <td class="no-export">
                        <input type="checkbox"
                            class="form-check-input row-checkbox"
                            name="ids[]"
                            value="{{ $article->id }}">
                    </td>
                    <td>
                        <div class="ezydev-content-box">
                            <a class="image-fluid rounded me-2"
                                href="{{ route('admin.blog.articles.edit', $article->id) }}">
                                <img src="{{ $article->image_link }}" alt="{{ $article->title }}">
                            </a>
                            <div>
                                <a class="text-reset fw-semibold hover-primary"
                                    href="{{ route('admin.blog.articles.edit', $article->id) }}">
                                    {{ truncateText($article->title, 30) }}
                                </a>
                                <p class="text-muted small mb-0">
                                    {{ truncateText($article->short_description, 40) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.blog.categories.index', ['category' => $article->category->id]) }}">
                            <span class="badge bg-text-primary">{{ $article->category->name }}</span>
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-text-green">{{ numberFormat($article->comments_count) }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-text-dark">{{ numberFormat($article->total_views) }}</span>
                    </td>
                    <td class="text-center">{{ dateFormat($article->created_at) }}</td>
                    <td>
                        <div class="text-end">
                            <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                                <x-dropdown.item
                                    href="{{ $article->view_link }}"
                                    target="_blank"
                                    icon="bi bi-eye"
                                    iconClass="text-primary me-2">
                                    {{ translate('Preview') }}
                                </x-dropdown.item>
                                <x-dropdown.item
                                    href="{{ route('admin.blog.comments.index', 'article=' . $article->id) }}"
                                    icon="bi bi-chat-dots"
                                    iconClass="text-primary me-2">
                                    {{ translate('Comments') }}
                                </x-dropdown.item>
                                <x-dropdown.item
                                    href="{{ route('admin.blog.articles.edit', $article->id) }}"
                                    icon="bi bi-pencil-square"
                                    iconClass="text-primary me-2">
                                    {{ translate('Edit Details') }}
                                </x-dropdown.item>
                                <x-dropdown.item type="divider" />
                                <x-dropdown.item
                                    href="{{ route('admin.blog.articles.destroy', $article->id) }}"
                                    icon="bi bi-trash"
                                    color="danger"
                                    class="action-confirm"
                                    data-method="DELETE"
                                    data-confirm="{{ translate('Are you sure want to delete this article? This action can not be undone.') }}">
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
@push('top_scripts')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Blog Articles') }}";
    </script>
@endpush

@push('scripts_libs')
    <script>
        const tableElement = document.getElementById('blogArticleTable');
        if (tableElement) {
            const bulkActions = [
                {
                    text: '<i class="bi bi-trash me-1"></i> {{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger bulk-action-delete',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.blog.articles.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected articles?') }}"
                        });
                    }
                }
            ];

            $(tableElement).data('bulk-actions', bulkActions);

            const customButtons = [
                {
                    text: '<i class="bi bi-plus-lg me-1"></i> {{ translate("New Article") }}',
                    className: 'btn btn-primary',
                    action: function(e, dt, node, config) {
                        window.location.href = "{{ route('admin.blog.articles.create') }}";
                    }
                }
            ];

            $(tableElement).data('custom-buttons', customButtons);
        }
    </script>
@endpush

















