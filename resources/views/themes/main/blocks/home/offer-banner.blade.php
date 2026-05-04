@php $data = (object)($data ?? []); @endphp

<div id="{{ $data->uniqueId }}" class="home-offer-banner {{ $isFullWidth ? $data->containerClass : '' }}">
    @if(($data->bannerStyle ?? 'modern') === 'glass')
    {{-- Glassmorphism Style --}}
    <div class="glass-card d-flex align-items-center justify-content-center text-center rounded-4">
        <div class="glass-content mx-auto" style="max-width: 600px;">
            @if($data->bannerShowMegaphone)
            <div class="mb-3 text-primary" data-aos="fade-down" data-aos-delay="200">
                <i class="bi bi-megaphone-fill display-4"></i>
            </div>
            @endif
            @if($data->bannerTitle)
            <h2 class="display-5 fw-bold mb-3">{{ translate($data->bannerTitle) }}</h2>
            @endif
            @if($data->bannerSubtitle)
            <p class="lead mb-4">{{ translate($data->bannerSubtitle) }}</p>
            @endif
            @if($data->bannerRegularPrice || $data->bannerOfferPrice)
            <div class="mb-4" data-aos="fade-right" data-aos-delay="200">
                @if($data->bannerRegularPrice)
                <span class="text-decoration-line-through text-dark-50 me-2">{{
                    getAmount($data->bannerRegularPrice ?? 0) }}</span>
                @endif
                @if($data->bannerOfferPrice)
                <span class="fs-4 fw-bold text-success">{{ getAmount($data->bannerOfferPrice ?? 0) }}</span>
                @endif
            </div>
            @endif
            @if($data->bannerBtnText)
            <a href="{{ $data->bannerBtnUrl }}"
                class="btn btn-{{ $data->bannerBtnStyle }} btn-lg rounded-pill px-5 shadow-sm"
                data-aos="fade-up" data-aos-delay="400">
                @if($data->bannerBtnIcon) <i class="bi {{ $data->bannerBtnIcon }} me-2"></i> @endif
                {{ translate($data->bannerBtnText) }}
            </a>
            @endif
        </div>
    </div>

    @elseif(($data->bannerStyle ?? 'modern') === 'minimal')
    {{-- Minimal Border Style --}}
    <div class="minimal-card overflow-hidden bg-{{ $data->bannerBgStyle }}">
        @if($data->bannerImage)
        <img src="{{ $data->bannerImage }}" class="w-100 object-fit-cover banner-img"
            alt="{{ $data->bannerTitle }}">
        @endif
        <div class="minimal-content {{ $data->bannerTextAlign }}">
            <div class="mx-auto" style="max-width: 700px;">
                @if($data->bannerShowMegaphone)
                <div class="mb-3 text-primary"><i class="bi bi-megaphone-fill display-5"></i></div>
                @endif
                @if($data->bannerTitle)
                <h3 class="h2 fw-bold mb-3">{{ translate($data->bannerTitle) }}</h3>
                @endif
                @if($data->bannerSubtitle)
                <p class="text-muted mb-4">{{ translate($data->bannerSubtitle) }}</p>
                @endif
                @if($data->bannerRegularPrice || $data->bannerOfferPrice)
                <div class="mb-4">
                    @if($data->bannerRegularPrice)
                    <span class="text-decoration-line-through text-muted me-2">{{
                        getAmount($data->bannerRegularPrice ?? 0) }}</span>
                    @endif
                    @if($data->bannerOfferPrice)
                    <span class="fs-4 fw-bold text-dark">{{ getAmount($data->bannerOfferPrice ?? 0) }}</span>
                    @endif
                </div>
                @endif
                @if($data->bannerBtnText)
                <a href="{{ $data->bannerBtnUrl }}"
                    class="btn btn-{{ $data->bannerBtnStyle }} rounded-pill px-4 text-uppercase fw-medium"
                    data-aos="fade-up" data-aos-delay="400">
                    @if($data->bannerBtnIcon) <i class="bi {{ $data->bannerBtnIcon }} me-2"></i> @endif
                    {{ translate($data->bannerBtnText) }}
                </a>
                @endif
            </div>
        </div>
    </div>

    @elseif(($data->bannerStyle ?? 'modern') === 'creative')
    {{-- Creative Split Style --}}
    <div class="creative-card shadow-sm bg-{{ $data->bannerBgStyle }}">
        <div class="row g-0 align-items-stretch {{ $data->bannerIsReverse ? 'flex-row-reverse' : '' }}">
            @if($data->bannerImage)
            <div class="col-lg-6">
                <img src="{{ $data->bannerImage }}" class="w-100 h-100 object-fit-cover banner-img"
                    alt="{{ $data->bannerTitle }}">
            </div>
            @endif
            <div class="col-lg-6 d-flex align-items-center">
                <div class="creative-content w-100 {{ $data->bannerTextAlign }}">
                    @if($data->bannerShowMegaphone)
                    <div class="mb-3 text-primary" data-aos="fade-down" data-aos-delay="200">
                        <i class="bi bi-megaphone-fill display-4"></i>
                    </div>
                    @endif
                    @if($data->bannerTitle)
                    <h2 class="display-6 fw-black mb-3">{{ translate($data->bannerTitle) }}</h2>
                    @endif
                    @if($data->bannerSubtitle)
                    <p class="fs-5 text-muted mb-4">{{ translate($data->bannerSubtitle) }}</p>
                    @endif
                    @if($data->bannerRegularPrice || $data->bannerOfferPrice)
                    <div class="mb-3" data-aos="fade-right" data-aos-delay="200">
                        @if($data->bannerRegularPrice)
                        <span class="text-decoration-line-through text-muted me-2">{{
                            getAmount($data->bannerRegularPrice ?? 0) }}</span>
                        @endif
                        @if($data->bannerOfferPrice)
                        <span class="fs-4 fw-bold text-success">{{ getAmount($data->bannerOfferPrice ?? 0) }}</span>
                        @endif
                    </div>
                    @endif
                    @if($data->bannerBtnText)
                    <a href="{{ $data->bannerBtnUrl }}"
                        class="btn btn-{{ $data->bannerBtnStyle }} btn-lg rounded-pill px-5 shadow"
                        data-aos="fade-up" data-aos-delay="400">
                        @if($data->bannerBtnIcon) <i class="bi {{ $data->bannerBtnIcon }} me-2"></i> @endif
                        {{ translate($data->bannerBtnText) }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- Modern Card (Default) --}}
    <div class="card border-0 shadow-sm overflow-hidden rounded-4 bg-{{ $data->bannerBgStyle }}">
        <div class="row g-0 align-items-center {{ $data->bannerIsReverse ? 'flex-row-reverse' : '' }}">
            @if($data->bannerImage)
            <div class="col-md-6">
                <img src="{{ $data->bannerImage }}" class="w-100 h-100 object-fit-cover banner-img"
                    alt="{{ $data->bannerTitle }}">
            </div>
            @endif
            <div class="{{ $data->bannerImage ? 'col-md-6' : 'col-12' }} p-3 {{ $data->bannerTextAlign }}">
                <div class="card-body p-0">
                    @if($data->bannerShowMegaphone)
                    <div class="mb-3 text-primary" data-aos="fade-down" data-aos-delay="200">
                        <i class="bi bi-megaphone-fill display-5"></i>
                    </div>
                    @endif
                    @if($data->bannerTitle)
                    <h2 class="display-6 fw-bold mb-3">{{ translate($data->bannerTitle) }}</h2>
                    @endif
                    @if($data->bannerSubtitle)
                    <p class="lead text-gray mb-4">{{ translate($data->bannerSubtitle) }}</p>
                    @endif
                    @if($data->bannerRegularPrice || $data->bannerOfferPrice)
                    <div class="mb-4" data-aos="fade-right" data-aos-delay="200">
                        @if($data->bannerRegularPrice)
                        <span class="text-decoration-line-through text-gray me-2">{{
                            getAmount($data->bannerRegularPrice ?? 0) }}</span>
                        @endif
                        @if($data->bannerOfferPrice)
                        <span class="fs-4 fw-bold text-success">{{ getAmount($data->bannerOfferPrice ?? 0)
                            }}</span>
                        @endif
                    </div>
                    @endif
                    @if($data->bannerBtnText)
                    <a href="{{ $data->bannerBtnUrl }}"
                        class="btn btn-{{ $data->bannerBtnStyle }} btn-lg rounded-pill px-5"
                        data-aos="fade-up" data-aos-delay="400">
                        @if($data->bannerBtnIcon) <i class="bi {{ $data->bannerBtnIcon }} me-2"></i> @endif
                        {{ translate($data->bannerBtnText) }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
