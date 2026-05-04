<div class="notification-dropdown" id="notificationDropdown">
	<div class="notification-header">
		<div class="d-flex justify-content-between align-items-center gap-2">
			<div class="notification-header-title d-flex align-items-center">
				<p class="fw-500 mb-0">{{ translate('Notifications') }}</p>
				<span class="notification-header-counter text-xsmall ms-1 d-none" id="headerUnreadCount">(0)</span>
			</div>
			<div class="notification-header-right">
				<div class="dropdown">
					<button class="btn dp-custom-btn" type="button" id="notificationMenuBtn" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="bi bi-three-dots-vertical"></i>
					</button>
					<ul class="dropdown-menu dropdown-menu-end border" aria-labelledby="notificationMenuBtn">
						<li class="my-1">
							<button class="dropdown-item btn btn-soft text-small" id="markAllReadBtn">
								<i class="bi bi-envelope-open me-1"></i>
								{{ translate('Mark All as Read') }}
							</button>
						</li>
						<li class="my-1">
							<button class="dropdown-item btn btn-soft text-small" id="soundToggleBtn">
								<i class="bi bi-volume-up me-1" id="soundIcon"></i>
								<span class="sound-toggle-text">{{ translate('Mute notification sound') }}</span>
							</button>
						</li>
						<li class="my-1">
							<a href="{{ route('user.settings.notification.preferences') }}" class="dropdown-item btn btn-soft text-small">
								<i class="bi bi-sliders me-1"></i>
								{{ translate('View preferences') }}
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="notification-list" id="notificationList"></div>
	<div class="notification-footer">
		<a href="{{ route('notifications.index', ['username' => auth()->user()->username ?? 'guest']) }}">
			{{ translate('View all notifications') }}
		</a>
	</div>
</div>
