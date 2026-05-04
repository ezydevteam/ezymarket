@props([
    'product' => null,
    'showImage' => true,
    'showSeller' => false,
    'showCategory' => true,
    'showSubCategory' => false,
    'imageSize' => 'sm',
    'nameLimit' => 40,
    'linkRoute' => 'admin.products.show',
    'sellerLinkRoute' => 'admin.roles.users.edit',
    'categoryLinkRoute' => 'admin.products.categories.edit',
    'subCategoryLinkRoute' => 'admin.products.categories.sub-categories.index',
    'linkTarget' => '_self',
    'emptyText' => null,
    'fontWeight' => 'medium'
])

@if($product)
    <div class="product-box d-flex align-items-center gap-3">
        @if($showImage)
            <a href="{{ route($linkRoute, $product->id) }}"
                target="{{ $linkTarget }}"
                class="text-reset image-fluid image-{{ $imageSize }}">
                <img src="{{ $product->thumbnail_url ?? $product->preview_url }}"
                    alt="{{ $product->name }}">
            </a>
        @endif
        <div>
            <a href="{{ route($linkRoute, $product->id) }}"
                target="{{ $linkTarget }}"
                title="{{ $product->name }}"
                class="text-reset fw-{{ $fontWeight }} hover-primary">
                {{ truncateText($product->name, $nameLimit) }}
            </a>
            @isset($afterName)
                {{ $afterName }}
            @endisset
            @if ($showCategory || $showSeller)
            <div class="d-flex align-items-center small text-muted">
                @if($showCategory && $product->category)
                    <a href="{{ route($categoryLinkRoute, $product->category->id) }}"
                        class="text-reset hover-primary"
                        target="{{ $linkTarget }}"
                        title="{{ translate('Category') }}">
                        <i class="bi bi-tag me-1"></i>{{ $product->category->name }}
                    </a>
                    @if($showSubCategory && $product->subCategory)
                        <i class="bi bi-chevron-right fs-10 mx-1"></i><a
                            href="{{ route($subCategoryLinkRoute, ['subCategory' => $product->subCategory->id]) }}"
                            class="text-reset hover-primary"
                            target="{{ $linkTarget }}"
                            title="{{ translate('Sub Category') }}"
                            title="{{ translate('Sub Category') }}">
                            {{ $product->subCategory->name }}
                        </a>
                    @endif
                @endif
                @if($showSeller && $product->seller)
                    <a href="{{ route($sellerLinkRoute, $product->seller->id) }}"
                        class="text-reset hover-primary"
                        target="{{ $linkTarget }}">
                        <i class="bi bi-person me-1"></i>{{ $product->seller->full_name }}
                    </a>
                @endif
            </div>
            @endif
        </div>
    </div>
@else
    <span class="text-muted">{{ $emptyText ?? translate('Product Deleted') }}</span>
@endif
