@php
    $updatedProperties = $productUpdate->getUpdatedProperties();
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-lg-8 border-lg-end">
                <div class="p-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="position-relative">
                            <img src="{{ $productUpdate->thumbnail_url }}" alt="{{ $productUpdate->name }}"
                                class="image-fluid image-xl border border-3 border-white shadow-sm">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h4 class="fw-bold mb-0 text-gray-900">{{ $productUpdate->name }}</h4>
                                <span class="status-badge bg-orange-subtle text-orange border border-orange-subtle">
                                    {{ translate('Pending Update Review') }}
                                </span>
                            </div>
                            <p class="text-muted small mb-2">
                                <span class="me-2">#{{ $productUpdate->id }}</span>
                                <span class="mx-1 text-gray-300">|</span>
                                <i class="bi bi-folder2-open me-1"></i>{{ $productUpdate->category->name }}
                                @if ($productUpdate->subCategory)
                                    <i class="bi bi-chevron-right small mx-1"></i>{{ $productUpdate->subCategory->name }}
                                @endif
                                <span class="mx-1 text-gray-300">|</span>
                                <i class="bi bi-person me-1"></i>{{ $productUpdate->seller->full_name }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="status-badge border text-gray-800 fw-medium">
                                    <i class="bi bi-pencil-square me-1"></i>
                                    {{ count($updatedProperties) }} {{ translate('Total Changes') }}
                                </span>
                                @if (array_key_exists('regular_price', $updatedProperties) || array_key_exists('extended_price', $updatedProperties))
                                    <span class="status-badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-currency-dollar me-1"></i>
                                        {{ translate('Price Change Detected') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <div class="row g-4 g-lg-5">
                            <div class="col-6 col-lg-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Regular Price') }}</p>
                                <div class="d-flex align-items-center gap-2">
                                    @if(array_key_exists('regular_price', $updatedProperties))
                                        <span class="text-decoration-line-through text-muted small">{{ getAmount($updatedProperties['regular_price']['old']) }}</span>
                                        <i class="bi bi-arrow-right text-muted small"></i>
                                        <span class="text-success fw-bold">{{ getAmount($updatedProperties['regular_price']['new']) }}</span>
                                    @else
                                        <span class="text-gray-800 fw-medium">{{ getAmount($productUpdate->product->regular_price) }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($productUpdate->product->extended_price || array_key_exists('extended_price', $updatedProperties))
                                <div class="col-6 col-lg-auto">
                                    <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Extended Price') }}</p>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(array_key_exists('extended_price', $updatedProperties))
                                            <span class="text-decoration-line-through text-muted small">{{ getAmount($updatedProperties['extended_price']['old']) }}</span>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <span class="text-success fw-bold">{{ getAmount($updatedProperties['extended_price']['new']) }}</span>
                                        @else
                                            <span class="text-gray-800 fw-medium">{{ getAmount($productUpdate->product->extended_price) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="col-6 col-lg-auto">
                                <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Submitted At') }}</p>
                                <p class="mb-0 fw-medium text-gray-800">{{ dateFormat($productUpdate->created_at) }}</p>
                            </div>
                            @if($productUpdate->last_updated_at)
                                <div class="col-6 col-lg-auto">
                                    <p class="text-muted small mb-1 uppercase fw-bold ls-1">{{ translate('Last Revised') }}</p>
                                    <p class="mb-0 fw-medium text-gray-800">{{ timeAgo($productUpdate->last_updated_at) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex flex-column justify-content-between p-4 h-100 bg-light-subtle rounded-end-4">
                    <div class="text-center py-2 flex-grow-1 d-flex flex-column justify-content-center">
                        <div class="mb-2">
                            <i class="bi bi-shield-check fs-1 text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-1">{{ translate('Finalize Review') }}</h5>
                        <p class="text-muted small px-3">
                            {{ translate('Carefully review all changes submitted by the seller before approving or rejecting this update.') }}
                        </p>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-auto">
                        @if ($productUpdate->main_file)
                            <a class="btn bg-success-subtle text-success flex-fill py-2"
                                href="{{ $productUpdate->isMainFileExternal() ? ($productUpdate->main_file['path'] ?? '') : route('admin.products.updated.download', $productUpdate->id) }}"
                                target="{{ $productUpdate->isMainFileExternal() ? '_blank' : '_self' }}">
                                <i class="bi bi-download me-2"></i>{{ translate('Download File') }}
                            </a>
                        @endif

                        <a href="{{ route('admin.products.show', $productUpdate->product->id) }}" target="_blank"
                            class="btn bg-primary-subtle text-primary flex-fill py-2">
                            <i class="bi bi-box-arrow-up-right me-2"></i>{{ translate('Original Product') }}
                        </a>

                        <div class="dropdown">
                            <button class="btn bg-secondary-subtle text-secondary py-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item text-danger action-confirm"
                                        href="{{ route('admin.products.updated.destroy', $productUpdate->id) }}"
                                        data-text="{{ translate('Are you sure you want to delete this update request?') }}"
                                        data-method="DELETE">
                                        <i class="bi bi-trash me-2"></i>{{ translate('Delete Request') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
