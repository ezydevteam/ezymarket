@php
$widgetClass = $instance->widget?->getWidgetInstance();
$isDeletable = !$widgetClass || $widgetClass->isDeletable();
@endphp
<div class="widget-instance p-2 mb-2 border rounded bg-white cursor-move transition-all"
    data-instance-id="{{ $instance->id }}" data-is-active="{{ $instance->is_active ? '1' : '0' }}"
    data-is-deletable="{{ $isDeletable ? '1' : '0' }}">
    <div class="d-flex align-items-center">
        <div
            class="widget-handle d-flex align-items-center flex-grow-1 gap-3 ms-2 {{ !$instance->is_active ? 'opacity-50' : '' }}">
            <i class="{{ $instance->widget->icon ?? 'bi bi-puzzle' }} text-gray-700"></i>
            <div class="flex-grow-1">
                <p class="widget-title fw-semibold fs-14 mb-0">{{ $instance->title ?: $instance->widget->title }}</p>
                <small class="text-muted">{{ $instance->widget->title }}</small>
            </div>
        </div>
        <div class="dropdown no-drag">
            <button type="button" class="btn text-muted cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <button type="button" class="dropdown-item btn-widget-settings">
                        <i class="bi bi-gear text-primary me-2"></i>{{ translate('Settings') }}
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item btn-widget-toggle">
                        <i
                            class="bi {{ $instance->is_active ? 'bi-eye-slash' : 'bi-eye' }} {{ $instance->is_active ? 'text-orange' : 'text-success' }} me-2"></i>
                        {{ $instance->is_active ? translate('Disable') : translate('Enable') }}
                    </button>
                </li>
                @if($isDeletable)
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a href="{{ route('admin.appearance.widgets.destroy',  $instance->id) }}"
                        data-confirm="{{ translate('Are you sure want to delete this widget? This action can not be undone.') }}"
                        data-method="DELETE" class="dropdown-item text-danger action-confirm">
                        <i class="bi bi-trash me-2"></i>{{ translate('Delete') }}
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
