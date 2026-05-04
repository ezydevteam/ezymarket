<div class="ajax-tabs d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div class="ajax-tabs-nav">
        <a href="{{ route('user.product.edit', $product->id) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.product.edit') ? 'current' : '' }}">
            <i class="bi bi-pencil-square"></i>
            <span>{{ translate('Edit Details') }}</span>
        </a>
        @if (@$settings->product->changelogs_status)
            <a href="{{ route('user.product.changelogs.index', $product->id) }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ request()->routeIs('user.product.changelogs.index') ? 'current' : '' }}">
                <i class="bi bi-arrow-repeat"></i>
                <span>{{ translate('Changelogs') }}</span>
            </a>
        @endif
        @if (@$settings->product->discount_status)
            <a href="{{ route('user.product.discount', $product->id) }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ request()->routeIs('user.product.discount') ? 'current' : '' }}">
                <i class="bi bi-tags"></i>
                <span>{{ translate('Discount') }}</span>
            </a>
        @endif
        <a href="{{ route('user.product.statistics', $product->id) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.product.statistics') ? 'current' : '' }}">
            <i class="bi bi-bar-chart"></i>
            <span>{{ translate('Statistics') }}</span>
        </a>
        <a href="{{ route('user.product.history', $product->id) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ request()->routeIs('user.product.history') ? 'current' : '' }}">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>{{ translate('History') }}</span>
        </a>
    </div>
    <div>
        <a href="{{ route('user.product.index') }}"
           class="btn btn-soft rounded-pill">
            <i class="bi bi-arrow-left me-2"></i>{{ translate('Back') }}
        </a>
    </div>
</div>
