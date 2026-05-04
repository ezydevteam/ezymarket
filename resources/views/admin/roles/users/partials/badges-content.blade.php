<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom-dashed">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-circle icon-circle-md bg-primary-subtle text-primary me-2">
                    <i class="bi bi-person-badge fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"> {{ translate('User Badges') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('Manage user badges') }}</p>
                </div>
            </div>
            <button class="btn btn-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addBadgeModal">
                <i class="bi bi-plus-lg me-1"></i>
                {{ translate('Add Badge') }}
            </button>
        </div>

        @if ($userBadges->count() > 0)
        <div class="list-wrapper">
            <ul class="list-group list-group-flush border rounded-4 overflow-hidden">
                @foreach ($userBadges as $userBadge)
                <li
                    class="list-group-item d-flex align-items-center py-3 px-4 bg-white hover-bg-light transition-base cursor-default">
                    <div class="image-fluid image-md me-3">
                        <img src="{{ $userBadge->badge->image_url }}" alt="{{ $userBadge->badge->name }}">
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-0 text-dark">{{ $userBadge->badge->name }}</h6>
                        @if($userBadge->badge->title)
                        <span class="text-muted small">{{ $userBadge->badge->title }}</span>
                        @endif
                    </div>
                    <div class="dropdown">
                        <button class="btn-icon" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li>
                                <a href="{{ route('admin.settings.badges.index', ['badge' => $userBadge->badge->id]) }}"
                                    class="dropdown-item py-2" target="_blank">
                                    <i class="bi bi-pencil-square me-2"></i>{{ translate('Edit Badge') }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <button type="button" class="dropdown-item py-2 text-danger action-confirm"
                                    data-action="{{ route('admin.roles.users.badges.destroy', [$user->id, $userBadge->id]) }}"
                                    data-method="DELETE"
                                    data-text="{{ translate('Are you sure you want to remove this badge from the user?') }}">
                                    <i class="bi bi-trash me-2"></i>{{ translate('Remove') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @else
        <div class="py-5 text-center bg-light-subtle rounded-4 border border-dashed">
            <i class="bi bi-award text-muted" style="font-size: 3rem;"></i>
            <h6 class="mt-3 text-muted">{{ translate('No badges assigned to this user yet.') }}</h6>
            <button class="btn btn-sm btn-soft-primary mt-2" data-bs-toggle="modal" data-bs-target="#addBadgeModal">
                {{ translate('Assign First Badge') }}
            </button>
        </div>
        @endif
    </div>
</div>

<x-modal id="addBadgeModal" title="{{ translate('Assign New Badge') }}" icon="bi bi-award">
    <form id="addBadgeForm" action="{{ route('admin.roles.users.badges.store', $user->id) }}" method="POST"
        class="ajax-form">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-medium">{{ translate('Select Badge') }}</label>
            <select name="badge" class="form-select form-select-lg selectpicker"
                title="{{ translate('Search performance or status badges...') }}" data-live-search="true"
                data-size="7" required>
                @foreach ($badges as $badge)
                <option value="{{ $badge->id }}"
                    data-content="<div class='d-flex align-items-center'><img src='{{ $badge->image_url }}' class='me-2' width='20' height='20'> <span>{{ $badge->name }}</span></div>">
                    {{ $badge->name }}</option>
                @endforeach
            </select>
            <div class="alert alert-info mt-4 p-2"><i class="bi bi-info-circle me-1"></i>{{ translate('Assigning a
                badge also upgrades it if already present.') }}</div>
        </div>
    </form>
    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary text-uppercase flex-fill" data-bs-dismiss="modal">{{ translate('Cancel')
            }}</button>
        <button type="submit" form="addBadgeForm" class="btn btn-primary text-uppercase flex-fill">{{ translate('Assign Badge') }}
        </button>
    </x-slot:footer>
</x-modal>
