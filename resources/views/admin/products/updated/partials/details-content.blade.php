{{-- Description Section --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent py-3">
        <i class="bi bi-text-left me-2"></i>{{ translate('Description') }}
    </div>
    <div class="card-body p-4">
        <div class="product-description">
            {!! sanitizeRichText($productUpdate->description) !!}
        </div>
    </div>
</div>

{{-- Category & Attributes Section --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent py-3">
        <i class="bi bi-list-ol me-2"></i>{{ translate('Category & Attributes') }}
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            {{-- Category --}}
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Category') }}</label>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $productUpdate->product->category->view_link }}"
                       class="text-reset hover-primary" target="_blank">
                        <span class="fw-medium">{{ $productUpdate->product->category->name }}</span>
                    </a>
                </div>
            </div>

            {{-- Sub-category --}}
            @if ($productUpdate->product->subCategory)
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">{{ translate('Sub-category') }}</label>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ $productUpdate->product->subCategory->view_link }}"
                           class="text-reset hover-primary" target="_blank">
                            <span class="fw-medium">{{ $productUpdate->product->subCategory->name }}</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Version --}}
            @if ($productUpdate->version)
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">{{ translate('Version') }}</label>
                    <div class="fw-medium">v{{ $productUpdate->version }}</div>
                </div>
            @endif

            {{-- Demo Link --}}
            @if ($productUpdate->demo_link)
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">{{ translate('Demo Link') }}</label>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ $productUpdate->view_demo }}" target="_blank" class="text-primary text-truncate" style="max-width: 250px;">
                            {{ $productUpdate->demo_link }}
                        </a>
                        <i class="bi bi-box-arrow-up-right small text-muted ms-2"></i>
                    </div>
                </div>
            @endif

            {{-- Custom Attributes --}}
            @if ($productUpdate->options && count($productUpdate->options) > 0)
                @foreach ($productUpdate->options as $key => $option)
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
                    @foreach ($productUpdate->getTags() as $tag)
                        <span class="status-badge bg-primary-subtle text-primary">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Media Section --}}
@if ($productUpdate->preview_image || $productUpdate->isVideoPreview() || $productUpdate->isAudioPreview() || $productUpdate->gallery)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent py-3">
            <i class="bi bi-images me-2"></i>{{ translate('Media') }}
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                {{-- Preview Image --}}
                @if (!$productUpdate->isAudioPreview() && $productUpdate->preview_image)
                    <div class="col-12">
                        <label class="form-label fw-medium d-block mb-2">{{ translate('Preview Image') }}</label>
                        <img class="img-fluid rounded" src="{{ $productUpdate->preview_image_url }}"
                             alt="{{ $productUpdate->name }}" style="max-height: 350px; object-fit: contain;">
                    </div>
                @endif

                {{-- Preview Video --}}
                @if ($productUpdate->isVideoPreview())
                    <div class="col-12">
                        <label class="form-label fw-medium d-block mb-2">{{ translate('Preview Video') }}</label>
                        <div class="product-single-video">
                            <video class="video-plyr w-100 rounded" poster="{{ $productUpdate->preview_image_url }}" playsinline controls>
                                <source src="{{ $productUpdate->preview_video_url }}" type="video/mp4">
                            </video>
                        </div>
                    </div>
                @endif

                {{-- Preview Audio --}}
                @if ($productUpdate->isAudioPreview())
                    <div class="col-12">
                        <label class="form-label fw-medium d-block mb-2">{{ translate('Preview Audio') }}</label>
                        <div class="product-single-audio">
                            <div class="product-audio-wave">
                                <div class="product-audio-actions md">
                                    <button class="play-button btn btn-primary btn-sm px-2">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                    <button class="pause-button btn btn-primary btn-sm px-2 d-none">
                                        <i class="bi bi-pause-fill"></i>
                                    </button>
                                </div>
                                <div class="current-time small">00:00</div>
                                <div class="waveform" data-url="{{ $productUpdate->preview_audio_url }}" data-waveheight="60"></div>
                                <div class="total-duration small">00:00</div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Gallery --}}
                @if ($productUpdate->gallery)
                    @php $galleryImages = $productUpdate->gallery_links; @endphp
                    <div class="col-12">
                        <label class="form-label fw-medium d-block mb-2">{{ translate('Gallery') }}</label>
                        <div id="carouselGallery" class="carousel slide rounded overflow-hidden" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach ($galleryImages as $image)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <img class="d-block w-100" src="{{ $image }}" style="max-height: 400px; object-fit: contain;">
                                    </div>
                                @endforeach
                            </div>
                            @if (count($galleryImages) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselGallery" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselGallery" data-bs-slide="next">
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

{{-- Pricing & Settings Section --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent py-3">
        <i class="bi bi-gear me-2"></i>{{ translate('Pricing & Settings') }}
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            {{-- License Prices --}}
            @if ($productUpdate->regular_price)
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">{{ translate('Regular Price') }}</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-decoration-line-through text-muted">
                            {{ getAmount($productUpdate->product->price->regular) }}
                        </span>
                        <i class="bi bi-arrow-right text-muted"></i>
                        <span class="fw-semibold text-success">{{ getAmount($productUpdate->price->regular) }}</span>
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">{{ translate('Regular Price') }}</label>
                    <div class="fw-semibold text-success">{{ getAmount($productUpdate->product->price->regular) }}</div>
                </div>
            @endif

            @if ($productUpdate->extended_price > 0)
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">{{ translate('Extended Price') }}</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-decoration-line-through text-muted">
                            {{ getAmount($productUpdate->product->price->extended ?? 0) }}
                        </span>
                        <i class="bi bi-arrow-right text-muted"></i>
                        <span class="fw-semibold text-success">{{ getAmount($productUpdate->price->extended ?? 0) }}</span>
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">{{ translate('Extended Price') }}</label>
                    <div class="fw-semibold text-success">{{ getAmount($productUpdate->product->price->extended ?? 0) }}</div>
                </div>
            @endif

            {{-- Free Product Status --}}
            <div class="col-md-6">
                <label class="form-label text-muted small mb-1">{{ translate('Free Product') }}</label>
                <div>
                    @if ($productUpdate->isFree())
                        <span class="badge bg-success">{{ translate('Yes') }}</span>
                        @if ($productUpdate->isPurchasingEnabled())
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
                        @if ($productUpdate->isSupported())
                            <span class="badge bg-success">{{ translate('Supported') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ translate('Not Supported') }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Support Instructions --}}
        @if (@$settings->product->support_status && $productUpdate->isSupported() && $productUpdate->support_instructions)
            <hr class="my-3">
            <div>
                <label class="form-label text-muted small mb-2">{{ translate('Support Instructions') }}</label>
                <div class="bg-light rounded p-3 small">
                    {!! sanitizeHtml($productUpdate->support_instructions, true) !!}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Licensing Details Section --}}
@if ($productUpdate->regular_price_label || $productUpdate->extended_price_label || count($productUpdate->getRegularExtraFeatures()) > 0 || count($productUpdate->getExtendedExtraFeatures()) > 0)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent py-3">
            <i class="bi bi-award me-2"></i>{{ translate('Licensing Details') }}
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                {{-- Regular License Section --}}
                @if ($productUpdate->regular_price_label || count($productUpdate->getRegularExtraFeatures()) > 0)
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold mb-3">
                                <i class="bi bi-tag text-primary me-2"></i>{{ translate('Regular License') }}
                            </h6>

                            @if ($productUpdate->regular_price_label)
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">{{ translate('Price Label') }}</label>
                                    <div class="fw-medium">{{ $productUpdate->regular_price_label }}</div>
                                </div>
                            @endif

                            @if (count($productUpdate->getRegularExtraFeatures()) > 0)
                                <div>
                                    <label class="form-label text-muted small mb-1">{{ translate('Extra Features') }}</label>
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($productUpdate->getRegularExtraFeatures() as $feature)
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
                @if ($productUpdate->extended_price_label || count($productUpdate->getExtendedExtraFeatures()) > 0)
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold mb-3">
                                <i class="bi bi-tags text-success me-2"></i>{{ translate('Extended License') }}
                            </h6>

                            @if ($productUpdate->extended_price_label)
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">{{ translate('Price Label') }}</label>
                                    <div class="fw-medium">{{ $productUpdate->extended_price_label }}</div>
                                </div>
                            @endif

                            @if (count($productUpdate->getExtendedExtraFeatures()) > 0)
                                <div>
                                    <label class="form-label text-muted small mb-1">{{ translate('Extra Features') }}</label>
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($productUpdate->getExtendedExtraFeatures() as $feature)
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
@if ($productUpdate->has_custom_services && $productUpdate->custom_services)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent py-3">
            <i class="bi bi-tools me-2"></i>{{ translate('Custom Services') }}
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-success">{{ translate('Available') }}</span>
                <span class="text-muted">{{ translate('This seller offers custom services for this product') }}</span>
            </div>
            <div class="bg-light rounded p-3">
                {!! nl2br(e($productUpdate->custom_services)) !!}
            </div>
        </div>
    </div>
@elseif ($productUpdate->has_custom_services)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent py-3">
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
