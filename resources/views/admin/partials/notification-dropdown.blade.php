<div class="dropdown dropdown-notification">
  <button
    class="btn-icon header-btn"
    id="notificationDropdown"
    data-bs-toggle="dropdown"
    aria-expanded="false"
  >
    <i class="bi bi-bell fs-5 {{ $navbarNotifications['unread'] > 0 ? 'bell-ring-infinite' : '' }}"></i>
    @if($navbarNotifications['unread'] > 0)
      <span class="position-absolute start-50 translate-middle badge rounded-pill bg-danger text-white p-1 top-25">
        {{ $navbarNotifications['unread'] > 9 ? '9+' : $navbarNotifications['unread'] }}
        <span class="visually-hidden">unread notifications</span>
      </span>
    @endif
  </button>

  <div class="dropdown-menu codebay-dropdown-menu p-0" aria-labelledby="notificationDropdown" style="width: 360px">
    <!-- Header -->
    <div class="dropdown-header d-flex justify-content-between align-items-center bg-light">
      <h6 class="text-dark mb-0">
        {{ translate("Notifications") }} ({{ $navbarNotifications['unread'] }})
      </h6>
      @if($navbarNotifications['unread'] > 0)
        <form action="{{ route('admin.notifications.read.all') }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-sm btn-link text-muted p-0 text-decoration-none small hover-success action-confirm">
            {{ translate("Mark All as Read") }}
          </button>
        </form>
      @endif
    </div>

    <div class="dropdown-divider my-0"></div>

    <!-- Notification Items -->
    <div class="dropdown-body p-0">
      @forelse($navbarNotifications['list'] as $navbarNotification)
        @if($navbarNotification->link)
          <a
            class="dropdown-item notification-item position-relative py-2 {{ $navbarNotification->isUnread() ? 'unread' : '' }}"
            href="{{ route('admin.notifications.view', $navbarNotification->id) }}"
          >
            <div class="d-flex align-items-start">
              <img
                src="{{ $navbarNotification->image }}"
                alt="{{ $navbarNotification->title }}"
                class="rounded-circle me-3"
                width="40"
                height="40"
              />
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <p class="mb-1 fw-medium">{{ $navbarNotification->title }}</p>
                </div>
                <small class="text-muted">
                  {{ $navbarNotification->created_at->diffForHumans() }}
                </small>
              </div>
            </div>
          </a>
        @else
          <div class="dropdown-item notification-item position-relative py-2 {{ $navbarNotification->isUnread() ? 'unread' : '' }}">
            <div class="d-flex align-items-start">
              <img
                src="{{ $navbarNotification->image }}"
                alt="{{ $navbarNotification->title }}"
                class="rounded-circle me-3"
                width="40"
                height="40"
              />
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <p class="mb-1  fw-medium">{{ $navbarNotification->title }}</p>
                </div>
                <small class="text-muted">
                  {{ $navbarNotification->created_at->diffForHumans() }}
                </small>
              </div>
            </div>
          </div>
        @endif
        <div class="dropdown-divider my-0"></div>
      @empty
        <div class="text-center py-4">
          <small class="text-muted">{{ translate("No notifications found!") }}</small>
        </div>
      @endforelse
        <a
        class="dropdown-item text-center py-1 bg-light fw-medium hover-primary"
        href="{{ route('admin.notifications.index') }}"
        >
            {{ translate("View All") }}
        </a>
    </div>
  </div>
</div>
