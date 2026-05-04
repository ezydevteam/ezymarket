@extends('admin.layouts.app')
@section('section', translate('Tickets'))
@section('title', translate('Support Ticket'))
@section('description', translate('View and manage ticket #:id', ['id' => $ticket->id]))
@section('back', route('admin.tickets.index'))
@section('content')
    <x-archived-alert :model="$ticket" 
        :restoreRoute="route('admin.tickets.restore', $ticket->id)" 
        :deleteRoute="route('admin.tickets.destroy', $ticket->id)" />
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            {{-- Ticket Conversation --}}
            @foreach ($ticket->replies as $reply)
                <div class="card mb-3 {{ $loop->first ? 'border-primary' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                @if ($reply->user)
                                    <a href="{{ route('admin.roles.users.edit', $reply->user->id) }}" class="image-fluid image-md rounded">
                                        <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->username }}">
                                    </a>
                                    <div>
                                        <a href="{{ route('admin.roles.users.edit', $reply->user->id) }}" class="fw-semibold text-dark">
                                            {{ $reply->user->full_name }}
                                        </a>
                                        <div class="text-muted small">{{ $reply->user->isSeller() ? translate('Seller') : translate('Buyer') }}</div>
                                    </div>
                                @else
                                    @if ($reply->admin->id == superAdmin()->id)
                                        <div class="image-fluid image-md rounded">
                                            <img src="{{ $reply->admin->avatar_url }}" alt="{{ $reply->admin->username }}">
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $reply->admin->full_name }}</div>
                                            <div class="text-muted small">
                                                <i class="bi bi-shield-check text-primary me-1"></i>{{ translate('Support Team') }}
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ route('admin.roles.managers.edit', $reply->admin->id) }}" class="image-fluid image-md rounded">
                                            <img src="{{ $reply->admin->avatar_url }}" alt="{{ $reply->admin->username }}">
                                        </a>
                                        <div>
                                            <a href="{{ route('admin.roles.managers.edit', $reply->admin->id) }}" class="fw-semibold text-dark">
                                                {{ $reply->admin->full_name }}
                                            </a>
                                            <div class="text-muted small">
                                                <i class="bi bi-shield-check text-primary me-1"></i>{{ translate('Support Team') }}
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ dateFormat($reply->created_at) }}
                            </small>
                        </div>

                        @if ($loop->first)
                            <div class="alert alert-light border mb-3">
                                <i class="bi bi-ticket-detailed text-primary me-2"></i>
                                <strong>{{ $ticket->subject }}</strong>
                            </div>
                        @endif

                        <div class="mb-0">
                            {!! sanitizeHtml($reply->body, true) !!}
                        </div>

                        @if ($reply->attachments->count() > 0)
                            <div class="mt-3 pt-3 border-top">
                                <p>
                                    <i class="bi bi-paperclip"></i>
                                    {{ $reply->attachments->count() }} {{ $reply->attachments->count() > 1 ? translate('Files Attached') : translate('File Attached') }}
                                </p>
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($reply->attachments as $attachment)
                                        <a href="{{ route('admin.tickets.download', [$ticket->id, $attachment->id]) }}"
                                            class="d-flex align-items-center gap-3 p-2 bg-light rounded">
                                            <div class="bg-white rounded p-2">
                                                <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium text-dark">{{ truncateText($attachment->name, 30) }}</div>
                                                <small class="text-muted">{{ dateFormat($attachment->created_at) }}</small>
                                            </div>
                                            <i class="bi bi-download text-success"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Reply Form --}}
            <div class="card">
                <div class="card-body p-4">
                    @if ($ticket->isOpened())
                        <form id="replyTicketForm-{{ $ticket->id }}" action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST"
                            enctype="multipart/form-data" class="ajax-form">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-reply me-2"></i>{{ translate('Your Reply') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea name="reply" class="form-control" rows="6"
                                    placeholder="{{ translate('Type your response here...') }}"
                                    required>{{ old('reply') }}</textarea>
                            </div>
                            <div class="mb-4">
                                <div class="attachments">
                                    <div class="attachment-box-1">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-paperclip me-2"></i>{{ translate('Attachments') }}
                                            <span class="text-muted small">({{ translate('Optional') }})</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="file" name="attachments[]" class="form-control">
                                            <button id="addAttachment" class="btn bg-text-secondary" type="button">
                                                <i class="bi bi-plus-lg me-1"></i>{{ translate('Add More') }}
                                            </button>
                                        </div>
                                        <small class="form-text text-muted mt-1">
                                            {{ translate('Maximum file size: :size MB', ['size' => @settings('ticket')->max_file_size ?? 10]) }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" id="replyTicketBtn-{{ $ticket->id }}" class="btn btn-primary btn-lg px-4 submit-reply-btn">
                                <i class="bi bi-send me-2"></i>{{ translate('Send Reply') }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning mb-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lock fs-3 me-3"></i>
                                <div>
                                    <strong class="d-block mb-1">{{ translate('Ticket Closed') }}</strong>
                                    <span>{{ translate('This ticket is closed. Reopen it to send new replies.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-12 col-xl-4">
            {{-- Ticket Information Card --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ translate('Ticket Information') }}
                    </h6>
                </div>
                <div class="card-body px-0 pt-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item pb-3">
                            <div class="row align-items-center g-3">
                                <div class="col">
                                    <i class="bi bi-hash text-muted me-1"></i>
                                    {{ translate('Ticket ID') }}
                                </div>
                                <div class="col-auto">
                                    #{{ $ticket->id }}
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item py-3">
                            <div class="row align-items-center g-3">
                                <div class="col">
                                    <i class="bi bi-tag text-muted me-1"></i>
                                    {{ translate('Category') }}
                                </div>
                                <div class="col-auto">
                                    <a href="{{ route('admin.tickets.categories.index', ['category' => $ticket->category->id]) }}"
                                        class="text-dark text-decoration-none">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-folder me-1"></i>
                                            {{ $ticket->category->name }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item py-3">
                            <div class="row align-items-center g-3">
                                <div class="col">
                                    <i class="bi bi-flag text-muted me-1"></i>
                                    {{ translate('Status') }}
                                </div>
                                <div class="col-auto">
                                    <span class="badge {{ $ticket->status_badge_class }}">
                                        <i class="bi {{ $ticket->status_icon }} me-1"></i>
                                        {{ $ticket->status_name }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item py-3">
                            <div class="row align-items-center g-3">
                                <div class="col">
                                    <i class="bi bi-calendar-plus text-muted me-1"></i>
                                    {{ translate('Created Date') }}
                                </div>
                                <div class="col-auto">
                                    <small class="text-muted">{{ dateFormat($ticket->created_at) }}</small>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item py-3">
                            <div class="row align-items-center g-3">
                                <div class="col">
                                    <i class="bi bi-clock-history text-muted me-1"></i>
                                    {{ translate('Last Activity') }}
                                </div>
                                <div class="col-auto">
                                    <small class="text-muted">{{ dateFormat($ticket->updated_at) }}</small>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item pt-3">
                            <div class="row align-items-center g-3">
                                <div class="col">
                                    <i class="bi bi-chat-left-text text-muted me-1"></i>
                                    {{ translate('Total Replies') }}
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-text-primary">{{ $ticket->replies->count() }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            @if ($ticket->isOpened())
                <div class="card">
                    <div class="card-body px-3 py-4">
                        <form action="{{ route('admin.tickets.close', $ticket->id) }}" method="POST" class="ajax-form">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg w-100 close-ticket-btn action-confirm" data-confirm="{{ translate('Are you sure want to close this ticket?') }}">
                                <i class="bi bi-x-circle me-2"></i>
                                {{ translate('Close Ticket') }}
                            </button>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="notify_user" value="1" id="notifyUser-{{ $ticket->id }}" checked>
                                <label class="form-check-label" for="notifyUser-{{ $ticket->id }}">
                                    {{ translate('Notify user about ticket closure') }}
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection



















