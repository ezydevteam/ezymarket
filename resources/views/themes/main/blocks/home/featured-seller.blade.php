@php
$data = (object)($data ?? []);
$featuredSellerBlock = $data->featuredSellerBlock ?? null;
$featuredSeller = $data->featuredSeller ?? null;
$verifiedBadge = $featuredSeller?->hasVerifiedBadge();
@endphp
@if ($featuredSellerBlock && $featuredSeller)
<div id="{{ $data->uniqueId }}" class="home-featured-seller {{ $isFullWidth ? $data->containerClass : '' }}">
    <div class="featured-seller-wrapper">
        <div class="featured-seller-card p-4 rounded-4 border bg-{{ $data->bgStyle }}" data-aos="fade-up"
            data-aos-duration="1000">
            @themeInclude('blocks.home.partials.block-title', ['data' => $data])
            <div class="seller-minimal-card d-flex flex-column flex-lg-row align-items-center gap-4">
                <div class="seller-info flex-shrink-0 text-center">
                    <div class="user-avatar user-avatar-xxl mb-3 mx-auto mx-lg-0">
                        <a href="{{ $featuredSeller->profile_link }}">
                            <img src="{{ $featuredSeller->avatar_url }}" alt="{{ $featuredSeller->username }}"
                                class="rounded-circle shadow-sm">
                        </a>
                    </div>
                    <h5 class="mb-2 text-dark">
                        <a href="{{ $featuredSeller->profile_link }}" class="text-dark">{{ $featuredSeller->username}}
                            @if (isset($verifiedBadge) && $verifiedBadge)
                            <span class="verified-badge" title="{{ translate('Verified seller') }}">
                                <img src="{{ $verifiedBadge->image_url }}" alt="{{ $verifiedBadge->name }}"
                                    width="12" height="12">
                            </span>
                            @endif
                        </a>
                    </h5>
                    <div class="d-flex align-items-center justify-content-center small mb-4">
                        <div>
                            {{ numberFormat($featuredSeller->total_sales ?? 0) }}
                            <span class="text-muted text-lowercase">{{ translate('sales') }}</span>
                        </div>
                        <span class="dot-seperator"></span>
                        @themeInclude('partials.rating-stars', [
                        'ratings_classes' => 'ratings-sm',
                        'args' => $featuredSeller,
                        'counter' => true,
                        'counter_label' => 'reviews'
                        ])
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        @if ($featuredSeller->IsSeller())
                        <a href="{{ $featuredSeller->portfolio_link }}"
                            class="btn btn-outline-primary btn-sm btn-modern">
                            {{ translate('Portfolio') }}
                        </a>
                        @endif
                        <livewire:follow :user="$featuredSeller" :btnClass="'primary'" />
                    </div>
                </div>

                <div class="seller-products flex-grow-1 w-100">
                    @if ($featuredSeller->products->count() > 0)
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3 justify-content-center">
                        @foreach ($featuredSeller->products->take(3) as $featuredSellerProduct)
                        <div class="col" data-aos="fade-left" data-aos-duration="800"
                            data-aos-delay="{{ $loop->index * 100 }}">
                            @themeInclude('products.partials.grid-product', [
                            'product' => $featuredSellerProduct,
                            'custom_class' => 'featured-seller-product shadow-sm',
                            'customMetaStyle' => 'minimal',
                            ])
                        </div>
                        @endforeach
                    </div>
                    @else
                    @themeInclude('partials.no-products')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
