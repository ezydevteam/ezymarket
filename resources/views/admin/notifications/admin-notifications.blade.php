@extends('admin.layouts.app')
@section('title', translate('All Notifications'))
@section('description', translate('View and manage all notifications here. Stay updated with the latest alerts and messages.'))
@section('container', 'container-max-md')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6">
                    <div class="mb-2">
                        <i class="fas fa-bell fa-2x text-primary"></i>
                    </div>
                    <h4 class="mb-0">{{ $totalNotifications }}</h4>
                    <p class="text-muted mb-0">{{ translate('Total Notifications') }}</p>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <i class="fas fa-envelope fa-2x text-danger"></i>
                    </div>
                    <h4 class="mb-0">{{ $unreadCount }}</h4>
                    <p class="text-muted mb-0">{{ translate('Unread Notifications') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="all-notification">
        @forelse ($adminNotifications as $adminNotification)
            @if ($adminNotification->link)
                <a href="{{ route('admin.notifications.view', $adminNotification->id) }}"
                    class="notification-item {{ $adminNotification->isUnread() ? 'unread' : '' }} d-flex justify-content-between align-items-center">
                    <div class="flex-shrink-0">
                        <img src="{{ $adminNotification->image }}" alt="{{ $adminNotification->title }}" class="notification-image">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ $adminNotification->title }}</h5>
                        <p class="mb-0 text-muted">{{ $adminNotification->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            @else
                <div
                    class="notification-item {{ $adminNotification->isUnread() ? 'unread' : '' }} d-flex justify-content-between align-items-center">
                    <div class="flex-shrink-0">
                        <img src="{{ $adminNotification->image }}" alt="{{ $adminNotification->title }}" class="notification-image">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ $adminNotification->title }}</h5>
                        <p class="mb-0 text-muted">{{ $adminNotification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endif
        @empty
            <div class="card">
                <div class="card-body">
                    @include('admin.partials.empty', ['empty_classes' => 'empty-lg'])
                </div>
            </div>
        @endforelse
    </div>
    {{ $adminNotifications->links() }}
@endsection
