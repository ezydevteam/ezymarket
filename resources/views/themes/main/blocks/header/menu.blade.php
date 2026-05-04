@php
    $id = $data['uniqueId'];
    $navMenu = $data['navMenu'] ?? collect([]);
    $style = $data['menuStyle'] ?? 'horizontal';
    $verticalLabel = $data['verticalLabel'] ?? translate('All Categories');
    $verticalIcon = $data['verticalIcon'] ?? '';
    $btnStyle = $data['btnStyle'] ?? 'primary';
    $btnSize = $data['btnSize'] ?? '';
    $hideDropdownIcon = $data['hideDropdownIcon'] ?? false;
@endphp

@if ($style === 'vertical')
    <div id="{{ $id }}" class="header-menu vertical">
        <button class="btn btn-{{ $btnStyle }} btn-{{ $btnSize }} fw-semibold px-2 vertical-menu-header">
            @if(!empty($verticalIcon))
                <i class="bi {{ $verticalIcon }} me-1"></i>
            @endif
            {{ $verticalLabel }}
            @if (!$hideDropdownIcon)
                <i class="bi bi-chevron-down text-xsmall ms-1"></i>
            @endif
        </button>
        <div class="vertical-menu-list">
            @foreach ($navMenu as $menu)
                {{-- Reuse similar structure but ensure CSS classes handle display --}}
                @if ($menu->children->count() > 0)
                    <div class="nav-dropdown" data-trigger-type="{{ $data['trigger_type'] ?? 'hover' }}">
                        <a href="{{ $menu->link ?? '#' }}"
                            {{ $menu->isExternal() ? 'target=_blank' : '' }}
                            class="nav-link dropdown-trigger {{ $menu->custom_class ?? '' }}">
                            <div class="d-flex align-items-center gap-2">
                                @if($menu->hasIcon())
                                    <i class="bi {{ $menu->icon }} text-{{ $menu->icon_color }}"></i>
                                @endif
                                @if(!$menu->shouldHideLabel())
                                    <span class="top-menu-title">{{ $menu->name }}</span>
                                @endif

                                @if ($menu->custom_html)
                                    {!! $menu->custom_html !!}
                                @endif
                                @if($menu->hasBadge())
                                    <span class="badge bg-{{ $menu->badge_color?->value ?? 'primary' }} ms-1">{{ $menu->badge }}</span>
                                @endif
                            </div>
                            <div class="ms-auto">
                                <i class="bi bi-chevron-right text-xsmall"></i>
                            </div>
                        </a>
                        <div class="nav-dropdown-menu {{ $menu->getMegaStyleClass() }}">
                             @foreach ($menu->children as $child)
                                <div class="nav-dropdown-item">
                                    @if ($child->children->count() > 0)
                                        <a href="{{ $child->link ?? '#' }}" class="nav-dropdown-link submenu-parent {{ $child->custom_class ?? '' }} {{ $child->isHeading() ? 'heading pe-none' : '' }}">
                                             @if($child->hasIcon()) <i class="bi {{ $child->icon }}"></i> @endif
                                             {{ $child->name }}
                                             <i class="bi bi-chevron-right ms-auto"></i>
                                        </a>
                                        <div class="nav-submenu">
                                            @foreach ($child->children as $grandchild)
                                                <div class="nav-submenu-item">
                                                    <a href="{{ $grandchild->link }}" class="nav-submenu-link {{ $grandchild->custom_class ?? '' }}">
                                                        {{ $grandchild->name }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <a href="{{ $child->link }}" class="nav-dropdown-link {{ $child->custom_class ?? '' }} {{ $child->isHeading() ? 'heading pe-none' : '' }}">
                                            @if($child->hasIcon()) <i class="bi {{ $child->icon }}"></i> @endif
                                            {{ $child->name }}
                                        </a>
                                    @endif
                                </div>
                             @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $menu->link }}" class="nav-link {{ $menu->custom_class ?? '' }}">
                         <div class="d-flex align-items-center gap-2">
                            @if($menu->hasIcon())
                                <i class="bi {{ $menu->icon }} text-{{ $menu->icon_color }}"></i>
                            @endif
                            <span>{{ $menu->name }}</span>
                        </div>
                        @if($menu->hasBadge())
                            <span class="badge bg-{{ $menu->badge_color?->value ?? 'primary' }} ms-1">{{ $menu->badge }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@else
<div id="{{ $id }}" class="header-menu">
    @foreach ($navMenu as $menu)

        @if ($menu->children->count() > 0)
            <div class="nav-dropdown" data-trigger-type="{{ $data['trigger_type'] ?? 'hover' }}">
                <a href="{{ $menu->link ?? '#' }}"
                    {{ $menu->isExternal() ? 'target=_blank' : '' }}
                    class="nav-link dropdown-trigger {{ $menu->custom_class ?? '' }}">
                    @if($menu->hasIcon())
                        <i class="bi {{ $menu->icon }} text-{{ $menu->icon_color }} me-1"></i>
                    @endif
                    @if(!$menu->shouldHideLabel())
                        <span class="top-menu-title">{{ $menu->name }}</span>
                    @endif
                    @if ($menu->custom_html)
                        {!! $menu->custom_html !!}
                    @endif
                    @if($menu->hasBadge())
                        <span class="badge bg-{{ $menu->badge_color?->value ?? 'primary' }} ms-1">{{ $menu->badge }}</span>
                    @endif
                    <i class="bi bi-chevron-down text-xsmall ms-1"></i>
                </a>
                <div class="nav-dropdown-menu {{ $menu->getMegaStyleClass() }}">
                    @foreach ($menu->children as $child)
                        <div class="nav-dropdown-item">
                            @if ($child->children->count() > 0)
                                {{-- Level 2: Has children (Level 3) --}}
                                @if(!$child->shouldHideLabel())
                                    <a href="{{ $child->link ?? '#' }}"
                                        {{ $child->isExternal() ? 'target=_blank' : '' }}
                                        class="nav-dropdown-link submenu-parent d-flex align-items-center w-100 {{ $child->custom_class ?? '' }} {{ $child->isHeading() ? 'heading pe-none' : '' }}">
                                        @if($child->hasIcon())
                                            <i class="bi {{ $child->icon }} text-{{ $child->icon_color }} me-1"></i>
                                        @endif
                                        @if($child->custom_html)
                                            <span>{!! $child->custom_html !!}</span>
                                        @endif
                                        {{ $child->name }}
                                        @if($child->hasBadge())
                                            <span class="badge bg-{{ $child->badge_color?->value ?? 'primary' }}">{{ $child->badge }}</span>
                                        @endif
                                        <span class="ms-auto"><i class="bi bi-chevron-right dropdown-arrow"></i></span>
                                    </a>
                                @endif
                                <div class="nav-submenu">
                                    @foreach ($child->children as $grandchild)
                                        <div class="nav-submenu-item">
                                            <a href="{{ $grandchild->link }}"
                                                {{ $grandchild->isExternal() ? 'target=_blank' : '' }}
                                                class="nav-submenu-link {{ $grandchild->custom_class ?? '' }}">
                                                @if($grandchild->hasIcon())
                                                    <i class="bi {{ $grandchild->icon }} text-{{ $grandchild->icon_color }} me-1"></i>
                                                @endif
                                                @if($grandchild->custom_html)
                                                    <span>{!! $grandchild->custom_html !!}</span>
                                                @endif
                                                @if(!$grandchild->shouldHideLabel())
                                                    {{ $grandchild->name }}
                                                @endif
                                                @if($grandchild->hasBadge())
                                                    <span class="badge bg-{{ $grandchild->badge_color?->value ?? 'primary' }}">{{ $grandchild->badge }}</span>
                                                @endif
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- Level 2: No children --}}
                                <a href="{{ $child->link }}"
                                    {{ $child->isExternal() ? 'target=_blank' : '' }}
                                    class="nav-dropdown-link {{ $child->custom_class ?? '' }}">
                                    @if($child->hasIcon())
                                        <i class="bi {{ $child->icon }} text-{{ $child->icon_color }} me-1"></i>
                                    @endif
                                    @if($child->custom_html)
                                        <span>{!! $child->custom_html !!}</span>
                                    @endif
                                    @if(!$child->shouldHideLabel())
                                        {{ $child->name }}
                                    @endif
                                    @if($child->hasBadge())
                                        <span class="badge bg-{{ $child->badge_color?->value ?? 'primary' }}">{{ $child->badge }}</span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <a href="{{ $menu->link }}"
                {{ $menu->isExternal() ? 'target=_blank' : '' }}
                class="nav-link {{ $menu->custom_class ?? '' }}">
                @if($menu->hasIcon())
                    <i class="bi {{ $menu->icon }} text-{{ $menu->icon_color }} me-1"></i>
                @endif
                @if($menu->custom_html)
                    <span class="me-1">{!! $menu->custom_html !!}</span>
                @endif
                @if(!$menu->shouldHideLabel())
                    {{ $menu->name }}
                @endif
                @if($menu->hasBadge())
                    <span class="badge bg-{{ $menu->badge_color?->value ?? 'primary' }} ms-1">{{ $menu->badge }}</span>
                @endif
            </a>
        @endif
    @endforeach
</div>
@endif
