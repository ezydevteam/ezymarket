@php
$data = (object)($data ?? []);
$homeCategories = $data->homeCategories;
$blockStyle = $data->blockStyle ?? 'swiper';
$blockAlignment = $data->blockAlignment ?? 'left';
$containerClass = $data->containerClass ?? 'container container-default';
$categoriesViewLink = route('categories.index');
@endphp

@if (!empty($homeCategories) && $homeCategories->count() > 0)
<div id="{{ $data->uniqueId ?? '' }}" class="{{ $containerClass }}">

    @themeInclude('blocks.home.partials.block-title', ['data' => $data, 'viewDefaultLink' => $categoriesViewLink])

    @if($blockStyle === 'classic')
    <div class="categories-grid">
        <div class="row g-3 justify-content-{{ $blockAlignment }}">
            @foreach ($homeCategories as $homeCategory)
            <div class="col-auto">
                <a href="{{ $homeCategory->link ?? '#' }}" class="home-category">
                    <img class="home-category-img"
                        src="{{ asset($homeCategory->image ?? 'images/placeholders/category.png') }}"
                        alt="{{ $homeCategory->title ?? '' }}" />
                    <h6 class="home-category-title text-dark fw-medium mt-3">{{ $homeCategory->title ?? '' }}</h6>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="categories-swiper position-relative justify-content-{{ $blockAlignment }}">
        <div class="swiper categoriesSwiper w-100 pt-1 pb-4">
            <div class="swiper-wrapper mb-2">
                @foreach ($homeCategories as $homeCategory)
                <div class="swiper-slide">
                    <a href="{{ $homeCategory->link ?? '#' }}" class="home-category">
                        <img class="home-category-img"
                            src="{{ asset($homeCategory->image ?? 'images/placeholders/category.png') }}"
                            alt="{{ $homeCategory->title ?? '' }}" />
                        <h6 class="home-category-title text-dark fw-medium mt-3">{{ $homeCategory->title ?? '' }}</h6>
                    </a>
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination mb-2"></div>
        </div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
    @endif
</div>
@endif
