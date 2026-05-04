@extends('themes.main.userpanel.layout')
@section('title', translate('Ticket - #:id', ['id' => $ticket->id]))
@section('breadcrumbs', Breadcrumbs::render('user.ticket.show', $ticket))
@section('container', 'userpanel-container-xl')

@section('content')
@themeInclude('userpanel.partials.restored-notice', ['model' => $ticket, 'type' => 'ticket'])

@php $currentUser = authUser(); @endphp

<div class="row g-4">
    {{-- Main Conversation Area --}}
    <div class="col-lg-8">
        {{-- Header Info Card --}}
        <div class="card card-body border-0 shadow-sm rounded-4 p-4 mb-4 bg-white bg-opacity-75">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="user-avatar rounded bg-primary-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-headset fs-3 text-primary"></i>
                    </div>
                </div>
                <div class="col">
                    <h4 class="mb-1 fw-bold text-dark">{{ $ticket->subject }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-gray-700 small">
                        <span title="{{ translate('Ticket ID') }}">
                            <i class="bi bi-hash"></i>{{ $ticket->id }}
                        </span>
                        <span title="{{ translate('Created at') }}">
                            <i class="bi bi-clock-history me-1"></i>{{ dateFormat($ticket->created_at) }}
                        </span>
                        <span title="{{ translate('Status') }}"
                            class="badge {{ $ticket->status_badge_class }} rounded-pill py-2 px-3 fw-normal">
                            <i class="{{ $ticket->status_icon }} me-1"></i>{{ $ticket->status_name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Message Thread --}}
        <div class="case-thread mt-2">
            @foreach ($ticket->replies as $index => $reply)
            @php
            $user = $reply->user;
            $admin = $reply->admin;
            $isMe = $user && $user->id == $currentUser->id;
            @endphp

            <div class="case-thread-item mb-4 {{ $index === 0 ? 'active' : '' }}">
                <div
                    class="card card-body rounded-4 mb-3 {{ $index === 0 ? 'border border-dashed border-primary' : 'border-0 shadow-sm' }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            @if ($user)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}"
                                class="user-avatar user-avatar-xs rounded border">
                            <div>
                                <h6 class="mb-0 fw-bold small text-dark">
                                    {{ $isMe ? translate('You') : $user->username }}
                                    @if (!$isMe)
                                    <span class="badge bg-secondary-subtle text-secondary ms-1 fw-normal fs-10">
                                        {{ translate('User') }}
                                    </span>
                                    @endif
                                </h6>
                            </div>
                            @else
                            <img src="{{ $admin->avatar_url }}" alt="Support"
                                class="user-avatar user-avatar-xs rounded border">
                            <div>
                                <h6 class="mb-0 fw-bold small text-dark">
                                    {{ superAdmin() ? translate('Admin') : $admin->username }}
                                    @if (!superAdmin())
                                    <span class="badge bg-primary-light text-primary ms-1 fw-normal fs-10">
                                        <i class="bi bi-shield-check me-1"></i>{{ translate('Support team') }}
                                    </span>
                                    @endif
                                </h6>
                            </div>
                            @endif
                        </div>
                        <time class="text-muted text-xsmall">{{ dateFormat($reply->created_at) }}</time>
                    </div>
                    <div class="message-text text-dark-emphasis">
                        {!! sanitizeHtml($reply->body, true) !!}
                    </div>

                    @if ($reply->attachments->count() > 0)
                    <div class="mt-4 pt-3 border-0 border-top border-dashed">
                        <div class="row g-2">
                            @foreach ($reply->attachments as $attachment)
                            <div class="col-12">
                                <a href="{{ route('user.ticket.download', [$ticket->id, $attachment->id]) }}"
                                    class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 border text-muted hover-primary transition-all">
                                    <i class="bi bi-file-earmark-arrow-down fs-5"></i>
                                    <div class="min-w-0">
                                        <div class="text-dark small fw-medium text-truncate">{{ $attachment->name }}
                                        </div>
                                        <div class="text-xsmall">{{ translate('Click to download') }}</div>
                                    </div>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Reply Form --}}
        <div class="card card-body border-0 shadow-sm mt-4 p-4 rounded-4" id="replySection">
            <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-dark">
                <i class="bi bi-reply-all text-primary"></i>{{ translate('Post a Reply') }}
            </h5>
            @if ($ticket->isClosed())
            <div class="alert alert-info border-0 rounded-4 bg-info-subtle mb-4">
                <div class="d-flex gap-3 align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 text-info"></i>
                    <div class="text-info-emphasis small">
                        {{ translate('This ticket is currently closed. Sending a reply will automatically re-open it.')
                        }}
                    </div>
                </div>
            </div>
            @endif
            <form action="{{ route('user.ticket.reply', $ticket->id) }}" method="POST" class="ajax-form"
                enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <textarea name="reply" class="form-control rounded-3 bg-light-subtle p-3" rows="5"
                        placeholder="{{ translate('Describe your issue or provide an update...') }}"
                        required>{{ old('reply') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">{{ translate('Add Attachments') }}</label>
                    <input type="file" name="attachments[]" class="form-control rounded-3" multiple>
                    <div class="form-text text-muted text-xsmall mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ translate('Allowed types: :types. Max size: :sizeMB per file.', ['types' =>
                        @settings('ticket')->file_types, 'size' => @settings('ticket')->max_file_size]) }}
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 px-5 shadow-sm fw-bold">
                        <i class="bi bi-send-fill me-2"></i>{{ translate('Send Response') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar Info Area --}}
    <div class="col-lg-4">
        <div class="ticket-sidebar">
            {{-- Ticket Info Card --}}
            <div class="card border-0 shadow-sm p-4 rounded-4 mb-4 overflow-hidden">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                    <h5 class="fw-bold text-dark mb-0">{{ translate('Ticket Overview') }}</h5>
                    <a href="{{ route('user.ticket.index') }}" class="btn btn-sm btn-outline-light text-dark fw-medium">
                        <i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}
                    </a>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div
                        class="d-flex justify-content-between align-items-center pb-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('Category') }}</span>
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-normal">{{
                            $ticket->category->name }}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('Status') }}</span>
                        <span class="badge {{ $ticket->status_badge_class }} px-3 py-2 rounded-pill">{{
                            $ticket->status_name }}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('Created') }}</span>
                        <span class="text-dark small">{{ dateFormat($ticket->created_at) }}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('ID Reference') }}</span>
                        <code class="text-primary bg-primary-light px-2 py-1 rounded small">#{{ $ticket->id }}</code>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="text-muted small fw-medium">{{ translate('Last Active') }}</span>
                        <span class="text-dark small">{{ $ticket->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Action Panel --}}
            @if ($ticket->canCancel() || $ticket->canDelete())
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h5 class="fw-bold text-dark mb-4">{{ translate('Take Action') }}</h5>
                <div class="d-grid gap-3">
                    @if ($ticket->canCancel())
                    <button type="button"
                        class="btn btn-warning rounded-pill py-2 px-4 shadow-sm fw-bold w-100 action-confirm"
                        data-action="{{ route('user.ticket.cancel', $ticket->id) }}" data-method="POST"
                        data-text="{{ translate('Are you sure you want to cancel this ticket?') }}">
                        <i class="bi bi-slash-circle me-2"></i>{{ translate('Cancel Ticket') }}
                    </button>
                    @endif

                    @if ($ticket->canDelete())
                    <button type="button"
                        class="btn btn-danger rounded-pill py-2 px-4 shadow-sm fw-bold w-100 action-confirm"
                        data-action="{{ route('user.ticket.destroy', $ticket->id) }}" data-method="DELETE"
                        data-text="{{ translate('Are you sure you want to delete this ticket history? This action is permanent.') }}">
                        <i class="bi bi-trash me-2"></i>{{ translate('Delete History') }}
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
