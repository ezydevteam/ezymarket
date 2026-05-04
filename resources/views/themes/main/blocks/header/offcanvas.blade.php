@php
    $uniqueId = $data['uniqueId'];
    $iconClass = $data['iconClass'];
    $iconSize = $data['iconSize'];
    $triggerColor = $data['triggerColor'];
    $label = $data['label'];
    $labelPosition = $data['labelPosition'];
    $hideLabelInOffcanvas = $data['hideLabelInOffcanvas'];
    $elements = $data['elements'];
    $isActive = $data['isActive'];
    $getMenuCollection = $data['getMenuCollection'];
    $hasFooter = $data['hasFooter'];
    $triggerClass = $data['triggerClass'];
    $labelClass = $data['labelClass'];
@endphp

{{-- Trigger --}}
<a href="javascript:void(0)"
   role="button"
   id="{{ $uniqueId }}-trigger"
   class="{{ $triggerClass }}"
   data-bs-toggle="offcanvas"
   data-bs-target="#{{ $uniqueId }}"
   aria-controls="{{ $uniqueId }}"
   @if($labelPosition === 'tooltip') title="{{ $label }}" data-bs-placement="bottom" @endif>


    <i class="bi {{ $iconClass }} {{ $iconSize }}"></i>
    @if(in_array($labelPosition, ['inline', 'bottom']))
        <span class="{{ $labelClass }}">{{ $label }}</span>
    @endif
</a>

@push('footer_content')
{{-- Offcanvas --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="{{ $uniqueId }}" aria-labelledby="{{ $uniqueId }}Label">

    {{-- Header --}}
    <div class="offcanvas-header border-bottom">
        @if(!$hideLabelInOffcanvas)
            <h5 class="offcanvas-title fw-bold" id="{{ $uniqueId }}Label">{{ $label }}</h5>
        @else
            <div></div>
        @endif

        <div class="d-flex flex-wrap align-items-center justify-content-end gap-3 ms-auto">
            @if($isActive('search', 'header'))
                <button class="btn btn-sm btn-action p-1" type="button" data-bs-toggle="collapse" data-bs-target="#search-{{ $uniqueId }}">
                    <i class="bi bi-search"></i>
                </button>
            @endif

            @if($isActive('cart', 'header'))
                <a href="{{ route('cart.index') }}" class="position-relative btn-action p-1">
                     <i class="bi bi-cart"></i>
                     <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light p-1" style="font-size: 0.6rem;">
                        {{ $cartProductsCount ?? 0 }}
                     </span>
                </a>
            @endif

            @if($isActive('language', 'header'))
                <button class="btn btn-sm btn-action p-0 fw-bold" data-bs-toggle="modal" data-bs-target="#switchCurrencyLanguage">
                    <i class="bi bi-globe me-1"></i>
                    <span>{{ strtoupper(app()->getLocale()) }}</span>
                </button>
            @endif

            @if($isActive('theme', 'header'))
                <button class="btn btn-icon btn-sm btn-action" onclick="toggleTheme()">
                    <i class="bi bi-moon-stars"></i>
                </button>
            @endif

            @if($isActive('premium', 'header'))
                @php
                    $pStyle = $elements['premium']['style'] ?? 'btn-warning';
                    $pIcon = $elements['premium']['icon'] ?? 'bi-gem';
                    $isIconOnly = $pStyle === 'none';
                    $btnClass = $isIconOnly ? 'btn btn-sm btn-action' : 'btn btn-sm ' . $pStyle;
                @endphp
                 <a href="/premium/plans" class="{{ $btnClass }}">
                    <i class="bi {{ $pIcon }} {{ $isIconOnly ? '' : 'me-1' }}"></i>
                    @if(!$isIconOnly)
                        {{ $elements['premium']['label'] ?? translate('Premium') }}
                    @endif
                </a>
            @endif

            @if($isActive('social', 'header'))
                 <div class="d-flex gap-2">
                    @foreach(($settings->social_links ?? []) as $platform => $url)
                        @if($url) <a href="{{ formatExternalUrl($url) }}" class="social-link-{{ $platform }}" target="_blank"><i class="bi bi-{{ $platform }}"></i></a> @endif
                    @endforeach
                </div>
            @endif

            @if($isActive('html', 'header'))
                <div>{!! $elements['html']['content'] ?? '' !!}</div>
            @endif

            <button type="button" class="btn-close btn-action p-1" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
        </div>
    </div>

    {{-- Header Search Expansion --}}
    @if($isActive('search', 'header'))
        <div class="collapse border-bottom" id="search-{{ $uniqueId }}">
            <div class="p-3 bg-light bg-opacity-10">
                 <form action="{{ route('products.search') }}" method="GET" class="position-relative">
                    <input class="form-control" type="search" name="q" placeholder="{{ translate('Search here...') }}" aria-label="Search" autoFocus>
                    <button class="btn position-absolute top-50 end-0 translate-middle-y  bg-light border-0 pe-3" type="submit">
                        <i class="bi bi-arrow-right text-muted"></i>
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Body --}}
    <div class="offcanvas-body d-flex flex-column gap-4">

        @if($isActive('search', 'main'))
            <div>
                  <a class="d-flex justify-content-between align-items-center fw-bold mb-2 btn-action p-2 rounded"
                     data-bs-toggle="collapse" href="#search-main-{{ $uniqueId }}">
                     <span><i class="bi bi-search me-2"></i> {{ translate('Search') }}</span>
                     <i class="bi bi-chevron-down small"></i>
                  </a>
                  <div class="collapse show" id="search-main-{{ $uniqueId }}">
                        <form action="{{ route('products.search') }}" method="GET" class="position-relative mt-2">
                            <input class="form-control ps-5" type="search" name="q" placeholder="{{ translate('Search products...') }}">
                            <button class="btn position-absolute top-50 start-0 translate-middle-y border-0 ps-3" type="submit">
                                <i class="bi bi-search text-muted"></i>
                            </button>
                        </form>
                  </div>
            </div>
        @endif

        @if($isActive('cart', 'main'))
             <a href="{{ route('cart.index') }}" class="d-flex justify-content-between align-items-center fw-bold btn-action p-2 rounded">
                 <span><i class="bi bi-cart me-2"></i> {{ $elements['cart']['label'] ?: translate('Cart') }}</span>
                 <span class="badge rounded-pill bg-danger border border-light">{{ $cartProductsCount ?? 0 }}</span>
            </a>
        @endif

        @if($isActive('menu', 'main'))
            <ul class="nav flex-column gap-2">
                @foreach($getMenuCollection('main') as $menu)
                    <li class="nav-item">
                        @if($menu->children->count() > 0)
                            <a class="nav-link d-flex justify-content-between align-items-center fw-medium collapsed"
                               data-bs-toggle="collapse" href="#menu-{{ $uniqueId }}-{{ $loop->index }}">
                                <span>
                                    @if($menu->hasIcon()) <i class="bi {{ $menu->icon }} text-{{ $menu->icon_color }}"></i> @endif
                                    {{ $menu->name }}
                                    @if($menu->hasBadge()) <span class="badge bg-{{ $menu->badge_color?->value ?? 'primary' }} ms-1">{{ $menu->badge }}</span> @endif
                                </span>
                                <i class="bi bi-chevron-down small"></i>
                            </a>
                            <div class="collapse mt-2 ps-3 border-start" id="menu-{{ $uniqueId }}-{{ $loop->index }}">
                                <ul class="nav flex-column gap-1">
                                    @foreach($menu->children as $child)
                                        <li class="nav-item">
                                            @if($child->children->count() > 0)
                                                <a class="nav-link d-flex justify-content-between align-items-center py-1 opacity-75 collapsed"
                                                   data-bs-toggle="collapse" href="#menu-{{ $uniqueId }}-{{ $loop->parent->index }}-{{ $loop->index }}">
                                                    <span>
                                                        @if($child->hasIcon()) <i class="bi {{ $child->icon }} text-{{ $child->icon_color }}"></i> @endif
                                                        {{ $child->name }}
                                                        @if($child->hasBadge()) <span class="badge bg-{{ $child->badge_color?->value ?? 'primary' }} ms-1">{{ $child->badge }}</span> @endif
                                                    </span>
                                                    <i class="bi bi-chevron-down small"></i>
                                                </a>
                                                <div class="collapse mt-1 ps-4 border-start" id="menu-{{ $uniqueId }}-{{ $loop->parent->index }}-{{ $loop->index }}">
                                                    <ul class="nav flex-column gap-1">
                                                        @foreach($child->children as $subChild)
                                                            <li class="nav-item">
                                                                <a href="{{ $subChild->link }}" class="nav-link py-1 opacity-75 d-flex align-items-center text-start ps-3" {{ $subChild->isExternal() ? 'target=_blank' : '' }}>
                                                                    @if($subChild->hasIcon()) <i class="bi {{ $subChild->icon }} text-{{ $subChild->icon_color }}"></i> @endif
                                                                    {{ $subChild->name }}
                                                                    @if($subChild->hasBadge()) <span class="badge bg-{{ $subChild->badge_color?->value ?? 'primary' }} ms-1">{{ $subChild->badge }}</span> @endif
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @else
                                                <a href="{{ $child->link }}" class="nav-link py-1 opacity-75 d-flex align-items-center text-start" {{ $child->isExternal() ? 'target=_blank' : '' }}>
                                                    @if($child->hasIcon()) <i class="bi {{ $child->icon }} text-{{ $child->icon_color }}"></i> @endif
                                                    {{ $child->name }}
                                                    @if($child->hasBadge()) <span class="badge bg-{{ $child->badge_color?->value ?? 'primary' }} ms-1">{{ $child->badge }}</span> @endif
                                                </a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <a href="{{ $menu->link }}" class="nav-link fw-medium" {{ $menu->isExternal() ? 'target=_blank' : '' }}>
                                @if($menu->hasIcon()) <i class="bi {{ $menu->icon }} text-{{ $menu->icon_color }} me-2"></i> @endif
                                {{ $menu->name }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if($isActive('language', 'main'))
            <a href="#" class="d-flex align-items-center fw-medium btn-action p-2 rounded" data-bs-toggle="modal" data-bs-target="#switchCurrencyLanguage">
                <i class="bi bi-globe me-2"></i>
                {{ translate('Language & Currency') }}: <span class="fw-bold ms-1">{{ strtoupper(app()->getLocale()) }}</span>
            </a>
        @endif

        @if($isActive('theme', 'main'))
             <div class="d-flex justify-content-between align-items-center p-2">
                 <span class="fw-medium"><i class="bi bi-moon-stars me-2"></i> {{ translate('Dark Mode') }}</span>
                 <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" onchange="toggleTheme()" id="themeSwitchMain-{{ $uniqueId }}">
                </div>
            </div>
        @endif

        @if($isActive('premium', 'main'))
            @php
                $pStyle = $elements['premium']['style'] ?? 'btn-warning';
                $pIcon = $elements['premium']['icon'] ?? 'bi-gem';
                $isIconOnly = $pStyle === 'none';
                $btnClass = $isIconOnly ? 'btn btn-icon btn-action' : 'btn ' . $pStyle;
            @endphp
            <div class="{{ $isIconOnly ? 'd-flex justify-content-center' : 'd-grid' }}">
               <a href="/premium/plans" class="{{ $btnClass }}">
                    <i class="bi {{ $pIcon }} {{ $isIconOnly ? '' : 'me-1' }}"></i>
                    @if(!$isIconOnly)
                        {{ $elements['premium']['label'] ?? translate('Premium') }}
                    @endif
                </a>
            </div>
        @endif

        @if($isActive('social', 'main'))
            <div>
                <h6 class="text-uppercase extra-small fw-bold mb-3 opacity-75">{{ translate('Follow Us') }}</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(($settings->social_links ?? []) as $platform => $url)
                         @if($url)
                             <a href="{{ formatExternalUrl($url) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center social-link-{{ $platform }}" target="_blank" style="border-color: var(--oc-border);">
                                <i class="bi bi-{{ $platform }}"></i>
                                <span class="ms-1 text-capitalize">{{ $platform }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if($isActive('html', 'main'))
            <div>{!! $elements['html']['content'] ?? '' !!}</div>
        @endif
    </div>

    {{-- Footer --}}
    @if($hasFooter)
        <div class="offcanvas-footer border-top p-3 bg-light bg-opacity-10">
             <div class="d-flex flex-wrap align-items-center gap-3">

                @if($isActive('search', 'footer'))
                    <form action="{{ route('products.search') }}" method="GET" class="position-relative">
                        <input class="form-control ps-5" type="search" name="q" placeholder="{{ translate('Search products...') }}">
                        <button class="btn position-absolute top-50 start-0 translate-middle-y border-0 ps-3" type="submit">
                            <i class="bi bi-search text-muted"></i>
                        </button>
                    </form>
                @endif

                @if($isActive('cart', 'footer'))
                     <a href="{{ route('cart.index') }}" class="d-flex justify-content-between align-items-center fw-bold btn-action p-2 rounded">
                         <span><i class="bi bi-cart me-2"></i> {{ $elements['cart']['label'] ?: translate('Cart') }}</span>
                         <span class="badge rounded-pill bg-danger">{{ $cartProductsCount ?? 0 }}</span>
                    </a>
                @endif

                @if($isActive('menu', 'footer'))
                    <ul class="nav flex-column gap-1">
                        @foreach($getMenuCollection('footer') as $menu)
                            <li class="nav-item">
                                <a href="{{ $menu->link }}" class="nav-link py-1 ps-0 opacity-75" {{ $menu->isExternal() ? 'target=_blank' : '' }}>
                                    {{ $menu->name }}
                                    @if($menu->hasBadge()) <span class="badge bg-secondary ms-1">{{ $menu->badge }}</span> @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($isActive('language', 'footer'))
                     <a href="#" class="d-flex align-items-center fw-medium opacity-75 btn-action p-2 rounded" data-bs-toggle="modal" data-bs-target="#switchCurrencyLanguage">
                        <i class="bi bi-globe me-2"></i>
                        <span class="fw-bold ms-1">{{ strtoupper(app()->getLocale()) }}</span>
                    </a>
                @endif

                @if($isActive('theme', 'footer'))
                     <a href="#" onclick="toggleTheme()" class="d-flex align-items-center fw-medium opacity-75 btn-action p-2 rounded">
                        <i class="bi bi-moon-stars me-2"></i>
                        {{ translate('Toggle Theme') }}
                    </a>
                @endif

                @if($isActive('premium', 'footer'))
                    @php
                        $pStyle = $elements['premium']['style'] ?? 'btn-warning';
                        $pIcon = $elements['premium']['icon'] ?? 'bi-gem';
                        $isIconOnly = $pStyle === 'none';
                        $btnClass = $isIconOnly ? 'btn btn-sm btn-action' : 'btn btn-sm ' . $pStyle;
                    @endphp
                    <div class="{{ $isIconOnly ? 'd-flex justify-content-center' : 'd-grid' }}">
                       <a href="/premium/plans" class="{{ $btnClass }} p-1">
                        <i class="bi {{ $pIcon }} {{ $isIconOnly ? '' : 'me-1' }}"></i>
                        @if(!$isIconOnly)
                            {{ $elements['premium']['label'] ?? translate('Premium') }}
                        @endif
                        </a>
                    </div>
                @endif

                @if($isActive('social', 'footer'))
                    <div class="d-flex justify-content-center gap-3">
                        @foreach(($settings->social_links ?? []) as $platform => $url)
                            @if($url) <a href="{{ formatExternalUrl($url) }}" class="fs-5 social-link-{{ $platform }}" target="_blank"><i class="bi bi-{{ $platform }}"></i></a> @endif
                        @endforeach
                    </div>
                @endif
             </div>
            @if($isActive('html', 'footer'))
                <div class="text-start mt-3">
                    {!! $elements['html']['content'] ?? '' !!}
                </div>
            @endif
        </div>
    @endif
</div>
@endpush
