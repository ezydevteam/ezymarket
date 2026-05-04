@php
$data = (object)($data ?? []);
$style = $data->blockStyle ?? 'default';
$size = $data->imageSize ?? 'w-100';
$corner = $data->imageCorner ?? 'rounded-0';
$align = $data->blockAlign ?? 'center';
$textAlign = 'text-' . $align;

// Image tag helper
$imgTag = '<img src="' . $data->blockImageUrl . '" class="img-fluid ' . $size . ' ' . $corner . ' object-fit-cover"
    alt="Image Block">';
if($data->blockImageLink) {
$imgTag = '<a href="' . $data->blockImageLink . '">' . $imgTag . '</a>';
}
@endphp

@if($data->blockImageUrl)
<div id="{{ $data->uniqueId }}" class="home-image  {{ $isFullWidth ? $data->containerClass : '' }}">
    {{-- Overlay Style: Title inside, absolute centered --}}
    @if($style == 'overlay')
    <div class="image-overlay-style position-relative text-center">
        {!! $imgTag !!}
        <div class="position-absolute top-50 start-50 translate-middle fit-content z-1">
            <div class="text-white px-4 py-2 rounded-4 {{ $textAlign }}"
                style="background: rgba(0,0,0,0.15); backdrop-filter: blur(4px)">
                @if($data->imageTitle)
                <h5 class="mb-2" data-aos="fade-down" data-aos-delay="200">{{ $data->imageTitle }}</h5>
                @endif
                @if($data->imageSubtitle)
                <p class="mb-0 text-opacity-75" data-aos="fade-up" data-aos-delay="400">{{ $data->imageSubtitle }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Card Style: Image with floating card overlay at bottom center (50% width of the IMAGE) --}}
    @elseif($style == 'card')
    <div class="image-card-style d-flex justify-content-{{ $align }} px-0 {{ $size === 'w-100' ? 'pb-5' : '' }}">
        <div class="position-relative {{ $size }}">
            @if($data->blockImageLink)
            <a href="{{ $data->blockImageLink }}" class="d-block">
                @endif
                <img src="{{ $data->blockImageUrl }}" class="img-fluid w-100 {{ $corner }} object-fit-cover"
                    alt="Image Block">
                @if($data->blockImageLink)
            </a>
            @endif
            <div
                class="position-absolute top-100 start-50 translate-middle bg-white shadow-sm px-3 py-2 rounded-4 w-75 z-1 mx-auto text-center">
                @if($data->imageTitle)
                <h5 class="mb-1" data-aos="fade-down" data-aos-delay="200">{{ $data->imageTitle }}</h5>
                @endif
                @if($data->imageSubtitle)
                <p class="text-gray mb-0" data-aos="fade-up" data-aos-delay="400">{{ $data->imageSubtitle }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Creative Split Style: Image Left, Title Right (Flex) --}}
    @elseif($style == 'creative_split')
    <div class="image-creative-style">
        <div class="card border-0 shadow-sm">
            <div class="row align-items-center g-3">
                <div class="col-md-6 order-md-1">
                    {!! $imgTag !!}
                </div>
                <div class="col-md-6 order-md-2 {{ $textAlign }}">
                    <div class="p-3">
                        @if($data->imageTitle)
                        <h5 class="mb-2" data-aos="fade-down" data-aos-delay="200">{{ $data->imageTitle }}</h5>
                        @endif
                        @if($data->imageSubtitle)
                        <p class="text-gray mb-0" data-aos="fade-up" data-aos-delay="400">{{ $data->imageSubtitle }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Default Style: Title separate (above), Image below --}}
    <div class="image-default-style">
        <div class="d-flex flex-column align-items-{{ $align }} {{ $textAlign }}">
            @themeInclude('blocks.home.partials.block-title', ['data' => $data])
            <div class="w-100">
                {!! $imgTag !!}
            </div>
        </div>
    </div>
    @endif
</div>
@endif
