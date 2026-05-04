{{-- Product Details Card Widget --}}
@php
$showWidgetTitle = $widgetSettings['show_title'] ?? true;
$widgetTitle = $widgetTitle ?? ($widgetSettings['title'] ?? '');
$style = $widgetSettings['style'] ?? 'style-1';
$collapsedByDefault = $widgetSettings['collapsed_by_default'] ?? false;

// Determine if there is content to hide/show for each style
if ($style === 'style-1') {
$hasMoreContent = (($widgetSettings['show_version'] ?? true) && $product->version) ||
(($widgetSettings['show_category'] ?? true) && $product->category) ||
(($widgetSettings['show_options'] ?? true) && $product->options && count((array)$product->options) > 0) ||
(($widgetSettings['show_tags'] ?? true) && count((array)$product->getTags()) > 0);
} else {
$hasMoreContent = (($widgetSettings['show_options'] ?? true) && $product->options && count((array)$product->options) >
0) ||
(($widgetSettings['show_tags'] ?? true) && count((array)$product->getTags()) > 0);
}

$cardStyle = $instance->getSetting('widget_card_style') ?? 'card-border';
$titleStyle = $widgetSettings['title_style'] ?? 'default';
$titlePadding = in_array($titleStyle, ['style_1', 'style_2']) ? '' : ($cardStyle === 'none' ? '' : 'p-3 pb-0' );
@endphp

<div class="widget-product-details-card {{ $style }} {{ $titlePadding }}" id="productDetailsCard-{{ $product->id }}">
    @include('widgets.partials.widget-title', ['title' => $widgetTitle ?? '', 'widgetSettings' => $widgetSettings ??
    []])

    <div class="card-body {{ $cardStyle === 'none' ? 'p-0' : 'p-3' }}">
        @if ($style === 'style-1')
        {{-- Style 1: Classic with icons --}}
        <div class="widget-attribute">
            @if (($widgetSettings['show_last_updated'] ?? true) && $product->last_updated_at)
            <div class="attribute-item mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-icon-wrapper rounded-circle">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div>
                        <small class="text-uppercase mb-1">{{ translate('Last Updated') }}</small>
                        <div class="fw-medium">{{ dateFormat($product->last_updated_at) }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if ($widgetSettings['show_published_date'] ?? true)
            <div class="attribute-item mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-icon-wrapper rounded-circle">
                        <i class="bi bi-calendar3-event"></i>
                    </div>
                    <div>
                        <small class="text-uppercase mb-1">{{ translate('Published') }}</small>
                        <div class="fw-medium">{{ dateFormat($product->created_at) }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="widget-attribute-more {{ $collapsedByDefault ? '' : 'd-none' }}">
            @if (($widgetSettings['show_version'] ?? true) && $product->version)
            <div class="attribute-item mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-icon-wrapper rounded-circle">
                        <i class="bi bi-node-plus fs-5"></i>
                    </div>
                    <div>
                        <small class="text-uppercase mb-1">{{ translate('Version') }}</small>
                        <div class="fw-medium">
                            @if (@$settings->product->changelogs_status && $product->hasChangelogs())
                            <a href="{{ $product->getChangelogsLink() }}">
                                {{ translate('v:version', ['version' => $product->version]) }}
                            </a>
                            @else
                            {{ translate('v:version', ['version' => $product->version]) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if ($widgetSettings['show_category'] ?? true)
            <div class="attribute-item mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-icon-wrapper rounded-circle">
                        <i class="bi bi-folder"></i>
                    </div>
                    <div>
                        <small class="text-uppercase mb-1">{{ translate('Category') }}</small>
                        <div class="fw-medium">
                            <a href="{{ $product->category->view_link }}">{{ $product->category->name }}</a>
                            @if ($product->subCategory)
                            <span class="text-muted mx-1">›</span>
                            <a href="{{ $product->subCategory->view_link }}">{{ $product->subCategory->name }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if (($widgetSettings['show_options'] ?? true) && $product->options && count((array)$product->options) > 0)
            @foreach ($product->options as $key => $value)
            <div class="attribute-item mb-3">
                <div class="d-flex align-items-center gap-2">
                    @php
                    $icons = ['bi-file-diff', 'bi-clipboard', 'bi-collection', 'bi-gem', 'bi-droplet',
                    'bi-diamond-half', 'bi-shield-check'];
                    $randomIcon = $icons[array_rand($icons)];
                    @endphp
                    <div class="dash-icon-wrapper rounded-circle">
                        <i class="bi {{ $randomIcon }}"></i>
                    </div>
                    <div>
                        <small class="text-uppercase mb-1">{{ $key }}</small>
                        <div class="d-flex flex-wrap gap-2">
                            @if (is_array($value))
                            @foreach ($value as $option)
                            <a href="{{ route('products.index', ['search' => strtolower($option)]) }}" class="tag-pill">
                                {{ $option }}
                            </a>
                            @endforeach
                            @else
                            <span class="text-dark">{{ $value }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif

            @if ($widgetSettings['show_tags'] ?? true)
            <div class="attribute-item">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-icon-wrapper rounded-circle">
                        <i class="bi bi-tag"></i>
                    </div>
                    <div>
                        <small class="text-uppercase mb-1">{{ translate('Tags') }}</small>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($product->getTags() as $tag)
                            <a href="{{ route('products.index', ['search' => strtolower($tag)]) }}" class="tag-pill">
                                {{ $tag }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if ($hasMoreContent)
        <!-- Show More Button -->
        <div class="text-center mt-3">
            <button class="btn widget-attribute-btn fs-14 text-gray-700" type="button"
                data-text-more="{{ translate('Show more') }}" data-text-less="{{ translate('Show less') }}">
                <span class="widget-attribute-btn-text">{{ $collapsedByDefault ? translate('Show less') :
                    translate('Show more') }}</span>
                <i class="bi {{ $collapsedByDefault ? 'bi-chevron-up' : 'bi-chevron-down' }} fs-12 ms-1"></i>
            </button>
        </div>
        @endif

        @elseif ($style === 'style-2')
        {{-- Style 2: Modern Dashboard Grid --}}
        <div class="dash-grid mb-3">
            @if (($widgetSettings['show_last_updated'] ?? true) && $product->last_updated_at)
            <div class="dash-card">
                <div class="dash-icon-wrapper">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="dash-label">{{ translate('Updated') }}</div>
                <div class="dash-value">{{ dateFormat($product->last_updated_at) }}</div>
            </div>
            @endif

            @if ($widgetSettings['show_published_date'] ?? true)
            <div class="dash-card">
                <div class="dash-icon-wrapper">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="dash-label">{{ translate('Published') }}</div>
                <div class="dash-value">{{ dateFormat($product->created_at) }}</div>
            </div>
            @endif

            @if (($widgetSettings['show_version'] ?? true) && $product->version)
            <div class="dash-card">
                <div class="dash-icon-wrapper">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <div class="dash-label">{{ translate('Version') }}</div>
                <div class="dash-value">
                    @if (@$settings->product->changelogs_status && $product->hasChangelogs())
                    <a href="{{ $product->getChangelogsLink() }}" class="text-decoration-none text-dark">
                        {{ translate('v:version', ['version' => $product->version]) }}
                    </a>
                    @else
                    {{ translate('v:version', ['version' => $product->version]) }}
                    @endif
                </div>
            </div>
            @endif

            @if ($widgetSettings['show_category'] ?? true)
            <div class="dash-card">
                <div class="dash-icon-wrapper">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <div class="dash-label">{{ translate('Category') }}</div>
                <div class="dash-value">
                    <a href="{{ $product->category->view_link }}" class="text-dark hover-primary-underline">
                        {{ $product->category->name }}
                    </a>
                    @if ($product->subCategory)
                    <span class="text-muted mx-1">›</span>
                    <a href="{{ $product->subCategory->view_link }}" class="text-dark hover-primary-underline">
                        {{ $product->subCategory->name }}
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="widget-attribute-more {{ $collapsedByDefault ? '' : 'd-none' }}">
            <div class="dash-grid px-1 mt-2">
                @if (($widgetSettings['show_options'] ?? true) && $product->options && count((array)$product->options) >
                0)
                @foreach ($product->options as $key => $value)
                @if (is_array($value))
                <div class="dash-card full-width">
                    <div class="dash-label">{{ $key }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @foreach ($value as $option)
                        <a href="{{ route('products.index', ['search' => strtolower($option)]) }}"
                            class="soft-pill-dash">
                            {{ $option }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="dash-card">
                    <div class="dash-label">{{ $key }}</div>
                    <div class="dash-value">{{ $value }}</div>
                </div>
                @endif
                @endforeach
                @endif

                @if ($widgetSettings['show_tags'] ?? true)
                <div class="dash-card full-width">
                    <div class="dash-label">{{ translate('Tags') }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @foreach ($product->getTags() as $tag)
                        <a href="{{ route('products.index', ['search' => strtolower($tag)]) }}" class="soft-pill-dash">
                            #{{ $tag }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if ($hasMoreContent)
        <!-- Show More Button -->
        <div class="text-center mt-3">
            <button class="btn widget-attribute-btn fs-14 text-gray-700" type="button"
                data-text-more="{{ translate('Show more') }}" data-text-less="{{ translate('Show less') }}">
                <span class="widget-attribute-btn-text">
                    {{ $collapsedByDefault ? translate('Show less') : translate('Show more') }}
                </span>
                <i class="bi {{ $collapsedByDefault ? 'bi-chevron-up' : 'bi-chevron-down' }} fs-12 ms-1"></i>
            </button>
        </div>
        @endif

        @elseif ($style === 'style-3')
        {{-- Style 3: Minimal list layout --}}
        <ul class="list-unstyled mb-0 fs-14">
            @if (($widgetSettings['show_last_updated'] ?? true) && $product->last_updated_at)
            <li class="d-flex justify-content-between pb-2 border-bottom">
                <span class="fw-medium">{{ translate('Last Updated') }}</span>
                <span class="text-gray-700">{{ dateFormat($product->last_updated_at) }}</span>
            </li>
            @endif
            @if ($widgetSettings['show_published_date'] ?? true)
            <li class="d-flex justify-content-between py-2 border-bottom">
                <span class="fw-medium">{{ translate('Published') }}</span>
                <span class="text-gray-700">{{ dateFormat($product->created_at) }}</span>
            </li>
            @endif
            @if (($widgetSettings['show_version'] ?? true) && $product->version)
            <li class="d-flex justify-content-between py-2 border-bottom">
                <span class="fw-medium">{{ translate('Version') }}</span>
                <span>
                    @if (@$settings->product->changelogs_status && $product->hasChangelogs())
                    <a href="{{ $product->getChangelogsLink() }}" class="hover-underline">
                        {{ translate('v:version', ['version' => $product->version]) }}
                    </a>
                    @else
                    {{ translate('v:version', ['version' => $product->version]) }}
                    @endif
                </span>
            </li>
            @endif
            @if ($widgetSettings['show_category'] ?? true)
            <li class="d-flex justify-content-between py-2 border-bottom">
                <span class="fw-medium">{{ translate('Category') }}</span>
                <span>
                    <a href="{{ $product->category->view_link }}" class="hover-underline">
                        {{ $product->category->name }}
                    </a>
                    @if ($product->subCategory)
                    <span class="text-muted mx-1">›</span>
                    <a href="{{ $product->subCategory->view_link }}" class="hover-underline">
                        {{ $product->subCategory->name }}
                    </a>
                    @endif
                </span>
            </li>
            @endif
        </ul>

        <div class="widget-attribute-more {{ $collapsedByDefault ? '' : 'd-none' }}">
            <ul class="list-unstyled mb-0 fs-14">
                @if (($widgetSettings['show_options'] ?? true) && $product->options && count((array)$product->options) >
                0)
                @foreach ($product->options as $key => $value)
                <li class="d-flex justify-content-between py-2 border-bottom">
                    <span class="fw-medium">{{ $key }}</span>
                    <span>
                        @if (is_array($value))
                        {{ implode(', ', $value) }}
                        @else
                        {{ $value }}
                        @endif
                    </span>
                </li>
                @endforeach
                @endif
            </ul>

            @if ($widgetSettings['show_tags'] ?? true)
            <div class="pt-3">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($product->getTags() as $tag)
                    <a href="{{ route('products.index', ['search' => strtolower($tag)]) }}" class="tag-pill small">
                        #{{ $tag }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if ($hasMoreContent)
        <!-- Show More Button -->
        <div class="text-center mt-3">
            <button class="btn widget-attribute-btn fs-14 text-gray-700" type="button"
                data-text-more="{{ translate('Show more') }}" data-text-less="{{ translate('Show less') }}">
                <span class="widget-attribute-btn-text">
                    {{ $collapsedByDefault ? translate('Show less') : translate('Show more') }}
                </span>
                <i class="bi {{ $collapsedByDefault ? 'bi-chevron-up' : 'bi-chevron-down' }} fs-12 ms-1"></i>
            </button>
        </div>
        @endif
        @endif
    </div>
</div>
