<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 pb-3 border-bottom-dashed">
                <div class="d-flex align-items-center text-gray-600 fs-14">
                    <i class="bi bi-patch-exclamation me-2"></i>
                    <span>{{ translate('Product ID') }}</span>
                </div>
                <span class="fw-bold fs-14">#{{ $product->id }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom-dashed">
                <div class="d-flex align-items-center text-gray-600 fs-14">
                    <i class="bi bi-box-seam me-2"></i>
                    <span>{{ translate('Name') }}</span>
                </div>
                <span class="text-end fw-semibold fs-14" title="{{ $product->name }}">{{ truncateText($product->name, 20) }}</span>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom-dashed">
                <div class="d-flex align-items-center text-gray-600 fs-14">
                    <i class="bi bi-tags me-2"></i>
                    <span>{{ translate('Category') }}</span>
                </div>
                <div class="text-end fs-13">
                    <a href="{{ route('categories.category', $product->category->slug) }}" target="_blank" class="text-primary hover-underline">{{ $product->category->name }}</a>
                    @if ($product->subCategory)
                        <span class="text-gray-600 mx-1">/</span>
                        <a href="{{ route('categories.sub-category', [$product->category->slug, $product->subCategory->slug]) }}" target="_blank" class="text-primary hover-underline">{{ $product->subCategory->name }}</a>
                    @endif
                </div>
            </li>

            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom-dashed">
                <div class="d-flex align-items-center text-gray-600 fs-14">
                    <i class="bi bi-info-circle me-2"></i>
                    <span>{{ translate('Status') }}</span>
                </div>
                <div>
                    <span class="badge {{ $product->status->badgeClass() }} px-3 py-2 rounded-pill">
                        <i class="bi {{ $product->status->icon() }} me-1 small"></i>
                        {{ $product->status->label() }}
                    </span>
                </div>
            </li>

            @if ($product->last_updated_at)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom-dashed">
                    <div class="d-flex align-items-center text-gray-600 fs-14">
                        <i class="bi bi-clock-history me-2"></i>
                        <span>{{ translate('Last Updated') }}</span>
                    </div>
                    <span class="fs-14 fw-medium">{{ dateFormat($product->last_updated_at) }}</span>
                </li>
            @endif

            <li class="list-group-item d-flex justify-content-between align-items-center px-0 pt-3 border-0">
                <div class="d-flex align-items-center text-gray-600 fs-14">
                    <i class="bi bi-calendar3 me-2"></i>
                    <span>{{ translate('Published') }}</span>
                </div>
                <span class="fs-14 fw-medium">{{ dateFormat($product->created_at) }}</span>
            </li>
        </ul>

        @if ($product->isApproved())
            <div class="mt-4 d-grid gap-2">
                <a href="{{ $product->view_link }}" target="_blank" class="btn btn-outline-secondary btn-modern btn-md rounded-3">
                    <i class="bi bi-eye me-2"></i>
                    {{ translate('View Product') }}
                </a>

                @if ($product->isMainFileExternal())
                    <a href="{{ $product->main_file['path'] ?? '' }}" target="_blank" class="btn btn-primary btn-modern btn-md rounded-3">
                        <i class="bi bi-download me-2"></i>
                        {{ translate('Download') }}
                    </a>
                @else
                    <form action="{{ route('user.product.download', $product->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-primary btn-md btn-modern w-100 rounded-3">
                            <i class="bi bi-download me-2"></i>
                            {{ translate('Download') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

