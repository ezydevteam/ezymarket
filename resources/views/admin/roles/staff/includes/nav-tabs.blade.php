<div class="ajax-tabs-wrapper position-relative">
    <button class="tabs-nav-control prev d-none" type="button"><i class="bi bi-chevron-left"></i></button>
    <div class="ajax-tabs-nav">
        @php $activeTab = $activeTab ?? request('tab', 'account'); @endphp

        <a href="{{ route('admin.roles.staff.edit', ['staff' => $staff->id, 'tab' => 'account']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'account' ? 'current' : '' }}">
            <i class="bi bi-person-gear"></i>
            <span>{{ translate('Account') }}</span>
        </a>

        @if ($staff->role === \App\Enums\Admin\AdminRole::REVIEWER)
            <a href="{{ route('admin.roles.staff.edit', ['staff' => $staff->id, 'tab' => 'privilege']) }}"
                data-ajax-tab="true"
                class="ajax-tabs-item {{ $activeTab === 'privilege' ? 'current' : '' }}">
                <i class="bi bi-shield-lock"></i>
                <span>{{ translate('Privileges') }}</span>
            </a>
        @endif

        <a href="{{ route('admin.roles.staff.edit', ['staff' => $staff->id, 'tab' => 'security']) }}"
            data-ajax-tab="true"
            class="ajax-tabs-item {{ $activeTab === 'security' ? 'current' : '' }}">
            <i class="bi bi-shield-check"></i>
            <span>{{ translate('Security') }}</span>
        </a>
    </div>
    <button class="tabs-nav-control next d-none" type="button"><i class="bi bi-chevron-right"></i></button>
</div>
