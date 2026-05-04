@php
$bt = (object)($data->blockTitle ?? []);
$showTitle = $bt->show ?? false;
$style = $bt->style ?? 'default';
$title = $bt->title ?? '';
$subtitle = $bt->subtitle ?? '';
$showSubtitle = $bt->showSubtitle ?? false;
$showBorder = $bt->showBorder ?? false;
$fontSize = $bt->fontSize ?? 'fs-4';
$fontWeight = $bt->fontWeight ?? 'fw-medium';
$transform = $bt->transform ?? '';
$iconClass = $bt->iconClass ?? '';
$titleClasses = $bt->titleClasses ?? '';
$alignClass = $bt->alignClass ?? '';
$contentCenterClass = $bt->contentCenterClass ?? '';
$containerClass = $bt->containerClass ?? '';
$showViewMore = $bt->showViewMore ?? false;
$viewMoreStyle = $bt->viewMoreStyle ?? 'text';
$viewMoreIcon = $bt->viewMoreIcon ?? '';
$viewMoreClasses = $bt->viewMoreClasses ?? '';
$viewMoreText = $bt->viewMoreText ?? '';
$viewMoreUrl = $bt->viewMoreUrl ?: $viewDefaultLink ?? '#';
$blockAlignmentClass = $bt->blockAlignmentClass ?? '';
$showCategoryDropdown = $bt->showCategoryDropdown ?? false;
$categoryDropdownStyle = $bt->categoryDropdownStyle ?? 'text';
$categoryDropdownClasses = $bt->categoryDropdownClasses ?? 'text-dark fw-semibold';
$categoryDropdownIcon = $bt->categoryDropdownIcon ?? 'bi-chevron-down';
$categoryDropdownLabel = $bt->categoryDropdownLabel ?: translate('All Categories');
$categories = $data->categories ?? ($latestProductsCategories ?? []);

// Use active category from controller data
$activeCatSlug = $data->activeCategorySlug ?? null;
$categoryToggleLabel = $categoryDropdownLabel;
if ($activeCatSlug && !empty($categories)) {
foreach ($categories as $_cat) {
if ($_cat->slug === $activeCatSlug) {
$categoryToggleLabel = $_cat->name;
break;
}
}
}
@endphp

<div class="{{ $containerClass }}">
    <div class="d-flex align-items-center flex-wrap gap-2 {{ $alignClass }}">
        @if($showTitle)
        <div class="section-title-content {{ $contentCenterClass }}">
            @switch($style)
            @case('minimal')
            <h2 class="{{ $titleClasses }} {{ $fontWeight }}">
                @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
            </h2>
            @break

            @case('accent')
            <div class="d-inline-flex align-items-center gap-2">
                <span class="sta-accent-line"></span>
                <h2 class="{{ $titleClasses }} {{ $fontWeight }}">
                    @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
                </h2>
                <span class="sta-accent-line"></span>
            </div>
            @break

            @case('badge')
            <span class="sta-badge">
                <span class="{{ $fontSize }} {{ $transform }} {{ $fontWeight }}">
                    @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
                </span>
            </span>
            @break

            @case('gradient')
            <h2 class="{{ $titleClasses }} {{ $fontWeight }} sta-gradient-text">
                @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
            </h2>
            @break

            @case('underline')
            <h2 class="{{ $titleClasses }} {{ $fontWeight }}">
                @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
            </h2>
            <div class="sta-underline mt-1"></div>
            @break

            @case('parallelogram')
            <div class="sta-parallelogram">
                <span class="{{ $fontSize }} {{ $transform }} {{ $fontWeight }} text-white">
                    @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
                </span>
            </div>
            @break

            @case('square')
            <div class="sta-square">
                <span class="{{ $fontSize }} {{ $transform }} {{ $fontWeight }} text-white">
                    @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
                </span>
            </div>
            @break

            @default
            <h2 class="{{ $titleClasses }} {{ $fontWeight }}">
                @if($iconClass)<i class="{{ $iconClass }} me-2"></i>@endif{{ $title }}
            </h2>
            @endswitch

            @if($showSubtitle && !empty($subtitle))
            <p class="section-title-subtitle text-muted small mb-0 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        @endif

        @php
        $tabAlign = $data->tabNavAlignment ?? 'end';
        $tabAlignClass = match($tabAlign) {
        'start' => 'me-auto',
        'center' => 'mx-auto',
        default => 'ms-auto',
        };
        @endphp

        <div class="d-flex align-items-center gap-3 {{ $tabAlignClass }}">
            @if($showCategoryDropdown && !empty($categories) && count($categories) > 0)
            {{-- Category Dropdown --}}
            <div class="category-dropdown">
                <div class="custom-tabs mb-0" id="{{ $data->uniqueId }}-pills-tab" role="tablist">
                    <div class="custom-dropdown">
                        <div class="custom-dropdown-toggle cursor-pointer {{ $categoryDropdownClasses }}"
                            id="{{ $data->uniqueId }}-categoryToggle" role="button">
                            @if(!empty($categoryDropdownIcon) && $categoryDropdownIcon !== 'bi-chevron-down')
                            <i class="bi {{ $categoryDropdownIcon }} small ms-1"></i>
                            @endif
                            @if ($categoryDropdownStyle !== 'icon_only')
                            <span
                                class="selected-category {{ $categoryDropdownStyle === 'text' ? 'text-dark fw-semibold' : 'fw-medium' }}">{{
                                $categoryToggleLabel }}</span>
                            @endif
                            @if(!empty($categoryDropdownIcon) && $categoryDropdownIcon === 'bi-chevron-down')
                            <i class="bi {{ $categoryDropdownIcon }} small ms-1"></i>
                            @endif
                        </div>
                        <div class="custom-dropdown-menu" id="{{ $data->uniqueId }}-categoryMenu">
                            <button class="custom-dropdown-item custom-tabs-item {{ $activeCatSlug ? '' : 'active' }}"
                                id="{{ $data->uniqueId }}-pills-all-tab" data-bs-toggle="pill"
                                data-bs-target="#{{ $data->uniqueId }}-pills-all" type="button" role="tab"
                                aria-controls="{{ $data->uniqueId }}-pills-all"
                                aria-selected="{{ $activeCatSlug ? 'false' : 'true' }}"
                                data-category-name="{{ $categoryDropdownLabel }}">
                                {{ $categoryDropdownLabel }}
                            </button>
                            @foreach ($categories as $category)
                            <button
                                class="custom-dropdown-item custom-tabs-item {{ $activeCatSlug === $category->slug ? 'active' : '' }}"
                                id="{{ $data->uniqueId }}-pills-{{ $category->slug }}-tab" data-bs-toggle="pill"
                                data-bs-target="#{{ $data->uniqueId }}-pills-{{ $category->slug }}" type="button"
                                role="tab" aria-controls="{{ $data->uniqueId }}-pills-{{ $category->slug }}"
                                aria-selected="{{ $activeCatSlug === $category->slug ? 'true' : 'false' }}"
                                data-category-name="{{ $category->name }}">
                                {{ $category->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Product Tabs Navigation --}}
            @if(!empty($data->productTabsActiveTabs) && count($data->productTabsActiveTabs) > 0)
            @php
            $tabStyle = $data->tabNavStyle ?? 'pills';
            $tabId = $data->productTabsId ?? $data->uniqueId;

            $tabNavClass = match($tabStyle) {
            'underline' => 'product-tabs-nav product-tabs-underline',
            'bordered' => 'product-tabs-nav product-tabs-bordered',
            default => 'product-tabs-nav product-tabs-pills',
            };
            @endphp
            <ul class="nav {{ $tabNavClass }} mb-0" id="prod-tab-{{ $tabId }}" role="tablist">
                @foreach($data->productTabsActiveTabs as $key => $label)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="prod-{{ $key }}-{{ $tabId }}-tab"
                        data-bs-toggle="pill" data-bs-target="#prod-{{ $key }}-{{ $tabId }}" type="button" role="tab"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $label }}
                    </button>
                </li>
                @endforeach
            </ul>
            @endif

            @if($showViewMore)
            <a href="{{ $viewMoreUrl }}" class="{{ $viewMoreClasses }}" @if ($viewMoreStyle==='icon_only' )
                data-bs-toggle="tooltip" title="{{ $viewMoreText ?: translate('View More') }}"
                aria-label="{{ $viewMoreText ?: translate('View More') }}" @endif>
                @if(!empty($viewMoreText) && $viewMoreStyle !== 'icon_only'){{ $viewMoreText }}@endif
                @if(!empty($viewMoreIcon))
                <i class="bi {{ $viewMoreIcon }} small"></i>
                @endif
            </a>
            @endif
        </div>
    </div>

    @if($showBorder)
    <div class="sta-bottom-border mt-2"></div>
    @endif
</div>
