@php
    $uniqueId = $data['uniqueId'];
    $menuItems = $data['menuItems'];
    $style = $data['style'];
    $rootCount = $data['rootCount'];
    $colClass = $data['colClass'];
@endphp

<div id="{{ $uniqueId }}" class="footer-menu">
    @if($rootCount > 0)
        @if($style === 'columns')
            <div class="row g-4 justify-content-between">
                @foreach($menuItems as $menu)
                    <div class="{{ $colClass }}">
                        {{-- Level 1: Column Header / Root Link --}}
                        @if($menu->children->isNotEmpty())
                            <h6 class="mb-3 root-menu-item {{ $menu->hide_name ? 'd-none' : '' }}">
                                {{ $menu->name }}
                            </h6>

                            {{-- Level 2: Child Links --}}
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                @foreach($menu->children as $child)
                                    <li>
                                        <a href="{{ $child->link }}"
                                           class="text-reset custom-menu-item child-menu-item transition-all {{ $child->custom_class }}"
                                           target="{{ $child->target ?? '_self' }}">
                                            @if($child->icon) <i class="{{ $child->icon }} me-1" style="color: {{ $child->icon_color }}"></i> @endif
                                            {{ $child->name }}
                                        </a>
                                        {{-- Level 3: Grandchild Links --}}
                                        @if($child->children->isNotEmpty())
                                            <ul class="list-unstyled ps-3 mt-1 d-flex flex-column gap-1 border-start ms-1" style="border-color: rgba(var(--bs-body-color-rgb), 0.1) !important;">
                                                @foreach($child->children as $grandChild)
                                                     <li>
                                                        <a href="{{ $grandChild->link }}"
                                                           class="text-reset custom-menu-item child-menu-item transition-all {{ $grandChild->custom_class }}"
                                                           target="{{ $grandChild->target ?? '_self' }}">
                                                            @if($grandChild->icon) <i class="{{ $grandChild->icon }} me-1" style="color: {{ $grandChild->icon_color }}"></i> @endif
                                                            {{ $grandChild->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            {{-- Root Item without children (Standalone Link) --}}
                             <a href="{{ $menu->link }}"
                               class="mb-3 d-block text-reset root-menu-item {{ $menu->hide_name ? 'd-none' : '' }}"
                               target="{{ $menu->target ?? '_self' }}">
                                @if($menu->icon) <i class="{{ $menu->icon }} me-1" style="color: {{ $menu->icon_color }}"></i> @endif
                                {{ $menu->name }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif($style === 'horizontal')
             <ul class="list-inline mb-0">
                @foreach($menuItems as $menu)
                    <li class="list-inline-item me-3">
                         <a href="{{ $menu->link }}"
                            class="text-reset custom-menu-item child-menu-item">
                            {{ $menu->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            {{-- Vertical List (Default) --}}
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                @foreach($menuItems as $menu)
                    <li>
                        <a href="{{ $menu->link }}"
                           class="text-reset custom-menu-item root-menu-item pb-1">
                            {{ $menu->name }}
                        </a>
                        {{-- Level 2 --}}
                         @if($menu->children->isNotEmpty())
                            <ul class="list-unstyled ms-3 mt-1 d-flex flex-column gap-1">
                                @foreach($menu->children as $child)
                                    <li>
                                        <a href="{{ $child->link }}"
                                           class="text-reset custom-menu-item child-menu-item small">
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
