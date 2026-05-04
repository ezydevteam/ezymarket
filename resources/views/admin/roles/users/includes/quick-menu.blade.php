<x-dropdown width="220px" maxHeight="400px" buttonClass="btn bg-secondary-subtle text-secondary user-nav-link">
    @if ($user->isDataCompleted())
        <x-dropdown.item :href="$user->profile_link" target="_blank" icon="bi-box-arrow-up-right">
            {{ translate('View Profile') }}
        </x-dropdown.item>
    @endif
    <x-dropdown.item :href="route('admin.roles.users.login', $user->id)" target="_blank" icon="bi-box-arrow-right">
        {{ translate('Login as User') }}
    </x-dropdown.item>

    <x-dropdown.item type="divider" />

    <x-dropdown.item :href="route('admin.id-verification.index', ['user' => $user->id])" target="_blank" icon="bi-person-check">
        {{ translate('ID Verifications') }}
    </x-dropdown.item>

    @if (isPremiumAvailable() && $user->isPremiumMember())
        <x-dropdown.item :href="route('admin.premium.members.index', ['member' => $user->id])" target="_blank" icon="bi-gem">
            {{ translate('View Membership') }}
        </x-dropdown.item>
    @endif

    @if ($user->isSeller())
        @if ($user->isFeaturedSeller())
            <x-dropdown.item
                type="button"
                icon="bi bi-award-fill"
                color="danger"
                class="action-confirm"
                data-method="POST"
                data-action="{{ route('admin.roles.users.featured.remove', $user->id) }}"
                data-confirm="{{ translate('Are you sure you want to remove featured status?') }}">
                {{ translate('Remove Featured') }}
            </x-dropdown.item>
        @else
            <x-dropdown.item
                type="button"
                icon="bi bi-award"
                color="success"
                class="action-confirm"
                data-method="POST"
                data-action="{{ route('admin.roles.users.featured', $user->id) }}"
                data-confirm="{{ translate('Are you sure you want to make this user featured?') }}">
                {{ translate('Make Featured') }}
            </x-dropdown.item>
        @endif
    @endif

    <x-dropdown.item type="divider" />

    <x-dropdown.item type="header" :text="translate('Support & Records')" />

    @if (@$settings->ticket->status)
        <x-dropdown.item :href="route('admin.tickets.index', ['user' => $user->id])" target="_blank" icon="bi-inbox">
            {{ translate('Tickets') }}
        </x-dropdown.item>
    @endif
    <x-dropdown.item :href="route('admin.records.purchases.index', ['user' => $user->id])" target="_blank" icon="bi-bag-check">
        {{ translate('Purchases') }}
    </x-dropdown.item>
    <x-dropdown.item :href="route('admin.financial.transactions.index', ['user' => $user->id])" target="_blank" icon="bi-receipt">
        {{ translate('Transactions') }}
    </x-dropdown.item>
    @if (@$settings->actions->refunds)
        <x-dropdown.item :href="route('admin.records.refunds.index', ['user' => $user->id])" target="_blank" icon="bi-arrow-clockwise">
            {{ translate('Requested Refunds') }}
        </x-dropdown.item>
    @endif
    <x-dropdown.item :href="route('admin.records.statements.index', ['user' => $user->id])" target="_blank" icon="bi-file-text">
        {{ translate('Statements') }}
    </x-dropdown.item>

    @if ($user->isSeller())
        <x-dropdown.item type="divider" />
        <x-dropdown.item type="header" :text="translate('Seller Hub')" />

        <x-dropdown.item :href="route('admin.products.index', ['seller' => $user->id])" target="_blank" icon="bi-box-seam">
            {{ translate('Products') }}
        </x-dropdown.item>
        <x-dropdown.item :href="route('admin.records.sales.index', ['seller' => $user->id])" target="_blank" icon="bi-cart">
            {{ translate('Sales') }}
        </x-dropdown.item>
        <x-dropdown.item :href="route('admin.records.support-earnings.index', ['seller' => $user->id])" target="_blank" icon="bi-piggy-bank">
            {{ translate('Support Earnings') }}
        </x-dropdown.item>
        <x-dropdown.item :href="route('admin.records.referral-earnings.index', ['seller' => $user->id])" target="_blank" icon="bi-graph-up-arrow">
            {{ translate('Referral Earnings') }}
        </x-dropdown.item>
        @if (@$settings->actions->refunds)
            <x-dropdown.item :href="route('admin.records.refunds.index', ['seller' => $user->id])" target="_blank" icon="bi-arrow-counterclockwise">
                {{ translate('Received Refunds') }}
            </x-dropdown.item>
        @endif
        <x-dropdown.item :href="route('admin.financial.payouts.index', ['user' => $user->id])" target="_blank" icon="bi-send">
            {{ translate('Payouts') }}
        </x-dropdown.item>
    @endif
</x-dropdown>
