<section class="product-hero-header position-relative bg-dark text-white py-5">
    {{-- Background: Image --}}
    @if ($product->isImagePreview() && $product->preview_image_url)
    <div class="hero-bg-layer" style="background-image: url('{{ $product->preview_image_url }}');"></div>
    @endif

    {{-- Background: Video --}}
    @if ($product->isVideoPreview() && $product->preview_video_url)
    <video class="hero-bg-layer" autoplay muted loop playsinline>
        <source src="{{ $product->preview_video_url }}">
    </video>
    @endif

    {{-- Background: Audio (thumbnail fallback) --}}
    @if ($product->isAudioPreview() && $product->getImageLink())
    <div class="hero-bg-layer" style="background-image: url('{{ $product->getImageLink() }}');"></div>
    @endif

    {{-- Dark Overlay --}}
    <div class="hero-bg-layer bg-dark opacity-75"></div>

    {{-- Content --}}
    <div class="{{ $data->container_class }} position-relative z-1">
        <div class="row align-items-center min-vh-25">
            <div class="col-lg-8 mx-auto text-center">
                @themeInclude('products.partials.product-meta')

                {{-- Audio Player --}}
                @if ($product->isAudioPreview() && $product->preview_audio_url)
                <div class="hero-audio-wrapper mt-3 mx-auto">
                    <audio class="hero-audio-player w-100" controls>
                        <source src="{{ $product->preview_audio_url }}">
                    </audio>
                </div>
                @endif

                @if($data->meta_product_badge || $data->preview_gallery_display !== 'hide')
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mt-4">
                    @if($data->meta_product_badge)
                    @if (isPremiumAvailable() && $product->isPremium())
                    <div class="product-badge-hero product-badge-premium">
                        <i class="bi bi-gem me-1"></i>{{ translate('Premium') }}
                    </div>
                    @endif
                    @if ($product->isFree())
                    <div class="product-badge-hero product-badge-free">
                        <i class="bi bi-gift me-1"></i>{{ translate('Free') }}
                    </div>
                    @elseif ($product->isOnDiscount())
                    <div class="product-badge-hero product-badge-sale text-lowercase">
                        <i class="bi bi-tags me-1"></i>{{ $product->discount->regular_percentage }}% {{ translate('off')
                        }}
                    </div>
                    @elseif ($product->isTrending())
                    <div class="product-badge-hero product-badge-trending">
                        <i class="bi bi-lightning me-1"></i>{{ translate('Trending') }}
                    </div>
                    @endif
                    @endif

                    {{-- Live Preview & Gallery (visible on hover) --}}
                    @if (($data->preview_gallery_display !== 'hide') && ($product->demo_link || $product->gallery))
                    <span class="hero-action-btns">
                        @themeInclude('products.partials.preview-gallery-buttons', [
                        'wrapperClass' => 'm-0',
                        'previewBtnClass' => 'btn-primary rounded-pill shadow-sm',
                        'galleryBtnClass' => 'btn-dark-subtle rounded-pill shadow-sm'
                        ])
                    </span>
                    @endif
                </div>
                @endif
                @if ($data->meta_favorite_btn || $data->meta_share_btn || $data->meta_report_btn)
                <div class="product-floating-meta">
                    @if($data->meta_favorite_btn)
                    <div class="floating-meta-item" title="{{ translate('Favorite') }}">
                        <livewire:favorite :product="$product" :key="'floating-fav-'.$product->id" />
                    </div>
                    @endif
                    @if($data->meta_share_btn)
                    <button class="floating-meta-item" data-bs-toggle="modal" data-bs-target="#productShareModal"
                        title="{{ translate('Share') }}">
                        <i class="bi bi-reply text-dark"></i>
                    </button>
                    @endif
                    @if($data->meta_report_btn)
                    <button class="floating-meta-item" data-bs-toggle="modal" data-bs-target="#reportProductModal"
                        data-item-name="{{ $product->name }}"
                        data-report-url="{{ route('products.report.store', ['slug' => $product->slug, 'product' => $product->id]) }}"
                        title="{{ translate('Report') }}">
                        <i class="bi bi-flag text-dark"></i>
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
