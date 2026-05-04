<div class="card">
    {{-- Product Thumbnail --}}
    <div class="card-img-top position-relative">
        <img src="{{ $product->preview_image_url ?? $product->thumbnail_url }}"
            alt="{{ $product->name }}"
            class="w-100 rounded-top-3 {{ $product->preview_image_url ? 'object-fit-cover' : 'object-fit-contain' }}"
            style="max-height: 200px;">
        @if($product->isFeatured() || $product->isPremium())
            <div class="d-flex align-items-center gap-2 position-absolute bottom-0 start-0 p-2">
                @if(isPremiumAvailable() && $product->isPremium())
                    <span class="badge bg-purple text-white px-2 py-1">
                        <i class="bi bi-gem me-1"></i>{{ translate('Premium') }}
                    </span>
                @endif
                @if($product->isFeatured())
                    <span class="badge bg-warning text-dark px-2 py-1">
                        <i class="bi bi-star-fill me-1"></i>{{ translate('Featured') }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="card-body p-3">
        {{-- Product Name --}}
        <h6 class="card-title fw-semibold mb-3 text-truncate" title="{{ $product->name }}">
            {{ $product->name }}
        </h6>

        {{-- Quick Stats --}}
        @if(!$product->isPending())
            <div class="row g-2 mb-3">
                <div class="col-6 d-flex flex-column align-items-start gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="card-icon bg-text-green"><i class="bi bi-cart-check"></i></div>
                        <div>
                            <h6 class="mb-0">{{ numberFormat($product->total_sales) }}</h6>
                            <span class="text-muted small">{{ translate('Sales') }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="card-icon bg-text-yellow"><i class="bi bi-star-fill"></i></div>
                        <div>
                            <h6 class="mb-0">{{ $product->avg_reviews ?? '0.0' }} <small class="text-muted">({{ numberFormat($product->total_reviews) }})</small></h6>
                            <span class="text-muted small">{{ translate('Reviews') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 d-flex flex-column align-items-start gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="card-icon bg-text-primary"><i class="bi bi-eye"></i></div>
                        <div>
                            <h6 class="mb-0">{{ numberFormat($product->total_views) }}</h6>
                            <span class="text-muted small">{{ translate('Views') }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="card-icon bg-text-blue"><i class="bi bi-currency-dollar"></i></div>
                        <div>
                            <h6 class="mb-0">{{ getAmount($product->total_earnings ?? 0) }}</h6>
                            <span class="text-muted small">{{ translate('Earnings') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Product Info List --}}
        <ul class="list-group list-group-flush mb-3">
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-hash me-2"></i>{{ translate('ID') }}
                </span>
                <span class="ms-auto">#{{ $product->id }}</span>
            </li>
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-folder-plus me-2"></i>{{ translate('Category') }}
                </span>
                <span class="ms-auto">
                    <a href="{{ $product->category->view_link }}" target="_blank" class="text-decoration-none small">
                        {{ $product->category->name }}
                    </a>
                    @if ($product->subCategory)
                        <i class="bi bi-chevron-right small text-muted mx-1"></i>
                        <a href="{{ $product->subCategory->view_link }}" target="_blank" class="text-decoration-none small">
                            {{ $product->subCategory->name }}
                        </a>
                    @endif
                </span>
            </li>
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-person me-2"></i>{{ translate('Seller') }}
                </span>
                <x-user :user="$product->seller"
                    class="ms-auto"
                    :showEmail="false"
                    :showAvatar="false"
                    fontWeight="normal" />
            </li>
            <li class="d-flex align-items-center py-2 border-bottom">
                <span><i class="bi bi-battery-charging me-2"></i>
                    {{ translate('Status') }}
                </span>
                <span class="badge {{ $product->status_badge }} ms-auto">
                    <i class="bi {{ $product->status_icon }} me-1"></i>{{ $product->status_name }}
                </span>
            </li>
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                     <i class="bi bi-tag me-2"></i>{{ translate('Regular Price') }}
                </span>
                <span class="ms-auto">
                    {{ ($product->isFree()) ? translate('Free') : getAmount($product->price->regular) }}
                </span>
            </li>
            @if(!$product->isFree() && $product->hasExtendedPrice())
            <li class="d-flex align-items-center py-2 border-bottom">
                <span>
                    <i class="bi bi-tag-fill me-2"></i>{{ translate('Extended Price') }}
                </span>
                <span class="ms-auto">{{ getAmount($product->price->extended) }}</span>
            </li>
            @endif
            @if(!$product->isPending() && !$product->isFree())
                <li class="d-flex align-items-center py-2 border-bottom">
                    <span><i class="bi bi-cash-stack me-2"></i>
                        {{ translate('Seller Earnings') }}
                    </span>
                    <span class="ms-auto">{{ getAmount($product->total_earnings ?? 0) }}</span>
                </li>
            @endif
            <li class="d-flex align-items-center py-2">
                <span><i class="bi bi-calendar-event me-2"></i>
                    {{ translate('Published') }}
                </span>
                <span class="ms-auto text-muted">{{ dateFormat($product->created_at) }}</span>
            </li>
            @if ($product->last_updated_at)
                <li class="d-flex align-items-center py-2 border-bottom">
                    <span><i class="bi bi-arrow-repeat me-2"></i>
                        {{ translate('Updated') }}
                    </span>
                    <span class="ms-auto text-muted">{{ dateFormat($product->last_updated_at) }}</span>
                </li>
            @endif
        </ul>

        {{-- Action Buttons --}}
        <div class="d-flex gap-2">
            {{-- View Button --}}
            @if($product->isApproved())
            <a href="{{ $product->view_link }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                <i class="bi bi-box-arrow-up-right me-2"></i>{{ translate('View on Site') }}
            </a>
            @endif

            {{-- Download Button --}}
            @if (!$product->isDeleted())
                @php
                    $downloadLink = $product->isMainFileExternal() ? $product->main_file['path'] ?? '' : route('admin.products.download', $product->id);
                    $linkTarget = $product->isMainFileExternal() ? '_blank' : '_self';
                @endphp
                <a class="btn btn-sm btn-success flex-fill" href="{{ $downloadLink }}" target="{{ $linkTarget }}">
                    <i class="bi bi-download me-2"></i>{{ translate('Download') }}
                </a>
            @endif
        </div>
    </div>
</div>
