{{-- Description Section --}}
<div class="card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent px-4 py-3">
        <i class="bi bi-text-left me-2"></i>{{ translate('Description') }}
    </div>
    <div class="card-body p-4">
        <div class="product-description">
            {!! $product->description !!}
        </div>
    </div>
</div>

{{-- Category & Attributes Section --}}
<div class="card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent px-4 py-3">
        <i class="bi bi-list-ol me-2"></i>{{ translate('Category & Attributes') }}
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            {{-- Category --}}
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Category') }}</label>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $product->category->view_link }}" class="text-reset hover-primary" target="_blank">
                        <span class="fw-medium">{{ $product->category->name }}</span>
                    </a>
                </div>
            </div>

            {{-- Sub-category --}}
            @if ($product->subCategory)
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Sub-category') }}</label>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $product->subCategory->view_link }}" class="text-reset hover-primary" target="_blank">
                        <span class="fw-medium">{{ $product->subCategory->name }}</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Version --}}
            @if ($product->version)
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Version') }}</label>
                <div class="fw-medium">v{{ $product->version }}</div>
            </div>
            @endif

            {{-- Demo Link --}}
            @if ($product->demo_link)
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Demo Link') }}</label>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $product->view_demo }}" target="_blank" class="text-primary text-truncate"
                        style="max-width: 250px;">
                        {{ $product->demo_link }}
                    </a>
                    <i class="bi bi-box-arrow-up-right small text-muted ms-2"></i>
                </div>
            </div>
            @endif

            {{-- Custom Attributes --}}
            @if ($product->options && count($product->options) > 0)
            @foreach ($product->options as $key => $option)
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ $key }}</label>
                <div class="d-flex flex-wrap gap-1">
                    @if (is_array($option))
                    @foreach ($option as $subOption)
                    <span class="badge bg-light text-dark border">{{ $subOption }}</span>
                    @endforeach
                    @else
                    <span class="badge bg-light text-dark border">{{ $option }}</span>
                    @endif
                </div>
            </div>
            @endforeach
            @endif

            {{-- Tags --}}
            <div class="col-12">
                <label class="form-label text-muted small mb-1">{{ translate('Tags') }}</label>
                <div class="d-flex flex-wrap gap-1">
                    @foreach ($product->getTags() as $tag)
                    <span class="status-badge bg-primary-subtle text-primary">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@if (!$product->isDeleted())
{{-- Media Section --}}
@if ($product->isImagePreview() || $product->isVideoPreview() || $product->isAudioPreview() || ($product->gallery &&
count($product->gallery) > 0))
<div class="card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent px-4 py-3">
        <i class="bi bi-images me-2"></i>{{ translate('Media') }}
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            {{-- Preview Image --}}
            @if ($product->isImagePreview())
            <div class="col-12">
                <label class="form-label fw-medium d-block mb-2">{{ translate('Preview Image') }}</label>
                <img class="img-fluid rounded object-fit-contain" src="{{ $product->preview_image_url }}"
                    alt="{{ $product->name }}" style="max-height: 300px;">
            </div>
            @endif

            {{-- Preview Video --}}
            @if ($product->isVideoPreview())
            <div class="col-12">
                <label class="form-label fw-medium d-block mb-2">{{ translate('Preview Video') }}</label>
                <div class="product-single-video">
                    <video class="video-plyr w-100 rounded" poster="{{ $product->preview_image_url }}" controls
                        style="max-height: 300px;">
                        <source src="{{ $product->preview_video_url }}">
                    </video>
                </div>
            </div>
            @endif

            {{-- Preview Audio --}}
            @if ($product->isAudioPreview())
            <div class="col-12">
                <label class="form-label fw-medium d-block mb-2">{{ translate('Preview Audio') }}</label>
                <div class="product-single-audio">
                    <div class="product-audio-wave">
                        <div class="product-audio-actions md">
                            <button class="play-button btn btn-primary btn-sm px-2">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="pause-button btn btn-primary btn-sm px-2 d-none">
                                <i class="fas fa-pause"></i>
                            </button>
                        </div>
                        <div class="current-time small">00:00</div>
                        <div class="waveform" data-url="{{ $product->preview_audio_url }}" data-waveheight="60"></div>
                        <div class="total-duration small">00:00</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Gallery --}}
            @if ($product->gallery && count($product->gallery) > 0)
            @php $galleryImages = $product->gallery_links; @endphp
            <div class="col-12">
                <label class="form-label fw-medium d-block mb-2">{{ translate('Gallery') }}</label>
                <div id="carouselGallery" class="carousel slide rounded overflow-hidden" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($galleryImages as $image)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <img class="d-block w-100 object-fit-contain" src="{{ $image }}" style="max-height: 300px;">
                        </div>
                        @endforeach
                    </div>
                    @if (count($galleryImages) > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselGallery"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselGallery"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
@endif

{{-- Pricing & Settings Section --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent px-4 py-3">
        <i class="bi bi-gear me-2"></i>{{ translate('Pricing & Support') }}
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            {{-- License Prices --}}
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Regular Price') }}</label>
                <div class="fw-semibold text-success">{{ getAmount($product->price->regular) }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Extended Price') }}</label>
                <div class="fw-semibold text-success">
                    @if (!empty($product->extended_price))
                    {{ getAmount($product->price->extended) }}
                    @else
                    <span class="text-muted">{{ translate('N/A') }}</span>
                    @endif
                </div>
            </div>

            {{-- Free Product Status --}}
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Free Product') }}</label>
                <div>
                    @if ($product->isFree())
                    <span class="badge bg-success">{{ translate('Yes') }}</span>
                    @if ($product->isPurchasingEnabled())
                    <span class="badge bg-info ms-1">{{ translate('Purchasing Enabled') }}</span>
                    @endif
                    @else
                    <span class="badge bg-secondary">{{ translate('No') }}</span>
                    @endif
                </div>
            </div>

            {{-- Support Status --}}
            @if (@$settings->product->support_status)
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Support') }}</label>
                <div>
                    @if ($product->isSupported())
                    <span class="badge bg-success">{{ translate('Supported') }}</span>
                    @else
                    <span class="badge bg-secondary">{{ translate('Not Supported') }}</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Support Instructions --}}
        @if (@$settings->product->support_status && $product->isSupported() && $product->support_instructions)
        <hr class="my-3">
        <div>
            <label class="form-label text-muted small mb-2">{{ translate('Support Instructions') }}</label>
            <div class="bg-light rounded-4 p-3 small">
                {!! sanitizeHtml($product->support_instructions, true) !!}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Licensing Details Section --}}
@if ($product->regular_price_label || $product->extended_price_label || count($product->getRegularExtraFeatures()) > 0
|| count($product->getExtendedExtraFeatures()) > 0)
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent px-4 py-3">
        <i class="bi bi-award me-2"></i>{{ translate('Licensing Details') }}
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            {{-- Regular License Section --}}
            @if ($product->regular_price_label || count($product->getRegularExtraFeatures()) > 0)
            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-tag text-primary me-2"></i>{{ translate('Regular License') }}
                    </h6>

                    @if ($product->regular_price_label)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">{{ translate('Price Label') }}</label>
                        <div class="fw-medium">{{ $product->regular_price_label }}</div>
                    </div>
                    @endif

                    @if (count($product->getRegularExtraFeatures()) > 0)
                    <div>
                        <label class="form-label text-muted small mb-1">{{ translate('Extra Features') }}</label>
                        <ul class="list-unstyled mb-0">
                            @foreach ($product->getRegularExtraFeatures() as $feature)
                            <li class="d-flex align-items-start gap-2 mb-1">
                                <i class="bi bi-check-circle text-success small me-1"></i>{{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Extended License Section --}}
            @if ($product->extended_price_label || count($product->getExtendedExtraFeatures()) > 0)
            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-tags text-success me-2"></i>{{ translate('Extended License') }}
                    </h6>

                    @if ($product->extended_price_label)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">{{ translate('Price Label') }}</label>
                        <div class="fw-medium">{{ $product->extended_price_label }}</div>
                    </div>
                    @endif

                    @if (count($product->getExtendedExtraFeatures()) > 0)
                    <div>
                        <label class="form-label text-muted small mb-1">{{ translate('Extra Features') }}</label>
                        <ul class="list-unstyled mb-0">
                            @foreach ($product->getExtendedExtraFeatures() as $feature)
                            <li class="d-flex align-items-start gap-2 mb-1">
                                <i class="bi bi-check-circle text-success small me-1"></i>{{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Custom Services Section --}}
@if ($product->has_custom_services && $product->custom_services)
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent px-4 py-3">
        <i class="bi bi-tools me-2"></i>{{ translate('Custom Services') }}
    </div>
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-success">{{ translate('Available') }}</span>
            <span class="text-muted">{{ translate('This seller offers custom services for this product') }}</span>
        </div>
        <div class="bg-light rounded-4 p-3">
            {!! nl2br(e($product->custom_services)) !!}
        </div>
    </div>
</div>
@elseif ($product->has_custom_services)
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent px-4 py-3">
        <i class="bi bi-tools me-2"></i>{{ translate('Custom Services') }}
    </div>
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success">{{ translate('Available') }}</span>
            <span class="text-muted">{{ translate('Custom services are available for this product') }}</span>
        </div>
    </div>
</div>
@endif
