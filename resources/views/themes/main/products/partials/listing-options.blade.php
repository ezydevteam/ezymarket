@php
    $pageSettings = themePageSettings();
    $showResults = $pageSettings->show_results ?? true;
    $showFilters = $pageSettings->show_filters ?? true;
    $showSorting = $pageSettings->show_sorting ?? true;
    $listingStyle = $pageSettings->listing_style ?? 'style-1';

    $viewStyle = $pageSettings->product_view_style ?? 'grid';
    $containerWidth = $pageSettings->container_width ?? 'default';
    $compactClass= ($viewStyle === 'list-compact' && $containerWidth !== 'boxed') ? 'col-lg-8 mx-auto' : '';

    $routeName = request()->route()?->getName() ?? 'products.index';
    $currentQuery = request()->except(['sort_by', 'page']);

    // Reset filters should only keep the search query, clearing all other filters and sorting
    $resetParams = request()->only(['query']);
    $resetFilter = url()->current() . (!empty($resetParams) ? '?' . http_build_query($resetParams) : '');

    $totalCount = $products->total();
    $count = sprintf('%02d', $totalCount) . ($totalCount > 99 ? '+' : '');

    // Determine sort route based on context
    $sortRoute = match ($routeName) {
        'categories.sub-category' => route('categories.sub-category', array_merge($currentQuery, [
            'category_slug' => $category->slug ?? '',
            'sub_category_slug' => $subCategory->slug ?? ''
        ])),
        'categories.category' => route('categories.category', array_merge($currentQuery, ['category_slug' => $category->slug ?? ''])),
        'products.search' => route('products.search', $currentQuery),
        default => route('products.index', $currentQuery),
    };
@endphp

@if ($showResults || $showFilters || $showSorting)
    <div class="product-listing-options mb-3 {{ $listingStyle }} {{ $compactClass }}">
        <div class="product-listing-options-left d-flex align-items-center gap-2">
            @if ($showResults)
                <div class="product-results-section">
                    <p class="text-dark mb-0">
                        {{ $totalCount == 0
                            ? translate('No product found.')
                            : trans_choice(':count product found.|:count products found.', $totalCount, [
                                'count' => $count
                            ])
                        }}
                    </p>
                </div>
            @endif

            @if ($showFilters)
                @if ($showResults)
                    <div class="d-none d-md-block text-muted opacity-50 mx-1">|</div>
                @endif
                <div class="product-filter-options d-flex align-items-center gap-2">
                    <button class="btn border-0 p-0" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#searchFilters" aria-controls="searchFilters">
                        <i class="bi bi-filter me-1"></i><span>{{ translate('Filter') }}</span>
                    </button>

                    @if ($hasFilters)
                        <div class="d-none d-md-block text-muted opacity-50 mx-1">|</div>
                        <a href="{{ $resetFilter }}" class="text-danger hover-underline" title="{{ translate('Reset filter') }}">
                            <i class="bi bi-x-circle me-1"></i>{{ translate('Reset') }}
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if ($showSorting)
            <div class="product-listing-options-right d-flex align-items-center gap-2">
                <div class="product-sort-options d-flex align-items-center">
                    <span class="d-none d-md-block" title="{{ translate('Sort by') }}"><i class="bi bi-sort-down"></i></span>
                    <select class="form-select custom-sort-select" id="listing_sort_by" data-width="auto">
                        @if ($routeName === 'products.search')
                            <option value="{{ $sortRoute . (strpos($sortRoute, '?') === false ? '?' : '&') . 'sort_by=relevance' }}"
                                {{ request('sort_by') == 'relevance' || !request('sort_by') ? 'selected' : '' }}>
                                {{ translate('Relevance') }}
                            </option>
                        @endif
                        <option value="{{ $sortRoute . (strpos($sortRoute, '?') === false ? '?' : '&') . 'sort_by=newest_arrivals' }}"
                            {{ request('sort_by') == 'newest_arrivals' ? 'selected' : '' }}>
                            {{ translate('Newest arrivals') }}
                        </option>
                        <option value="{{ $sortRoute . (strpos($sortRoute, '?') === false ? '?' : '&') . 'sort_by=best_selling' }}"
                            {{ request('sort_by') == 'best_selling' ? 'selected' : '' }}>
                            {{ translate('Best selling') }}
                        </option>
                        <option value="{{ $sortRoute . (strpos($sortRoute, '?') === false ? '?' : '&') . 'sort_by=price_high_to_low' }}"
                            {{ request('sort_by') == 'price_high_to_low' ? 'selected' : '' }}>
                            {{ translate('Price high to low') }}
                        </option>
                        <option value="{{ $sortRoute . (strpos($sortRoute, '?') === false ? '?' : '&') . 'sort_by=price_low_to_high' }}"
                            {{ request('sort_by') == 'price_low_to_high' ? 'selected' : '' }}>
                            {{ translate('Price low to high') }}
                        </option>
                        <option value="{{ $sortRoute . (strpos($sortRoute, '?') === false ? '?' : '&') . 'sort_by=recommended' }}"
                            {{ request('sort_by') == 'recommended' ? 'selected' : '' }}>
                            {{ translate('Recommended') }}
                        </option>
                    </select>
                </div>
            </div>
        @endif
    </div>
@endif
