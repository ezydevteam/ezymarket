@php
$seller = $product->seller;
$options = $product->options ?? [];
$listCompact = ($viewStyle ?? 'grid') === 'list-compact';
@endphp

<div class="product product-list-item border p-3 mb-3 hover-shadow {{ $custom_class ?? '' }}">
    <div class="row g-4 align-items-center">
        {{-- Left: Image/Media Section --}}
        <div class="{{ $listCompact ? 'col-5' : 'col-12' }} col-md-4 col-lg-4">
            <div class="product-header rounded-2">

                {{-- Preview Media Section --}}
                @if ($product->isImagePreview())
                {{-- Image Preview --}}
                <a class="product-list-img d-block" href="{{ $product->view_link }}">
                    <img class="img-fluid w-100 object-fit-cover" src="{{ $product->preview_image_url }}"
                        alt="{{ $product->name }}" loading="lazy">
                </a>

                @elseif($product->isVideoPreview())
                {{-- Video Preview --}}
                <a href="{{ $product->view_link }}" class="opacity-100">
                    <div class="product-video">
                        <video class="plyr" poster="{{ $product->preview_image_url }}" muted>
                            <source src="{{ $product->preview_video_url }}">
                        </video>

                        {{-- Play Overlay --}}
                        <div class="product-video-play-overlay">
                            <i class="bi bi-play-circle-fill"></i>
                        </div>

                        {{-- Video Controls --}}
                        <div class="product-video-actions d-flex align-items-center justify-content-between gap-1">
                            <div class="product-video-volume product-video-action">
                                <i class="bi bi-volume-up" class="unmuted"></i>
                                <i class="bi bi-volume-mute" class="muted"></i>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <div class="product-video-full product-video-action">
                                    <i class="bi bi-arrows-angle-expand"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Video Progress Bar --}}
                        <div class="product-video-progress">
                            <span></span>
                        </div>
                    </div>
                </a>

                @elseif($product->isAudioPreview())
                {{-- Audio Preview --}}
                <div class="product-audio">
                    <a href="{{ $product->view_link }}" class="product-audio-link opacity-100"></a>

                    <div class="product-audio-wave">
                        {{-- Audio Controls --}}
                        <div class="product-audio-actions position-relative">
                            <button class="play-button btn btn-primary rounded-circle px-2">
                                <div class="play-button-icon">
                                    <i class="bi bi-play-fill"></i>
                                </div>
                            </button>
                            <button class="pause-button btn btn-primary rounded-circle px-2 d-none">
                                <div class="play-button-icon">
                                    <i class="bi bi-pause-fill"></i>
                                </div>
                            </button>
                        </div>

                        {{-- Waveform Display --}}
                        <div class="waveform" data-url="{{ $product->preview_audio_url }}" data-waveheight="50"></div>
                    </div>
                </div>
                @endif

                {{-- Badges logic from grid --}}
                @if (isPremiumAvailable() && $product->isPremium())
                <div class="product-badge product-badge-premium">
                    <i class="bi bi-gem me-1"></i>{{ translate('Premium') }}
                </div>
                @endif
                @if ($product->isFree())
                <div class="product-badge product-badge-free">
                    <i class="bi bi-gift me-1"></i>{{ translate('Free') }}
                </div>
                @elseif ($product->isOnDiscount())
                <div class="product-badge product-badge-sale text-lowercase">
                    <i class="bi bi-tags me-1"></i>{{ $product->discount->regular_percentage }}% {{ translate('off') }}
                </div>
                @elseif ($product->isTrending())
                <div class="product-badge product-badge-trending">
                    <i class="bi bi-lightning me-1"></i>{{ translate('Trending') }}
                </div>
                @endif

                {{-- Favorite Button --}}
                <div class="product-favorite-btn">
                    <livewire:favorite :product="$product" />
                </div>
            </div>
        </div>

        {{-- Center: Info Section --}}
        <div class="{{ $listCompact ? 'col-7' : 'col-12' }} col-md-5 col-lg-5">
            <div class="product-info-center">
                <h5 class="product-title fw-semibold mb-1">
                    <a href="{{ $product->view_link }}" class="text-dark lh-base">
                        {{ truncateText($product->name, $listCompact ? 50 : 65) }}
                    </a>
                </h5>
                <div class="d-flex gap-2 text-muted small mb-3">
                    <span>
                        {{ translate('by') }}
                        <a href="{{ $seller->profile_link }}" class="text-gray-200 hover-primary-underline">
                            {{ $seller->username }}
                        </a>
                    </span>
                    <span>
                        {{ translate('in') }}
                        <a href="{{ $product->category->view_link }}" class="text-gray-200 hover-primary-underline">
                            {{ $product->category->name }}
                        </a>
                    </span>
                </div>

                <div class="product-specs d-flex flex-column gap-2 {{ $listCompact ? 'd-none' : '' }}">
                    <div class="spec-item fs-13">
                        <span class="fw-medium text-muted">{{ translate('Support included:') }}</span>
                        <span class="ms-1">{{ $product->isSupported() ? translate('Yes') : translate('No') }}</span>
                    </div>

                    @if($product->version)
                    <div class="spec-item fs-13">
                        <span class="fw-medium text-muted">{{ translate('Current version:') }}</span>
                        <span class="ms-1">{{ $product->version }}</span>
                    </div>
                    @endif

                    @if(!empty($options['Files Included']))
                    <div class="spec-item fs-13">
                        <span class="fw-medium text-muted">{{ translate('Files included:') }}</span>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach((array)$options['Files Included'] as $file)
                            <span class="badge bg-light text-dark fw-medium border rounded-pill">{{ $file }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Price & Actions Section --}}
        <div class="col-12 col-md-3 col-lg-3 col-right">
            <div class="product-actions-right d-flex flex-column justify-content-center text-center h-100 p-2">
                <div class="price-section mb-3">
                    @if ($product->isFree())
                    <h3 class="fw-bold text-success mb-0">{{ translate('Free') }}</h3>
                    @else
                    <div class="d-flex flex-column align-items-center">
                        @if ($product->isOnDiscount())
                        <span class="text-gray-700 text-decoration-line-through">
                            {{ getAmount($product->price->regular, 2, '.', '', true) }}
                        </span>
                        <h3 class="fw-bold text-success mb-0">
                            {{ getAmount($product->discount->price->regular, 2, '.', '', true) }}
                        </h3>
                        @else
                        <h3 class="fw-bold mb-0">
                            {{ getAmount($product->price->regular, 2, '.', '', true) }}
                        </h3>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="rating-stats-section mb-3">
                    <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                        @themeInclude('partials.rating-stars', [
                        'ratings_classes' => 'ratings-sm',
                        'args' => $product,
                        'type' => 'full',
                        'star_size' => 'fs-12',
                        'rating_number' => false,
                        'counter_only' => true
                        ])
                    </div>
                    <div class="stats small">
                        <i class="bi bi-cart-check text-success me-1"></i>{{ numberFormat($product->total_sales ?? 0) }}
                        <span class="text-gray-700">{{ translate(($product->total_sales ?? 0) > 1 ? 'Sales' : 'Sale')
                            }}</span>
                        @if($product->last_updated_at)
                        <div class="text-gray-700 mt-1">
                            {{ translate('Last updated:') }} {{ $product->last_updated_at->format('d M y') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="actions-buttons d-flex flex-wrap justify-content-center gap-2">
                    @if (!empty($product->demo_link))
                    <a href="{{ $product->view_demo }}" target="_blank"
                        class="btn btn-outline-primary btn-sm btn-padding">
                        {{ translate('Live Preview') }}
                    </a>
                    @endif

                    @if (!$product->isFree())
                    <form data-action="{{ route('cart.add-product') }}" class="add-to-cart-form" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="license_type" value="1">
                        <button class="btn btn-primary btn-sm btn-padding d-flex gap-1" @disabled(authUser()?->id ==
                            $product->seller_id)
                            @if (!empty($product->demo_link)) title="{{ translate('Add to cart') }}" @endif>
                            <i class="bi bi-cart-plus-fill"></i>
                            @if (empty($product->demo_link)) {{ translate('Add to cart') }} @endif
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
