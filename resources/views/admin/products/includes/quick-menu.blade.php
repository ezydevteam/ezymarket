<x-dropdown icon="bi-three-dots-vertical" width="220px" maxHeight="350px"
    buttonClass="btn bg-secondary-subtle text-secondary user-nav-link">
    @if($product->isApproved() && $product->demo_link)
    <x-dropdown.item :href="$product->demo_link" target="_blank" icon="bi-box-arrow-up-right">
        {{ translate('Live Preview') }}
    </x-dropdown.item>
    @endif
    <x-dropdown.item :href="route('admin.roles.users.edit', $product->seller->id)" target="_blank" icon="bi-person">
        {{ translate('View Seller') }}
    </x-dropdown.item>
    @if (!$product->isDeleted())
    @php
    $downloadLink = $product->isMainFileExternal() ? $product->main_file['path'] ?? '' :
    route('admin.products.download', $product->id);
    $linkTarget = $product->isMainFileExternal() ? '_blank' : '_self';
    @endphp
    <x-dropdown.item :href="$downloadLink" :target="$linkTarget" icon="bi-download">
        {{ translate('Download') }}
    </x-dropdown.item>
    @endif
    @if ($product->isApproved())
    <x-dropdown.item type="divider" />
    @php
    $isFeatured = $product->isFeatured();
    $featuredLink = $isFeatured ? route('admin.products.featured.remove', $product->id) :
    route('admin.products.featured', $product->id);
    $featuredText = $isFeatured ? translate('Remove Featured') : translate('Make Featured');
    $featuredColor = $isFeatured ? 'danger' : 'success';
    $featuredIcon = $isFeatured ? 'bi-star-fill' : 'bi-star';
    $featuredConfirm = $isFeatured ? translate('Are you sure want to remove featured status?') : translate('Are you sure
    want to make this product featured?');
    @endphp
    <x-dropdown.item type="button" :icon="$featuredIcon" :color="$featuredColor" class="action-confirm"
        data-method="POST" data-action="{{ $featuredLink }}" data-confirm="{{ $featuredConfirm }}">
        {{ $featuredText }}
    </x-dropdown.item>
    @if (isPremiumAvailable() && !$product->isFree())
    @php
    $isPremium = $product->isPremium();
    $premiumLink = $isPremium ? route('admin.products.premium.remove', $product->id) : route('admin.products.premium',
    $product->id);
    $premiumText = $isPremium ? translate('Remove Premium') : translate('Make Premium');
    $premiumColor = $isPremium ? 'danger' : 'purple';
    $premiumConfirm = $isPremium ? translate('Are you sure want to remove premium status?') : translate('Are you sure
    want to make this product premium?');
    @endphp
    <x-dropdown.item type="button" icon="bi bi-gem" :color="$premiumColor" class="action-confirm" data-method="POST"
        data-action="{{ $premiumLink }}" data-confirm="{{ $premiumConfirm }}">
        {{ $premiumText }}
    </x-dropdown.item>
    @endif
    @endif

    <x-dropdown.item type="divider" />
    @if($product->hasSales())
    <x-dropdown.item :href="route('admin.records.sales.index', ['product' => $product->id])" target="_blank"
        icon="bi-receipt">
        {{ translate('View Sales') }}
    </x-dropdown.item>
    @endif
    <x-dropdown.item :href="route('admin.products.categories.edit', $product->category->id)" target="_blank"
        icon="bi-folder-plus">
        {{ translate('View Category') }}
    </x-dropdown.item>
    @if (!$product->isDeleted())
    <x-dropdown.item type="divider" />
    <x-dropdown.item type="button" icon="bi-trash" color="danger" class="action-confirm" data-method="DELETE"
        data-action="{{ route('admin.products.soft-delete', $product->id) }}"
        data-confirm="{{ translate('Are you sure want to delete this product?') }}">
        {{ translate('Delete Product') }}
    </x-dropdown.item>
    @endif
</x-dropdown>
