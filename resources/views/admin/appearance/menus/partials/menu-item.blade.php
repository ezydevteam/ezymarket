<li class="dd-item" data-id="{{ $menu->id }}">
    <div class="dd-handle">
        <span class="drag-indicator {{ !$menu->is_active ? 'opacity-50' : '' }}"></span>
        <span class="dd-title {{ !$menu->is_active ? 'opacity-50' : '' }}">
            {{ $menu->name }}
              @if($menu->isMegaMenu())
                <span class="badge bg-text-primary ms-1">{{ translate('Mega-menu') }}</span>
            @endif
            @if($menu->hasBadge())
                <span class="badge bg-{{ $menu->badge_color?->value ?? 'primary' }} ms-1">{{ $menu->badge }}</span>
            @endif
            @if(!$menu->is_active)
                <span class="badge bg-secondary ms-1">{{ translate('Inactive') }}</span>
            @endif
        </span>
        <code class="text-muted ms-2 small d-none d-md-inline {{ !$menu->is_active ? 'opacity-50' : '' }}">{{ $menu->slug }}</code>
        <div class="dd-nodrag ms-auto d-flex align-items-center gap-3">
            <input type="checkbox" class="form-check-input row-checkbox m-0" value="{{ $menu->id }}">
            <div class="dropdown">
                <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical fs-6"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editMenuModal-{{ $menu->id }}">
                            <i class="bi bi-pencil-square text-primary me-2"></i>{{ translate('Edit Details') }}
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a href="{{ route('admin.appearance.menus.destroy', $menu->id) }}"
                           class="dropdown-item text-danger action-confirm"
                           data-method="DELETE"
                           data-confirm="{{ translate('Are you sure want to delete this menu? If it has child menus, they will also be deleted.') }}">
                           <i class="bi bi-trash me-2"></i>{{ translate('Delete') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @if ($menu->children->count() > 0)
        <ol class="dd-list">
            @foreach ($menu->children as $child)
                @include('admin.appearance.menus.partials.menu-item', ['menu' => $child, 'level' => $level + 1])
            @endforeach
        </ol>
    @endif
</li>
