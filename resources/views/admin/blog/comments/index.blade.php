@extends('admin.layouts.full')
@section('section', translate('Blog'))
@section('title', translate('Blog Comments'))
@section('content')
    <x-datatable
        id="blogCommentTable"
        :items="$comments"
        tableClass="datatable2"
        emptyMessage="{{ translate('No comments found!') }}"
        emptyDescription="{{ translate('Comments will appear here once users start commenting') }}"
        emptyIcon="bi-chat-dots"
    >
        <thead>
            <tr>
                <th class="no-sort no-export">
                    <input type="checkbox" class="form-check-input bulk-select-checkbox">
                </th>
                <th>{{ translate('Commented By') }}</th>
                <th>{{ translate('Article') }}</th>
                <th>{{ translate('Comment') }}</th>
                <th class="text-center">{{ translate('Status') }}</th>
                <th class="text-center">{{ translate('Comment Date') }}</th>
                <th class="text-end no-sort no-export">{{ translate('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comments as $comment)
                <tr>
                    <td class="no-export">
                        <input type="checkbox"
                            class="form-check-input row-checkbox"
                            name="ids[]"
                            value="{{ $comment->id }}">
                    </td>
                    <td>
                       <x-user :user="$comment->user" />
                    </td>
                    <td>
                        <a href="{{ route('admin.blog.articles.edit', $comment->article->id) }}" class="text-reset hover-primary">
                            <i class="bi bi-file-text me-2"></i>{{ truncateText($comment->article->title, 25) }}
                        </a>
                    </td>
                    <td>
                        <span role="button" data-bs-toggle="modal" data-bs-target="#viewComment-{{ $comment->id }}">
                            <i class="bi bi-chat-dots me-2"></i>
                            {{ truncateText($comment->body, 30) }}
                        </span>
                    </td>
                    <td class="text-center">
                        {!! $comment->status_badge !!}
                    </td>
                    <td class="text-center">{{ dateFormat($comment->created_at) }}</td>
                    <td class="text-end">
                        <x-dropdown icon="bi-three-dots-vertical" buttonClass="btn-icon">
                            <x-dropdown.item
                                type="button"
                                icon="bi-pencil-square"
                                iconClass="text-primary me-2"
                                data-bs-toggle="modal"
                                data-bs-target="#viewComment-{{ $comment->id }}">
                                {{ translate('Take Action') }}
                            </x-dropdown.item>
                            <x-dropdown.item type="divider" />
                            <x-dropdown.item
                                href="{{ route('admin.blog.comments.destroy', $comment->id) }}"
                                icon="bi-trash"
                                color="danger"
                                class="action-confirm"
                                data-method="DELETE"
                                data-confirm="{{ translate('Are you sure want to delete this comment? This action can not be undone.') }}">
                                {{ translate('Delete') }}
                            </x-dropdown.item>
                        </x-dropdown>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-datatable>

    {{-- View Comment Modals --}}
    @foreach ($comments as $comment)
        @include('admin.blog.comments.partials.action-modal', ['comment' => $comment])
    @endforeach
@endsection

@push('top_scripts')
    <script>
        "use strict";
        config.translates.searchPlaceholder = "{{ translate('Search Blog Comments') }}";
    </script>
@endpush

@push('scripts_libs')
    <script>
        const tableElement = document.getElementById('blogCommentTable');
        if (tableElement) {
            const bulkActions = [
                 {
                    text: '<i class="bi bi-check-circle me-1"></i> {{ translate("Publish Selected") }}',
                    className: 'dropdown-item text-success bulk-action-btn',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.blog.comments.bulk-publish') }}",
                            method: 'POST',
                            confirmMessage: "{{ translate('Are you sure you want to publish the selected comments?') }}"
                        });
                    }
                },
                {
                    className: 'dropdown-item border-top my-1 p-0',
                },
                {
                    text: '<i class="bi bi-trash me-1"></i> {{ translate("Delete Selected") }}',
                    className: 'dropdown-item text-danger bulk-action-btn',
                    action: function(e, dt, node, config) {
                        bulkAction({
                            url: "{{ route('admin.blog.comments.bulk-delete') }}",
                            method: 'DELETE',
                            confirmMessage: "{{ translate('Are you sure you want to delete the selected comments?') }}"
                        });
                    }
                }
            ];

            $(tableElement).data('bulk-actions', bulkActions);
        }
    </script>
@endpush

















