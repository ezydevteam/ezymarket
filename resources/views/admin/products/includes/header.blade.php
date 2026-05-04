<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-lg-8 border-lg-end">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="position-relative">
                            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                                class="image-fluid image-xl border border-3 border-white shadow-sm">
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $product->name }}</h4>
                            <p class="text-muted small mb-2">
                                <span class="me-2">#{{ $product->id }}</span>
                                <span class="mx-1 text-gray-300">|</span>
                                <i class="bi bi-folder2-open me-1"></i>{{ $product->category->name }}
                                @if ($product->subCategory)
                                <i class="bi bi-chevron-right small mx-1"></i>{{ $product->subCategory->name }}
                                @endif
                                <span class="mx-1 text-gray-300">|</span>
                                <i class="bi bi-person me-1"></i>{{ $product->seller->full_name }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="status-badge {{ $product->status_badge }}">
                                    {{ $product->status_name }}
                                </span>
                                @if($product->isBestSelling())
                                <span class="status-badge bg-orange-subtle text-orange border border-orange-subtle">
                                    {{ translate('Best Selling') }}
                                </span>
                                @endif
                                @if($product->isPremium())
                                <span class="status-badge bg-purple-subtle text-purple border border-purple-subtle">
                                    {{ translate('Premium') }}
                                </span>
                                @endif
                                @if($product->isFeatured())
                                <span class="status-badge bg-warning-subtle text-warning border border-warning-subtle">
                                    {{ translate('Featured') }}
                                </span>
                                @endif
                                @if($product->isFree())
                                <span class="status-badge bg-success-subtle text-success border border-success-subtle">
                                    {{ translate('Free') }}
                                </span>
                                @elseif ($product->isOnDiscount())
                                <span class="status-badge bg-success-subtle text-success border border-success-subtle">
                                    {{ translate('On Discount') }}
                                </span>
                                @endif
                                @if($product->isRestricted())
                                <span class="status-badge bg-danger-subtle text-danger border border-danger-subtle">
                                    {{ translate('Restricted') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <div class="row g-4 g-lg-5 justify-content-center justify-content-lg-start">
                            <div class="col-6 col-lg-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Regular Price') }}
                                </p>
                                <p class="mb-0 fw-medium">
                                    @if($product->isOnDiscount())
                                    <span class="text-decoration-line-through text-muted small me-1">{{
                                        getAmount($product->price->regular) }}</span>
                                    <span class="text-success">{{ getAmount($product->discount->price->regular)
                                        }}</span>
                                    @else
                                    {{ getAmount($product->price->regular) }}
                                    @endif
                                </p>
                            </div>
                            @if($product->hasExtendedPrice())
                            <div class="col-6 col-lg-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Extended Price')
                                    }}</p>
                                <p class="mb-0 fw-medium">
                                    @if($product->isOnDiscount() && $product->discount?->extended_price)
                                    <span class="text-decoration-line-through text-muted small me-1">{{
                                        getAmount($product->price->extended) }}</span>
                                    <span class="text-success">{{ getAmount($product->discount->price->extended)
                                        }}</span>
                                    @else
                                    {{ getAmount($product->price->extended) }}
                                    @endif
                                </p>
                            </div>
                            @endif
                            <div class="col-6 col-lg-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Published') }}</p>
                                <p class="mb-0 fw-medium">{{ dateFormat($product->created_at, 'M d, Y') }}</p>
                            </div>
                            <div class="col-6 col-lg-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Last Updated') }}
                                </p>
                                <p
                                    class="mb-0 fw-medium {{ $product->last_updated_at ? 'text-primary' : 'text-muted' }}">
                                    {{ $product->last_updated_at ? timeAgo($product->last_updated_at) :
                                    translate('Never') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex flex-column justify-content-between p-4 h-100">
                    @if($product->isApproved())
                        <div class="row g-4 mb-4 text-center">
                            <div class="col-3">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0 text-dark">{{ numberFormat($product->total_sales) }}</h3>
                                    <p class="text-muted small mb-0">{{ translate('Sales') }}</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($product->avg_reviews ?? 0, 1) }}
                                    </h3>
                                    <p class="text-muted small mb-0">{{ translate('Rating') }}</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0 text-dark">{{ numberFormat($product->total_views) }}</h3>
                                    <p class="text-muted small mb-0">{{ translate('Views') }}</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="stats-item">
                                    <h3 class="fw-bold mb-0 text-dark">{{ numberFormat($product->total_comments) }}</h3>
                                    <p class="text-muted small mb-0">{{ translate('Comments') }}</p>
                                </div>
                            </div>
                            <div class="col-12 mt-3 px-4">
                                <div class="row stats-item p-3 bg-white rounded-3 shadow-none border text-center">
                                    <div class="col-6 border-lg-end">
                                        <p class="text-muted small mb-1 fw-bold uppercase">{{ translate('Sales Amount') }}</p>
                                        <h3 class="fw-bold text-primary mb-0">{{ getAmount($product->total_sales_amount ?? 0) }}</h3>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted small mb-1 fw-bold uppercase">{{ translate('Net Revenue') }}</p>
                                        <h3 class="fw-bold text-success mb-0">{{ getAmount($product->total_earnings ?? 0) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle fs-2 mb-2 d-block"></i>
                            <p class="mb-0">{{ translate('Product is not approved yet, so no stats are available') }}</p>
                        </div>
                    @endif
                    <div class="d-flex align-items-center gap-2 mt-auto">
                        @if($product->isApproved())
                        <a href="{{ $product->view_link }}" target="_blank"
                            class="btn bg-primary-subtle text-primary border-primary-subtle w-100 fw-bold">
                            <i class="bi bi-eye me-2"></i>{{ translate('View Product') }}
                        </a>
                        @endif

                        @if(!$product->isDeleted())
                        @php
                        $downloadLink = $product->isMainFileExternal() ? $product->main_file['path'] ?? '' :
                        route('admin.products.download', $product->id);
                        $linkTarget = $product->isMainFileExternal() ? '_blank' : '_self';
                        @endphp
                        <a class="btn bg-success-subtle text-success border-success-subtle w-100 fw-bold"
                            href="{{ $downloadLink }}" target="{{ $linkTarget }}">
                            <i class="bi bi-download me-2"></i>{{ translate('Download') }}
                        </a>
                        @endif

                        @include('admin.products.includes.quick-menu')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
