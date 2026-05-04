@extends('themes.main.layouts.single')
@section('title', translate('Notifications'))
@section('container', 'container-custom')
@section('main')
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 py-2 border-bottom">
                <h2 class="mb-0 fs-4"><i class="bi bi-app-indicator small me-1"></i>
                    {{ translate('Notifications') }}
                    @if($unreadCount > 0)
                        <p class="text-muted text-small d-none d-md-inline mb-0">( {{ $totalCount }} @if($unreadCount > 0) {{ translate('total') . ', ' . $unreadCount . translate(' unread') }} @endif)</p>
                    @endif
                </h2>
                <div class="right-side-buttons">
                    @if($unreadCount > 0)
                    <button id="markAllRead" class="btn btn-outline-primary btn-sm btn-padding" title="{{ translate('Mark all as read') }}">
                        <i class="bi bi-envelope-open small"></i><span class="d-none d-lg-inline ms-2">{{ translate('Mark All as Read') }}</span>
                    </button>
                    @endif
                    @if($totalCount > 0)
                    <button id="deleteAll" class="btn btn-outline-danger btn-sm btn-padding ms-2" title="{{ translate('Delete all notifications') }}">
                        <i class="bi bi-trash small"></i><span class="d-none d-lg-inline ms-2">{{ translate('Delete All') }}</span>
                    </button>
                    @endif
                    <a href="{{ route('user.settings.notification.preferences') }}" class="ms-2">
                        <button class="btn btn-outline-secondary btn-sm btn-padding" title="{{ translate('Preferences') }}"><i class="bi bi-sliders small"></i><span class="d-none d-lg-inline ms-2">{{ translate('Preferences') }}</span></button>
                    </a>
                </div>
            </div>

            <div id="notifications-container">
                @forelse($notifications as $notification)
                    <a href="{{ $notification->data['action_url'] }}" class="all-notification-item {{ $notification->read_at ? 'read' : 'unread' }}" data-id="{{ $notification->id }}">
                        <div class="card border mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="notification-icon {{ $notification->data['color'] ?? 'success' }} me-3">
                                        @if(isset($notification->data['preview_image']))
                                            <img src="{{ $notification->data['preview_image'] }}" alt="thumbnail" />
                                        @elseif(isset($notification->data['icon']))
                                            <i class="bi bi-{{ $notification->data['icon'] }} text-{{ $notification->data['color'] ?? 'success' }}"></i>
                                        @else
                                            <i class="bi bi-bell text-primary"></i>
                                        @endif
                                    </div>
                                    <div class="notification-content d-block">
                                        <h5>{{ $notification->data['title'] ?? 'No Title' }}</h5>
                                        <p class="mb-0">{{ $notification->data['message'] ?? 'No Message' }}</p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <button class="btn btn-default text-danger p-0 ms-auto delete-notification" data-id="{{ $notification->id }}" title="{{ translate('Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="alert alert-info">No notifications found.</div>
                @endforelse
            </div>
            {{ $notifications->links() }}
        </div>
    </div>

<script>
    const markAllReadButton = document.getElementById('markAllRead');
    if (markAllReadButton) {
        markAllReadButton.addEventListener('click', function() {
            fetch('{{ route("notifications.mark-all-read", ["username" => $username]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            });
        });
    }

    const deleteAllButton = document.getElementById('deleteAll');
    if (deleteAllButton) {
        deleteAllButton.addEventListener('click', function() {
            if (confirm("{{ translate('Are you sure you want to delete all notifications?') }}")) {
                fetch('{{ route("notifications.delete-all", ["username" => $username]) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    toastr.error('An error occurred while deleting notifications');
                });
            }
        });
    }

    document.querySelectorAll('.delete-notification').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const notificationId = this.getAttribute('data-id');
            if (confirm("{{ translate('Are you sure you want to delete this notification?') }}")) {
                fetch('{{ route("notifications.delete", ["username" => $username, "id" => "__ID__"]) }}'.replace('__ID__', notificationId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notificationElement = this.closest('.all-notification-item');
                        notificationElement.remove();

                        toastr.success(data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    toastr.error('An error occurred while deleting the notification');
                });
            }
        });
    });
</script>
@endsection

