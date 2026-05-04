<div class="d-flex align-items-center gap-3">
    <a href="{{ !$product->isPending() ? route('user.product.edit', $product->id) : 'javascript:void(0)' }}"
        class="image-fluid image-lg flex-shrink-0">
        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}">
    </a>
    <div class="flex-shrink-1">
        <a href="{{ !$product->isPending() ? route('user.product.edit', $product->id) : 'javascript:void(0)' }}"
            class="text-dark d-block fw-semibold hover-primary" title="{{ $product->name }}">
            {{ truncateText($product->name, 65) }}
            @if ($product->isRestricted())
                <span class="small ms-1 text-danger" data-bs-toggle="tooltip"
                    data-bs-title="{{ translate('This product has been Restricted from public view') }}">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </span>
            @endif
        </a>

        <small class="bg-light border text-gray-600 fw-normal rounded-pill fs-11 mb-2 px-2 py-0"
            title="{{ translate('Product ID') }}">
            #{{ $product->id }}
        </small>

        <div aria-label="breadcrumb" class="export-ignore">
            <ol class="breadcrumb mb-0 fs-12 flex-nowrap overflow-hidden">
                <li class="breadcrumb-item text-truncate">
                    <a href="{{ $product->category->view_link }}" target="_blank" title="{{ translate('Category') }}"
                        class="text-gray-700 hover-underline">{{ $product->category->name }}</a>
                </li>
                @if ($product->subCategory)
                    <li class="breadcrumb-item text-truncate">
                        <a href="{{ $product->subCategory->view_link }}" target="_blank" title="{{ translate('Sub Category') }}"
                            class="text-gray-600 hover-underline">{{ $product->subCategory->name }}</a>
                    </li>
                @endif
            </ol>
        </div>
    </div>
</div>
