@php $data = (object)($data ?? []); @endphp

<div id="{{ $data->uniqueId }}" class="position-relative overflow-hidden w-100 hero-design-{{ $data->designStyle }}">

    {{-- Background --}}
    @if($data->designStyle === 'modern')
    <div class="hero-bg-desktop position-absolute top-0 end-0 w-50 h-100 z-0 d-none d-lg-block"></div>
    <div class="hero-bg position-absolute top-0 start-0 w-100 h-100 z-0 d-lg-none"></div>
    <div class="hero-overlay-panel position-absolute top-0 start-0 w-50 h-100 z-0 d-none d-lg-block"></div>
    @else
    @if($data->bgType == 'video' && $data->heroVideoUrl)
    @if($data->heroYoutubeId)
    <div class="position-absolute top-50 start-50 translate-middle z-0"
        style="width:300%;height:300%;pointer-events:none;">
        <iframe
            src="https://www.youtube.com/embed/{{ $data->heroYoutubeId }}?autoplay=1&mute=1&loop=1&playlist={{ $data->heroYoutubeId }}&controls=0&showinfo=0&rel=0&modestbranding=1&playsinline=1"
            class="w-100 h-100 border-0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
    @else
    <video autoplay muted loop playsinline
        class="position-absolute top-50 start-50 translate-middle min-w-100 min-h-100 object-fit-cover z-0">
        <source src="{{ $data->heroVideoUrl }}" type="video/mp4">
    </video>
    @endif
    @else
    <div class="hero-bg position-absolute top-0 start-0 w-100 h-100 z-0"></div>
    @endif
    @endif

    {{-- Overlay --}}
    @if($data->designStyle === 'gradient')
    <div class="hero-gradient-1 position-absolute top-0 start-0 w-100 h-100 z-0"></div>
    <div class="hero-gradient-2 position-absolute top-0 start-0 w-100 h-100 z-0"></div>
    @elseif($data->designStyle === 'modern')
    <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100 z-0 d-lg-none"></div>
    @else
    <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100 z-0"></div>
    @endif

    {{-- Content --}}
    <div class="{{ $data->containerClass }} position-relative z-2">
        <div class="row {{ $data->colAlign }}">
            <div class="col-12 hero-content-col">

                <div class="{{ $data->detailsAlign }} d-flex flex-column hero-box" data-aos="fade-up"
                    data-aos-duration="1000">

                    @if($data->title)
                    <h2 class="{{ $data->titleClass }} hero-title mb-1">
                        {!! nl2br(translate($data->title)) !!}
                    </h2>
                    @endif

                    @if($data->subtitle)
                    <p class="{{ $data->subtitleClass }} fs-5 mb-4 hero-subtitle">
                        {{ translate($data->subtitle) }}
                    </p>
                    @endif

                    @if($data->searchEnable)
                    <div class="mb-5 w-100 hero-search">
                        @include('themes.main.partials.search.search-form', [
                            'id' => 'heroSearchForm',
                            'wrapper_class' => 'input-group shadow-lg rounded-pill overflow-hidden bg-white p-1',
                            'input_class' => 'form-control border-0 shadow-none ps-4',
                            'btn_class' => 'btn btn-primary rounded-pill px-4',
                            'placeholder' => ($data->searchPlaceholder ?? '') ? translate($data->searchPlaceholder) : translate('Search for products...'),
                            'show_backdrop' => true
                        ])
                    </div>
                    @endif

                    @if($data->btn1Text || $data->btn2Text)
                    <div
                        class="d-flex gap-3 flex-wrap {{ $data->textAlign === 'center' ? 'justify-content-center' : '' }}">
                        @if($data->btn1Text)
                        <a href="{{ $data->btn1Url }}"
                            class="btn btn-{{ $data->btn1Class }} btn-lg px-4 py-2 rounded-pill fw-medium">
                            {{ translate($data->btn1Text) }}
                        </a>
                        @endif

                        @if($data->btn2Text)
                        <a href="{{ $data->btn2Url }}"
                            class="btn btn-{{ $data->btn2Class }} btn-lg px-4 py-2 rounded-pill fw-medium">
                            {{ translate($data->btn2Text) }}
                        </a>
                        @endif
                    </div>
                    @endif

                </div>

            </div>
        </div>
    </div>

    {{-- Bottom Fade/Curve --}}
    @if($data->showBottomFade)
    <div class="hero-bottom-fade position-absolute bottom-0 start-0 w-100 overflow-hidden z-2"></div>
    @endif

</div>
