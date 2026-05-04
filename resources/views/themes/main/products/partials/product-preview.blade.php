<div class="product-preview-section bg-light-subtle border mb-4">
    <div class="preview-container">
        @if ($product->isImagePreview())
        <div class="product-single-img">
            <img src="{{ $product->preview_image_url }}" alt="{{ $product->name }}" />
        </div>
        @elseif($product->isVideoPreview())
        <div class="product-single-video rounded-3 overflow-hidden">
            <video class="video-plyr" poster="{{ $product->preview_image_url }}" controls>
                <source src="{{ $product->preview_video_url }}">
            </video>
        </div>
        @elseif($product->isAudioPreview())
        <div class="product-single-audio bg-gradient-to-br from-purple-100 to-blue-100 rounded-4 p-4">
            <div class="product-audio-wave d-flex align-items-center justify-content-center gap-4">
                <div class="product-audio-actions d-flex align-items-center justify-content-center">
                    <button class="play-button btn btn-gradient rounded-circle text-center">
                        <i class="bi bi-play-fill fs-4"></i>
                    </button>
                    <button class="pause-button btn btn-gradient rounded-circle text-center d-none">
                        <i class="bi bi-pause-fill fs-4"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="current-time badge bg-light text-dark">00:00</div>
                    <div class="waveform flex-grow-1" data-url="{{ $product->preview_audio_url }}" data-waveheight="60">
                    </div>
                    <div class="total-duration badge bg-light text-dark">00:00</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Floating Meta Items for Fullwidth Layout --}}
        @if ($data->display_layout !== 'minimalist' && ($data->meta_favorite_btn || $data->meta_share_btn ||
        $data->meta_report_btn))
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

        @if($data->meta_product_badge)
        @if (isPremiumAvailable() && $product->isPremium())
        <div class="product-badge product-badge-premium">
            <i class="bi bi-gem me-1"></i>{{ translate('Premium') }}
        </div>
        @endif
        @if ($product->isFree())
        <div class="product-badge product-badge-free">
            <i class="bi bi-gift me-1"></i>
            {{ translate('Free') }}
        </div>
        @elseif ($product->isOnDiscount())
        <div class="product-badge product-badge-sale text-lowercase">
            <i class="bi bi-tags me-1"></i>
            {{ $product->discount->regular_percentage }}% {{ translate('off') }}
        </div>
        @elseif ($product->isTrending())
        <div class="product-badge product-badge-trending">
            <i class="bi bi-lightning me-1"></i>
            {{ translate('Trending') }}
        </div>
        @endif
        @endif

        <!-- Hover Overlay -->
        @if ($data->preview_gallery_display === 'overlay' || $data->preview_gallery_display === 'both' &&
        ($product->demo_link || $product->gallery))
        @php $img = $product->isImagePreview(); @endphp
        <div class="{{ $img ? 'preview-overlay' : 'd-none' }}">
            @themeInclude('products.partials.preview-gallery-buttons', [
            'wrapperClass' => 'm-0',
            'previewBtnClass' => 'rounded-pill shadow-sm',
            'galleryBtnClass' => 'rounded-pill shadow-sm'
            ])
        </div>
        @endif
    </div>
    @if ($data->display_layout !== 'gallery_focus' && ($data->preview_gallery_display === 'default' ||
    $data->preview_gallery_display === 'both'))
    @themeInclude('products.partials.preview-gallery-buttons')
    @endif
</div>
