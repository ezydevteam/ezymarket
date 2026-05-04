<div class="ajax-tabs-wrapper position-relative">
    <button class="tabs-nav-control prev d-none" type="button"><i class="bi bi-chevron-left"></i></button>
    <div class="ajax-tabs-nav">
        @php $activeTab = $activeTab ?? request('tab', 'details'); @endphp

        <a href="{{ route('admin.products.show', ['id' => $product->id, 'tab' => 'details']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'details' ? 'current' : '' }}">
            <i class="bi bi-info-circle"></i>
            <span>{{ translate('Details') }}</span>
        </a>

        <a href="{{ route('admin.products.show', ['id' => $product->id, 'tab' => 'actions']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'actions' ? 'current' : '' }}">
            <i class="bi bi-shield-check"></i>
            <span>{{ $product->isPendingReview() ? translate('Review') : translate('Action') }}</span>
        </a>

        <a href="{{ route('admin.products.show', ['id' => $product->id, 'tab' => 'history']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'history' ? 'current' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>{{ translate('History') }}</span>
        </a>

        @if (@$settings->product->discount_status)
        <a href="{{ route('admin.products.show', ['id' => $product->id, 'tab' => 'discount']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'discount' ? 'current' : '' }}">
            <i class="bi bi-tags"></i>
            <span>{{ translate('Discount') }}</span>
        </a>
        @endif

        <a href="{{ route('admin.products.show', ['id' => $product->id, 'tab' => 'reviews']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'reviews' ? 'current' : '' }}">
            <i class="bi bi-star"></i>
            <span>{{ translate('Reviews') }}</span>
        </a>

        <a href="{{ route('admin.products.show', ['id' => $product->id, 'tab' => 'comments']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'comments' ? 'current' : '' }}">
            <i class="bi bi-chat-dots"></i>
            <span>{{ translate('Comments') }}</span>
        </a>

        <a href="{{ route('admin.products.show', ['id' => $product->id, 'tab' => 'statistics']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'statistics' ? 'current' : '' }}">
            <i class="bi bi-graph-up"></i>
            <span>{{ translate('Statistics') }}</span>
        </a>
    </div>
    <button class="tabs-nav-control next d-none" type="button"><i class="bi bi-chevron-right"></i></button>
</div>
