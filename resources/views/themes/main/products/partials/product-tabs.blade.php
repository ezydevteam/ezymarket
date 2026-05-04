@php
$renderPart = $render_part ?? 'both';
$tabNavClass = $data->tab_nav_class ?? 'nav-tabs';
if ($renderPart === 'nav' && $data->display_layout === 'fullwidth_title') {
    $tabNavClass .= ($tabNavClass === 'nav-tabs') ? ' border-bottom-0' : ' mb-2';
} else {
    $tabNavClass .= ' mb-4';
}
@endphp

<div class="position-relative {{ $renderPart === 'nav' ? 'mb-0 z-2' : 'card border-0' }}">
    @if ($renderPart === 'both' || $renderPart === 'nav')
    @if (@$productSettings->reviews_status || @$productSettings->comments_status ||
    @$productSettings->changelogs_status || @$productSettings->support_status)
    <div
        class="card-header bg-transparent border-0 product-tab-area-wrapper {{ $data->tab_area_wrap_class ?? '' }}">
        <ul class="nav {{ $tabNavClass }}"
            role="tablist" id="product-tab-container-for-js"
            data-item-name="{{ $product->name }}" data-item-slug="{{ $product->slug }}"
            data-item-id="{{ $product->id }}" data-site-name="{{ @$settings->general->site_name }}">

            <li class="nav-item" role="presentation">
                <a href="{{ route('products.show', ['slug' => $product->slug, 'id' => $product->id]) }}"
                    data-url="{{ route('products.ajax_content', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'details']) }}"
                    class="nav-link {{ (isset($activeTab) && $activeTab === 'details') ? 'active' : '' }} ajax-tab-button"
                    data-tab="details" role="tab">
                    @unless($data->tab_hide_icon)<i class="bi bi-info-circle me-2"></i>@endunless{{
                    translate('Product Details')
                    }}
                </a>
            </li>

            @if ($settings->product->reviews_status && $product->hasReviews())
            <li class="nav-item" role="presentation">
                <a href="{{ route('products.show', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'reviews']) }}"
                    data-url="{{ route('products.ajax_content', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'reviews']) }}"
                    class="nav-link {{ (isset($activeTab) && $activeTab === 'reviews') ? 'active' : '' }} ajax-tab-button"
                    data-tab="reviews" role="tab">
                    @unless($data->tab_hide_icon)<i class="bi bi-star me-2"></i>@endunless{{
                    translate('Reviews') }}
                    @unless($data->tab_hide_counter)
                    @if ($product->reviews()->count() > 0)
                    <span class="badge bg-light text-dark ms-1">{{
                        $product->reviews()->count()
                        }}</span>
                    @endif
                    @endunless
                </a>
            </li>
            @endif

            @if (@$productSettings->comments_status)
            <li class="nav-item" role="presentation">
                <a href="{{ route('products.show', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'comments']) }}"
                    data-url="{{ route('products.ajax_content', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'comments']) }}"
                    class="nav-link {{ (isset($activeTab) && $activeTab === 'comments') ? 'active' : '' }} ajax-tab-button"
                    data-tab="comments" role="tab">
                    @unless($data->tab_hide_icon)<i class="bi bi-chat me-2"></i>@endunless{{
                    translate('Comments') }}
                    @unless($data->tab_hide_counter)
                    @if ($product->comments()->count() > 0)
                    <span class="badge bg-light text-dark ms-1">{{
                        $product->comments()->count()
                        }}</span>
                    @endif
                    @endunless
                </a>
            </li>
            @endif

            @if (@$productSettings->support_status && $product->isSupported())
            <li class="nav-item" role="presentation">
                <a href="{{ route('products.show', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'support']) }}"
                    data-url="{{ route('products.ajax_content', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'support']) }}"
                    class="nav-link {{ (isset($activeTab) && $activeTab === 'support') ? 'active' : '' }} ajax-tab-button"
                    data-tab="support" role="tab">
                    @unless($data->tab_hide_icon)<i class="bi bi-headset me-2"></i>@endunless{{
                    translate('Support') }}
                </a>
            </li>
            @endif

            @if ($settings->product->changelogs_status && $product->hasChangelogs())
            <li class="nav-item" role="presentation">
                <a href="{{ route('products.show', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'changelogs']) }}"
                    data-url="{{ route('products.ajax_content', ['slug' => $product->slug, 'id' => $product->id, 'tab' => 'changelogs']) }}"
                    class="nav-link {{ (isset($activeTab) && $activeTab === 'changelogs') ? 'active' : '' }} ajax-tab-button"
                    data-tab="changelogs" role="tab">
                    @unless($data->tab_hide_icon)<i class="bi bi-arrow-repeat me-2"></i>@endunless{{
                    translate('Changelogs')
                    }}
                </a>
            </li>
            @endif
        </ul>
    </div>
    @endif
    @endif

    @if ($renderPart === 'both' || $renderPart === 'content')
    <div class="card-body p-0">
        <div id="product-tab-content-area" class="tab-content-container">
            @if (isset($activeTab) && $activeTab === 'changelogs')
            @themeInclude('products.ajax-tabs.changelogs')
            @elseif (isset($activeTab) && $activeTab === 'reviews')
            @themeInclude('products.ajax-tabs.reviews')
            @elseif (isset($activeTab) && $activeTab === 'comments')
            @themeInclude('products.ajax-tabs.comments')
            @elseif (isset($activeTab) && $activeTab === 'support')
            @themeInclude('products.ajax-tabs.support')
            @else
            @themeInclude('products.ajax-tabs.details')
            @endif
        </div>
        <x-loader id="ajax-loader" centered="true" />
    </div>
    @endif
</div>
