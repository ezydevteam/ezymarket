@extends('admin.layouts.app')
@section('section',  translate('Messaging'))
@section('title',  translate('Chat History'))
@section('back', route('admin.chatbox.index'))
@section('container', 'container-max-xxl')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">{{ translate('Search Messages') }}</h5>
    </div>
    <div class="card-body py-3">
        <form class="row g-3" method="GET" action="{{ route('admin.chatbox.history') }}">
            {{-- user filter --}}
            <div class="col-6 col-lg-5">
                <label class="form-label">{{ translate('Sender ID') }}</label>
                <input type="number"
                       name="user_id"
                       class="form-control"
                       value="{{ request('user_id') }}"
                       placeholder="{{ translate('Exact user-id') }}">
            </div>

            {{-- keyword filter --}}
            <div class="col-6 col-lg-5">
                <label class="form-label">{{ translate('Keyword') }}</label>
                <input type="text"
                       name="keyword"
                       class="form-control"
                       value="{{ request('keyword') }}"
                       placeholder="{{ translate('Contents…') }}">
            </div>

            {{-- submit --}}
            <div class="col-6 col-lg-auto d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="fa fa-search me-1"></i> {{ translate('Search') }}
                </button>
            </div>
            <div class="col-6 col-lg-auto d-flex align-items-end">
                 <a href="{{ url()->current() }}" class="btn btn-secondary w-100">{{ translate('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

{{-- results --}}
<x-datatable
    id="chatHistoryTable"
    :items="$messages"
    tableClass="datatable2"
    emptyMessage="{{ translate('No messages found!') }}"
    emptyDescription="{{ translate('No messages found for the current filters.') }}"
    emptyIcon="bi-chat-dots"
>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ translate('Time') }}</th>
            <th>{{ translate('Conversation') }}</th>
            <th>{{ translate('Sender') }}</th>
            <th>{{ translate('Content') }}</th>
            <th>{{ translate('Filtered?') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($messages as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->created_at->format('Y-m-d H:i:s') }}</td>

                {{-- link to the conversation --}}
                <td>
                    <a href="#" target="_blank">
                        {{ $row->conversation->userOne->full_name }}
                        <span class="text-muted">↔</span>
                        {{ $row->conversation->userTwo->full_name }}
                    </a>
                </td>

                {{-- sender --}}
                <td>
                    <a href="{{ route('admin.roles.users.edit', $row->sender->id) }}" target="_blank">
                        {{ $row->sender->full_name }} (ID: {{ $row->sender_id }})
                    </a>
                </td>

                {{-- content --}}
                <td style="max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $row->content }}
                </td>

                <td>
                    @if($row->is_filtered)
                        <span class="badge bg-warning">{{ translate('Yes') }}</span>
                    @else
                        <span class="badge bg-success">{{ translate('No') }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</x-datatable>
@endsection



















