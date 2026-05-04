@php $data = (object)($data ?? []); @endphp
@if (!empty($data->testimonials) && $data->testimonials->count() > 0)
<div id="{{ $data->uniqueId ?? 'testimonials' }}"
    class="testimonials-section {{ empty($data->disableBg) ? 'bg-light py-4 rounded-4' : '' }}">
    <div class="{{ $data->containerClass ?? 'container' }}">
        @themeInclude('blocks.home.partials.block-title', ['data' => $data])
        <div class="section-body">
            @if(($data->blockStyle ?? 'swiper') === 'grid')
            {{-- Grid Layout --}}
            <div class="row g-3 justify-content-{{ $data->blockAlignment ?? 'start' }}">
                @foreach ($data->testimonials as $testimonial)
                <div class="col-auto" data-aos="zoom-in" data-aos-duration="1000">
                    @if(!empty($testimonial->show_image))
                    <div class="testimonial testimonial-image-only modern-card-3">
                        <img src="{{ asset($testimonial->testimonial_image ?: ($testimonial->image ?? 'images/placeholders/user.png')) }}"
                            alt="{{ $testimonial->name ?? '' }}" class="img-fluid rounded" />
                    </div>
                    @else
                    <div class="testimonial modern-card-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="testimonial-img">
                                <img src="{{ asset($testimonial->image ?? 'images/placeholders/user.png') }}"
                                    alt="{{ $testimonial->name ?? '' }}" />
                            </div>
                            <div class="testimonial-info">
                                <h6 class="fw-semibold mb-0">{{ $testimonial->name ?? '' }}</h6>
                                <p class="text-muted mb-0">{{ $testimonial->designation ?? '' }}</p>
                            </div>
                        </div>
                        <div class="testimonial-rating mt-1">
                            @for($i = 1; $i <= 5; $i++) <i
                                class="bi bi-star-fill {{ $i <= ($testimonial->rating ?? 5) ? 'text-warning' : 'text-dark opacity-25' }}">
                                </i>
                                @endfor
                        </div>
                        <p class="testimonial-text mt-3">{{ $testimonial->comment }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            {{-- Swiper Layout --}}
            <div class="testimonials-swiper mt-0">
                <div class="swiper testimonialsSwiper p-1" {{ !empty($data->disableAutoplay) ? 'data-no-autoplay' : ''
                    }}>
                    <div class="swiper-wrapper justify-content-{{ $data->blockAlignment ?? 'start' }}">
                        @foreach ($data->testimonials as $testimonial)
                        <div class="swiper-slide h-100 w-auto" data-aos="zoom-in" data-aos-duration="1000">
                            @if(!empty($testimonial->show_image))
                            <div class="testimonial testimonial-image-only modern-card-3">
                                <img src="{{ asset($testimonial->testimonial_image ?: ($testimonial->image ?? 'images/placeholders/user.png')) }}"
                                    alt="{{ $testimonial->name ?? '' }}" class="img-fluid rounded" />
                            </div>
                            @else
                            <div class="testimonial modern-card-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="testimonial-img">
                                        <img src="{{ asset($testimonial->image ?? 'images/placeholders/user.png') }}"
                                            alt="{{ $testimonial->name ?? '' }}" />
                                    </div>
                                    <div class="testimonial-info">
                                        <h6 class="fw-semibold mb-0">{{ $testimonial->name ?? '' }}</h6>
                                        <p class="text-muted mb-0">{{ $testimonial->designation ?? '' }}</p>
                                    </div>
                                </div>
                                <div class="testimonial-rating mt-1">
                                    @for($i = 1; $i <= 5; $i++) <i
                                        class="bi bi-star-fill {{ $i <= ($testimonial->rating ?? 5) ? 'text-warning' : 'text-dark opacity-25' }}">
                                        </i>
                                        @endfor
                                </div>
                                <p class="testimonial-text mt-3">{{ $testimonial->comment }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
