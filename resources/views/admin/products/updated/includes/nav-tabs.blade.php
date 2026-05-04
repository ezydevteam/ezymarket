<div class="ajax-tabs-wrapper position-relative">
    <button class="tabs-nav-control prev d-none" type="button"><i class="bi bi-chevron-left"></i></button>
    <div class="ajax-tabs-nav">
        @php $activeTab = $activeTab ?? request('tab', 'details'); @endphp

        <a class="ajax-tabs-item {{ $activeTab === 'details' ? 'current' : '' }}"
            href="{{ route('admin.products.updated.show', ['productUpdate' => $productUpdate->id, 'tab' => 'details']) }}"
            data-ajax-tab="true">
            <i class="bi bi-info-circle"></i>
            <span>{{ translate('Details') }}</span>
        </a>

        <a class="ajax-tabs-item {{ $activeTab === 'actions' ? 'current' : '' }}"
            href="{{ route('admin.products.updated.show', ['productUpdate' => $productUpdate->id, 'tab' => 'actions']) }}"
            data-ajax-tab="true">
            <i class="bi bi-shield-check"></i>
            <span>{{ translate('Action') }}</span>
        </a>

        @if($productUpdate->product->hasChangelogs())
            <a class="ajax-tabs-item {{ $activeTab === 'changelogs' ? 'current' : '' }}"
                href="{{ route('admin.products.updated.show', ['productUpdate' => $productUpdate->id, 'tab' => 'changelogs']) }}"
                data-ajax-tab="true">
                <i class="bi bi-journal-text"></i>
                <span>{{ translate('Changelogs') }}</span>
            </a>
        @endif

        <a class="ajax-tabs-item {{ $activeTab === 'history' ? 'current' : '' }}"
            href="{{ route('admin.products.updated.show', ['productUpdate' => $productUpdate->id, 'tab' => 'history']) }}"
            data-ajax-tab="true">
            <i class="bi bi-clock-history"></i>
            <span>{{ translate('History') }}</span>
        </a>
    </div>
    <button class="tabs-nav-control next d-none" type="button"><i class="bi bi-chevron-right"></i></button>
</div>
