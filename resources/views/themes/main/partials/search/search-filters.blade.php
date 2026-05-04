<div id="searchFiltersMenu">
    <x-offcanvas id="searchFilters" placement="start" :title="translate('Filter Options')"
        icon="bi-sliders2 text-primary" bodyClass="p-0">
        <div class="filter-container d-flex flex-column gap-4 p-4">

            {{-- Sub-categories --}}
            @isset($category)
            @if ($category->subCategories->count() > 0)
            <div class="filter-section">
                <h6
                    class="filter-title fw-semibold text-uppercase text-gray-700 fs-13 border-start border-primary border-3 mb-3 ps-2">
                    {{ $category->name }}
                </h6>
                <div class="filter-list d-flex flex-column gap-2">
                    @foreach ($category->subCategories as $subCategory)
                    <a href="{{ route('categories.sub-category', [$category->slug, $subCategory->slug] + request()->all()) }}"
                        class="d-flex align-items-center justify-content-between p-2 rounded text-dark hover-primary-light transition-in-out">
                        <span class="fs-15">{{ $subCategory->name }}</span>
                        <i class="bi bi-chevron-right fs-12 text-muted"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            @endisset

            {{-- General Options --}}
            <div class="filter-section">
                <h6
                    class="filter-title fw-semibold text-uppercase text-gray-700 fs-13 border-start border-primary border-3 mb-3 ps-2">
                    {{ translate('Type & Status') }}
                </h6>
                <div class="filter-list d-flex flex-column gap-2">
                    <div class="form-check custom-check">
                        <input class="form-check-input filter-input" type="checkbox" name="free" value="true" id="op1"
                            {{ request('free')=='true' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="op1">{{ translate('Free') }}</label>
                    </div>
                    @if (isPremiumAvailable())
                    <div class="form-check custom-check">
                        <input class="form-check-input filter-input" type="checkbox" name="premium" value="true"
                            id="op2" {{ request('premium')=='true' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="op2">{{ translate('Premium')
                            }}</label>
                    </div>
                    @endif
                    <div class="form-check custom-check">
                        <input class="form-check-input filter-input" type="checkbox" name="on_sale" value="true"
                            id="op3" {{ request('on_sale')=='true' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="op3">{{ translate('Discounted')
                            }}</label>
                    </div>
                    <div class="form-check custom-check">
                        <input class="form-check-input filter-input" type="checkbox" name="best_selling" value="true"
                            id="op4" {{ request('best_selling')=='true' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="op4">{{ translate('Best Selling')
                            }}</label>
                    </div>
                    <div class="form-check custom-check">
                        <input class="form-check-input filter-input" type="checkbox" name="trending" value="true"
                            id="op5" {{ request('trending')=='true' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="op5">{{ translate('Trending')
                            }}</label>
                    </div>
                    <div class="form-check custom-check">
                        <input class="form-check-input filter-input" type="checkbox" name="featured" value="true"
                            id="op6" {{ request('featured')=='true' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="op6">{{ translate('Featured')
                            }}</label>
                    </div>
                </div>
            </div>

            {{-- Price Range --}}
            <div class="filter-section">
                <h6
                    class="filter-title fw-semibold text-uppercase text-gray-700 fs-13 border-start border-primary border-3 mb-3 ps-2">
                    {{ translate('Price Range') }}
                </h6>
                <div class="price-inputs d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0 text-gray-700 px-2">{{
                            currency_symbol() }}</span>
                        <input id="priceForm" type="number" name="min_price" class="form-control ps-1"
                            placeholder="{{ translate('Min') }}" value="{{ request()->input('min_price') }}" />
                    </div>
                    <span class="text-muted">_</span>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0 text-gray-700 px-2">{{
                            currency_symbol() }}</span>
                        <input id="priceTo" type="number" name="max_price" class="form-control ps-1"
                            placeholder="{{ translate('Max') }}" value="{{ request()->input('max_price') }}" />
                    </div>
                    <button id="searchByPrice" type="button" class="btn btn-primary btn-sm px-3 rounded">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- Rating --}}
            @if (@$settings->product->reviews_status ?? false)
            <div class="filter-section">
                <h6
                    class="filter-title fw-semibold text-uppercase text-gray-700 fs-13 border-start border-primary border-3 mb-3 ps-2">
                    {{ translate('Rating') }}
                </h6>
                <div class="filter-list d-flex flex-column gap-2">
                    <div class="form-check custom-radio">
                        <input class="form-check-input filter-input" type="radio" name="stars" value="" id="rating1" {{
                            request('stars')=='' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="rating1">{{ translate('Show All')
                            }}</label>
                    </div>
                    @foreach(range(5, 1) as $star)
                    <div class="form-check custom-radio d-flex align-items-center gap-2">
                        <input class="form-check-input filter-input mt-0" type="radio" name="stars" value="{{ $star }}"
                            id="rating_{{ $star }}" {{ request('stars')==$star ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer d-flex align-items-center"
                            for="rating_{{ $star }}">
                            <div class="d-flex gap-1 text-warning fs-13">
                                @for ($i = 1; $i <= 5; $i++) <i
                                    class="bi {{ $i <= $star ? 'bi-star-fill' : 'bi-star opacity-50 text-gray-700' }}">
                                    </i>
                                    @endfor
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Date Added --}}
            <div class="filter-section">
                <h6
                    class="filter-title fw-semibold text-uppercase text-gray-700 fs-13 border-start border-primary border-3 mb-3 ps-2">
                    {{ translate('Publication Date') }}
                </h6>
                <div class="filter-list d-flex flex-column gap-2">
                    <div class="form-check custom-radio">
                        <input class="form-check-input filter-input" type="radio" name="date" value="" id="date0" {{
                            request('date')=='' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="date0">{{ translate('Anytime')
                            }}</label>
                    </div>
                    <div class="form-check custom-radio">
                        <input class="form-check-input filter-input" type="radio" name="date" value="this_week"
                            id="date1" {{ request('date')=='this_week' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="date1">{{ translate('This week')
                            }}</label>
                    </div>
                    <div class="form-check custom-radio">
                        <input class="form-check-input filter-input" type="radio" name="date" value="this_month"
                            id="date2" {{ request('date')=='this_month' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="date2">{{ translate('This month')
                            }}</label>
                    </div>
                    <div class="form-check custom-radio">
                        <input class="form-check-input filter-input" type="radio" name="date" value="last_month"
                            id="date3" {{ request('date')=='last_month' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="date3">{{ translate('Last month')
                            }}</label>
                    </div>
                    <div class="form-check custom-radio">
                        <input class="form-check-input filter-input" type="radio" name="date" value="this_year"
                            id="date4" {{ request('date')=='this_year' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="date4">{{ translate('This year')
                            }}</label>
                    </div>
                    <div class="form-check custom-radio">
                        <input class="form-check-input filter-input" type="radio" name="date" value="last_year"
                            id="date5" {{ request('date')=='last_year' ? 'checked' : '' }}>
                        <label class="form-check-label fs-15 cursor-pointer" for="date5">{{ translate('Last year')
                            }}</label>
                    </div>
                </div>
            </div>

            {{-- Dynamic Category Options --}}
            @if (isset($category) && !empty($category->options))
            @foreach ($category->options as $categoryOption)
            @php
                $optName = $categoryOption['name'];
                $isMultiple = $categoryOption['type'] == \App\Models\Product\ProductCategory::MULTIPLE_SELECT;
                $slug = strtolower(Str::slug($optName, '_'));
            @endphp
            <div class="filter-section">
                <h6
                    class="filter-title fw-semibold text-uppercase text-gray-700 fs-13 border-start border-primary border-3 mb-3 ps-2">
                    {{ $optName }}
                </h6>
                <div class="filter-list d-flex flex-column gap-2">
                    @foreach ($categoryOption['options'] ?? [] as $key => $value)
                    @php $optValue = strtolower(Str::slug($value)); @endphp
                    <div class="form-check custom-check">
                        <input class="form-check-input filter-input"
                            type="{{ $isMultiple ? 'checkbox' : 'radio' }}"
                            name="{{ $slug }}{{ $isMultiple ? '[]' : '' }}" value="{{ $optValue }}"
                            id="{{ $slug . $key }}" {{ $isMultiple ? 'data-multiple=true' : '' }}
                        @if($isMultiple)
                        {{ is_array(request($slug)) && in_array($optValue, request($slug)) ? 'checked' : '' }}
                        @else
                        {{ !is_array(request($slug)) && request($slug) == $optValue ? 'checked' : '' }}
                        @endif
                        >
                        <label class="form-check-label fs-15 cursor-pointer" for="{{ $slug . $key }}">{{ $value
                            }}</label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            @endif

        </div>

        <x-slot:footer>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="btnResetFilters" class="btn btn-outline-dark rounded-pill flex-fill">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    {{ translate('Reset') }}
                </button>
                <button type="button" id="btnApplyFilters" class="btn btn-primary rounded-pill flex-fill">
                    <i class="bi bi-check2-circle me-1"></i>
                    {{ translate('Apply Filter') }}
                </button>
            </div>
        </x-slot:footer>
    </x-offcanvas>
</div>
