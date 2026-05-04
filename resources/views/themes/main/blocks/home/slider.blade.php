@php $data = (object)($data ?? []); @endphp

@if(count($data->slides ?? []) > 0)
<div id="{{ $data->sliderId }}" class="home-slider {{ $isFullWidth ? $data->containerClass : '' }}">
    @themeInclude('blocks.home.partials.block-title', ['data' => $data])
    <div class="position-relative">
        <div class="swiper slider-{{ $data->sliderId }}">
            <div class="swiper-wrapper">
                @foreach($data->slides as $slide)
                @php $slide = (object)$slide; @endphp
                @if($slide->imageUrl)
                <div class="swiper-slide position-relative">

                    @if(($data->blockStyle ?? 'default') === 'creative')
                    <div class="creative-split">
                        <div
                            class="d-flex justify-content-{{ $data->blockAlignment ?? 'center' }} text-center creative-split-content position-relative overflow-hidden">
                            <!-- Match Gradient Background -->
                            <div class="creative-bg-blur" style="background-image: url('{{ $slide->imageUrl }}');">
                            </div>
                            <div class="creative-bg-overlay"></div>
                            <div class="creative-content-inner">
                                @if(!empty($slide->caption))
                                <p class="mb-0 fs-5">{{ $slide->caption }}</p>
                                @endif
                                @if(!empty($slide->link))
                                <a href="{{ $slide->link }}"
                                    class="btn btn-sm btn-light mt-3 rounded-pill px-4 fw-medium text-uppercase"
                                    style="font-size: 0.85rem;">{{ translate('Learn More') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="creative-split-image">
                            <img src="{{ $slide->imageUrl }}" class="w-100 h-100 slide-img" alt="Slide">
                        </div>
                    </div>

                    @elseif(($data->blockStyle ?? 'default') === 'modern')
                    {{-- Modern: Floating Card --}}
                    <img src="{{ $slide->imageUrl }}" class="w-100 h-100 rounded-4 slide-img" alt="Slide">
                    @if(!empty($slide->caption))
                    @php
                    $alignment = $data->blockAlignment === 'center'
                    ? 'start-50 mx-auto'
                    : ($data->blockAlignment === 'end' ? 'end-0 me-md-5 me-3' : 'start-0 ms-md-5 ms-3');
                    @endphp
                    <div class="position-absolute top-50 translate-middle-y {{ $alignment }} z-1">
                        <div class="modern-card text-center" data-aos="fade-up" data-aos-delay="200">
                            <p class="mb-0 fw-medium text-white">{{ $slide->caption }}</p>
                            @if(!empty($slide->link))
                            <a href="{{ $slide->link }}" class="btn btn-sm btn-dark mt-3 rounded-pill px-4">{{
                                translate('Discover') }}</a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @else
                    {{-- Common Image Link for other styles (Default, Centered, Minimal) --}}
                    <a href="{{ $slide->link ?? '#' }}" class="d-block w-100 h-100">
                        <img src="{{ $slide->imageUrl }}" class="w-100 h-100 rounded-4 slide-img" alt="Slide">
                    </a>

                    @if(!empty($slide->caption))
                    @if(($data->blockStyle ?? 'default') === 'centered')
                    <div
                        class="position-absolute top-50 start-50 translate-middle z-1 text-center w-100 px-3 pointer-events-none">
                        <p class="m-0 centered-bubble shadow-sm">{{ $slide->caption }}</p>
                    </div>
                    @elseif(($data->blockStyle ?? 'default') === 'minimal')
                    {{-- Minimal: No caption overlay --}}
                    @else
                    {{-- Default Style: Fade Caption --}}
                    <div
                        class="position-absolute bottom-0 start-0 w-100 text-white p-4 pt-5 rounded-bottom-4 default-gradient z-1 pointer-events-none text-{{ $data->blockAlignment ?? 'center' }}">
                        <p class="m-0 fw-medium" data-aos="fade-up" data-aos-delay="200">{{ $slide->caption }}</p>
                    </div>
                    @endif
                    @endif
                    @endif
                </div>
                @endif
                @endforeach
            </div>
        </div>

        @if(($data->blockStyle ?? 'default') !== 'minimal')
        @if($data->sliderNavigation)
        <div class="{{ $data->sliderId }}-next swiper-button-next"></div>
        <div class="{{ $data->sliderId }}-prev swiper-button-prev"></div>
        @endif
        @if($data->sliderPagination)
        <div class="{{ $data->sliderId }}-pagination swiper-pagination"></div>
        @endif
        @endif
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var sliderConfig = {
            loop: @json($data -> sliderLoop),
            autoplay: false
        };

        @if ($data -> sliderAutoplay)
            sliderConfig.autoplay = {
                delay: @json($data -> sliderDelay),
                pauseOnMouseEnter: @json($data -> sliderPause),
                disableOnInteraction: false
            };
        @endif

        @if (($data -> blockStyle ?? 'default') === 'creative')
            sliderConfig.effect = 'fade';
            sliderConfig.fadeEffect = { crossFade: true };
            sliderConfig.speed = 800;
        @endif

        @if (($data -> blockStyle ?? 'default') !== 'minimal')
            @if ($data -> sliderNavigation)
            sliderConfig.navigation = {
                nextEl: '.{{ $data->sliderId }}-next',
                prevEl: '.{{ $data->sliderId }}-prev'
            };
        @endif
        @if ($data -> sliderPagination)
            sliderConfig.pagination = {
                el: '.{{ $data->sliderId }}-pagination',
                clickable: true
            };
        @endif
        @endif

        new Swiper('.slider-{{ $data->sliderId }}', sliderConfig);
    });
</script>
@endpush
@endif
