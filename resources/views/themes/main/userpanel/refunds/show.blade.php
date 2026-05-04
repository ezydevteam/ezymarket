@extends('themes.main.userpanel.layout')
@section('section', translate('Refund for ' . $refund->purchase->product->name))

@section('content')
@themeInclude('userpanel.partials.restored-notice', ['model' => $refund, 'type' => 'refund'])

@php
$purchase = $refund->purchase;
$product = $purchase->product;
$currentUser = authUser();
$isBuyer = $refund->user_id == $currentUser->id;
$isSeller = $refund->seller_id == $currentUser->id;
@endphp

<div class="row g-4">
    {{-- Main Conversation Area --}}
    <div class="col-lg-8">
        {{-- Header Info Card --}}
        <div class="card card-body border-0 shadow-sm rounded-4 p-4 mb-4 bg-white bg-opacity-75">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="user-avatar rounded bg-primary-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-chat-dots-fill fs-3 text-primary"></i>
                    </div>
                </div>
                <div class="col">
                    <h4 class="mb-1 fw-bold text-dark">{{ $refund->subject }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-gray-700 small">
                        <span title="{{ translate('Refund ID') }}">
                            <i class="bi bi-hash"></i>{{ $refund->id }}
                        </span>
                        <span title="{{ translate('Created at') }}">
                            <i class="bi bi-clock-history me-1"></i>{{ dateFormat($refund->created_at) }}
                        </span>
                        <span title="{{ translate('Status') }}"
                            class="badge {{ $refund->status_badge_class }} rounded-pill py-2 px-3 fw-normal">
                            <i class="{{ $refund->status_icon }} me-1"></i>{{ $refund->status_name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Message Thread --}}
        <div class="case-thread mt-2">
            @foreach ($refund->replies as $index => $reply)
            @php
            $user = $reply->user;
            $isMe = $user->id == $currentUser->id;
            $roleLabel = '';
            if ($user->id == $refund->seller_id) $roleLabel = translate('Seller');
            elseif ($user->id == $refund->user_id) $roleLabel = translate('Buyer');
            @endphp

            <div class="case-thread-item mb-4 {{ $index === 0 ? 'active' : '' }}">
                <div
                    class="card card-body rounded-4 mb-3 {{ $index === 0 ? 'border border-dashed border-primary' : 'border-0 shadow-sm' }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}"
                                class="user-avatar user-avatar-xs rounded border ">
                            <div>
                                <h6 class="mb-0 fw-bold small text-dark">
                                    {{ $isMe ? translate('You') : $user->username }}
                                    @if (!$isMe && $roleLabel)
                                    <span class="badge bg-primary-light text-primary ms-1 fw-normal fs-10">
                                        <i class="bi bi-person-fill me-1"></i>{{ $roleLabel }}
                                    </span>
                                    @endif
                                </h6>
                            </div>
                        </div>
                        <time class="text-muted text-xsmall">{{ dateFormat($reply->created_at) }}</time>
                    </div>
                    <div class="message-text text-dark-emphasis">
                        {!! sanitizeHtml($reply->message, true) !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Reply Form --}}
        @if ($refund->isPending())
        <div class="card card-body border-0 shadow-sm mt-4 p-4 rounded-4">
            <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-dark">
                <i class="bi bi-reply-all text-primary"></i>{{ translate('Post a Reply') }}
            </h5>
            <form action="{{ route('user.refund.reply', $refund->id) }}" method="POST" class="ajax-form">
                @csrf
                <div class="mb-4">
                    <textarea name="reply" class="form-control rounded-3 bg-light-subtle p-3" rows="5"
                        placeholder="{{ translate('Write your response here...') }}" required></textarea>
                    <div class="form-text text-muted small mt-2">
                        <i class="bi bi-info-circle me-1"></i>{{ translate('Maximum 2000 characters allowed.') }}
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 px-5 shadow-sm fw-bold">
                        <i class="bi bi-send-fill me-2"></i>{{ translate('Send Response') }}
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- Sidebar Info Area --}}
    <div class="col-lg-4">
        <div class="refund-sidebar">
            {{-- Product Card --}}
            <div class="card border-0 shadow-sm p-4 rounded-4 mb-4 overflow-hidden">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                    <h5 class="fw-bold text-dark">{{ translate('Purchase Details') }}</h5>
                    <a href="{{ route('user.refund.index') }}" class="btn btn-sm btn-outline-light text-dark fw-medium">
                        <i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}
                    </a>
                </div>
                <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3 transition-all border">
                    <div class="user-avatar flex-shrink-0">
                        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
                    </div>
                    <div class="min-w-0">
                        <a href="{{ $product->view_link }}" class="text-dark fw-bold hover-primary d-block">
                            {{ $product->name }}
                        </a>
                        <span class="text-muted small d-block">#{{ $purchase->id }}</span>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div
                        class="d-flex justify-content-between align-items-center pb-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('License') }}</span>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">{{
                            $purchase->license_type_name }}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('Price Paid') }}</span>
                        <span class="text-dark fw-bold">{{ getAmount($purchase->sale->price) }}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('Purchased') }}</span>
                        <span class="text-dark small">{{ dateFormat($purchase->created_at) }}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-2 border-0 border-bottom border-dashed">
                        <span class="text-muted small fw-medium">{{ translate('Code') }}</span>
                        <code class="text-primary bg-primary-light px-2 py-1 rounded small">{{ $purchase->code }}</code>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="text-muted small fw-medium">{{ translate('Downloaded') }}</span>
                        @if ($purchase->isDownloaded())
                        <span class="text-success small fw-bold"><i class="bi bi-check2-circle me-1"></i>{{
                            translate('Yes') }}</span>
                        @else
                        <span class="text-muted small">{{ translate('No') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Appeal Alert --}}
            @if (@$settings->ticket->status && $isBuyer && $refund->isDeclined())
            <div class="alert alert-warning border-0 shadow-sm p-4 rounded-4 mb-4">
                <div class="d-flex gap-3">
                    <i class="bi bi-shield-lock-fill fs-2 text-warning"></i>
                    <div>
                        <h6 class="fw-bold text-dark">{{ translate('Need help with this decision?') }}</h6>
                        <p class="small text-gray-700 mb-3">{{ translate('If you’re not satisfied with the seller’s
                            refund denial, you can create a ticket to appeal.') }}</p>
                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#createTicketModal" data-action="{{ route('user.ticket.modal.create') }}">
                            {{ translate('Appeal Decision') }}
                        </button>
                    </div>
                </div>
            </div>
            @themeInclude('userpanel.tickets.partials.create-ticket', ['categories' => $ticketCategories])
            @endif

            {{-- Action Panel --}}
            @if (($isSeller && $refund->isPending()) || ($isBuyer && ($refund->canCancel() || $refund->canDelete())))
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h5 class="fw-bold text-dark mb-4">{{ translate('Take Action') }}</h5>
                <div class="d-grid gap-3">
                    {{-- Seller Actions --}}
                    @if ($isSeller && $refund->isPending())
                    <button type="button"
                        class="btn btn-success rounded-pill py-2 px-4 shadow-sm fw-bold w-100 action-confirm"
                        data-action="{{ route('user.refund.accept', $refund->id) }}" data-method="POST"
                        data-text="{{ translate('Are you sure you want to accept this refund request?') }}">
                        <i class="bi bi-check2-circle me-2"></i>{{ translate('Accept Refund') }}
                    </button>

                    <button class="btn btn-outline-danger rounded-pill py-2 px-4 fw-bold w-100"
                        data-slide-toggle="#declineRequestForm">
                        <i class="bi bi-x-circle me-2"></i>{{ translate('Decline Request') }}
                    </button>

                    <div id="declineRequestForm" class="mt-2 d-none">
                        <div class="bg-danger-subtle p-3 rounded-3 border border-danger">
                            <form action="{{ route('user.refund.decline', $refund->id) }}" method="POST"
                                class="ajax-form">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-danger">{{ translate('Decline Reason')
                                        }}</label>
                                    <textarea name="reason" class="form-control border-danger-subtle rounded-3" rows="4"
                                        placeholder="{{ translate('Explain why you are declining...') }}"
                                        required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill py-2 fw-bold">
                                    {{ translate('Confirm Decline') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Buyer Actions --}}
                    @if ($isBuyer)
                    @if ($refund->canCancel())
                    <button type="button"
                        class="btn btn-warning rounded-pill py-2 px-4 shadow-sm fw-bold w-100 action-confirm"
                        data-action="{{ route('user.refund.cancel', $refund->id) }}" data-method="POST"
                        data-text="{{ translate('Are you sure you want to cancel this refund request?') }}">
                        <i class="bi bi-slash-circle me-1"></i> {{ translate('Cancel Request') }}
                    </button>
                    @endif

                    @if ($refund->canDelete())
                    <button type="button"
                        class="btn btn-danger rounded-pill py-2 px-4 shadow-sm fw-bold w-100 action-confirm"
                        data-action="{{ route('user.refund.destroy', $refund->id) }}" data-method="DELETE"
                        data-text="{{ translate('Are you sure you want to delete this refund request?') }}">
                        <i class="bi bi-trash me-1"></i> {{ translate('Delete') }}
                    </button>
                    @endif
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<x-modal id="createTicketModal" :header="false" />
@endsection
