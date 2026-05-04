@extends('themes.main.layouts.single')
@section('title', translate('Chatbox'))

@section('main')

@push('styles')
<link rel="stylesheet" href="{{ theme_assets_with_version('assets/css/chatbox.css') }}">
@endpush

<div class="row g-0 Chatbox-container">
    {{-- Left Sidebar: Conversations --}}
    <div class="col-md-4 col-lg-3 conversation-section d-flex flex-column">
        <div class="conversations-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bolder">
                    <i class="bi bi-chat-square-dots-fill me-2"></i>
                    {{ translate('Chats') }}
                </h5>
                <button class="btn new-chat-btn" data-bs-toggle="modal" data-bs-target="#newChatModal" title="{{ translate('New Chat') }}">
                    <i class="bi bi-plus fs-4"></i>
                </button>
            </div>
        </div>
        <div class="flex-grow-1 overflow-auto" id="conversations-list"></div>
    </div>

    {{-- Right Side: Chat Window --}}
    <div class="col-md-8 col-lg-9 chat-window">
        {{-- Chat Header with Mobile Back Button --}}
        <div class="chat-header d-none" id="chat-header">
            <div class="d-flex align-items-center">
                <button class="mobile-back-btn" onclick="closeMobileChat()" type="button">
                    <i class="bi bi-arrow-left"></i>
                </button>
               <img id="recipient-avatar" src="" class="rounded-circle me-2" width="45" height="45">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold" id="recipient-name"></h6>
                    <small class="text-muted d-flex align-items-center mb-0" id="recipient-status">
                    </small>
                </div>
                <div class="dropdown">
                    <button class="btn dp-custom-btn fs-5" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border">
                        <li class="border-bottom mb-2"><a class="dropdown-item" href="#" id="view-profile">
                            <i class="bi bi-person me-2"></i>{{ translate('View Profile') }}
                        </a></li>
                        <li class="mb-2"><a class="dropdown-item text-warning" href="#" id="block-user-btn" data-user-blocked="false">
                            <i class="bi bi-ban me-2" id="block-icon"></i>
                            <span id="block-text">{{ translate('Block User') }}</span>
                        </a></li>
                        <li><a class="dropdown-item text-danger" href="#" id="delete-conversation">
                            <i class="bi bi-trash me-2"></i>{{ translate('Delete Chats') }}
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Messages Area --}}
        <div class="chat-messages" id="chat-messages">
            <div class="empty-state">
                <i class="bi bi-chat-square-dots-fill fs-1"></i>
                <h5 class="mt-3">{{ translate('Welcome to DiziPlace Chatbox') }}</h5>
                <p>{{ translate('Select a conversation to start messaging') }}</p>
            </div>
        </div>

        {{-- Message Input --}}
        <div class="message-input-area d-none" id="message-form-container">
            <form id="message-form">
                @csrf
                <div class="message-input-container">
                    <button type="button" class="emoji-btn" id="emoji-btn" title="{{ translate('Add Emoji') }}">
                        👍
                    </button>
                    <input type="text" id="message-input" placeholder="{{ translate('Type a message...') }}" maxlength="1000" autocomplete="off">
                    <button type="submit" class="send-btn" id="send-btn" disabled title="{{ translate('Send Message') }}">
                       <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>

            {{-- Emoji Picker Container --}}
            <div id="emoji-picker" class="emoji-picker d-none"></div>
        </div>
    </div>
</div>


{{-- New Chat Modal --}}
<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    {{ translate('Start New Chat') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="search-container mb-3">
                    <input type="text"
                           class="form-control"
                           id="user-search"
                           placeholder="{{ translate('Search by username, name, or ID...') }}"
                           autocomplete="off">
                </div>
                <div id="user-results" class="mt-3">
                    <div class="text-muted text-center py-4">
                        <i class="bi bi-search fs-4 text-muted"></i>
                        <div class="mt-2">{{ translate('Start typing to search users') }}</div>
                    </div>
                </div>
                <div id="no-results" class="text-center py-4 d-none">
                    <i class="bi bi-person-x-fill fs-4 text-muted"></i>
                    <div class="mt-2 text-muted">{{ translate('No users found') }}</div>
                    <small class="text-muted">{{ translate('Try searching by username, full name, or user ID') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Confirmation Modal for Block/Delete --}}
<div class="modal" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage"></p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmModalAction">{{ translate('Confirm') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection
