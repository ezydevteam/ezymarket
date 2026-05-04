@php
$excludedBlocks = ['home_hero', 'home_newsletter', 'home_offer_banner', 'home_divider', 'home_widget'];
$hideTitleArea = in_array($homeBlock->id, $excludedBlocks);
@endphp

@if(!$hideTitleArea)
<hr class="my-3">
<h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Title Area') }}</h6>

{{-- Title Style --}}
<div class="mb-3">
    <label class="form-label">{{ translate('Title Style') }}</label>
    <select name="title_style" class="form-select selectpicker">
        <option value="none" @selected(($options['title_style'] ?? '' )=='none' )>{{ translate('Hide Title') }}</option>
        <option value="minimal" @selected(($options['title_style'] ?? 'minimal' )=='minimal' )>{{ translate('Minimal')
            }}
        </option>
        <option value="accent" @selected(($options['title_style'] ?? '' )=='accent' )>{{ translate('Accent Line') }}
        </option>
        <option value="badge" @selected(($options['title_style'] ?? '' )=='badge' )>{{ translate('Badge') }}</option>
        <option value="gradient" @selected(($options['title_style'] ?? '' )=='gradient' )>{{ translate('Gradient') }}
        </option>
        <option value="underline" @selected(($options['title_style'] ?? '' )=='underline' )>{{ translate('Underline') }}
        </option>
        <option value="parallelogram" @selected(($options['title_style'] ?? '' )=='parallelogram' )>{{
            translate('Parallelogram') }}</option>
        <option value="square" @selected(($options['title_style'] ?? '' )=='square' )>{{ translate('Square') }}</option>
    </select>
</div>

<div class="row">
    {{-- Show Subtitle Toggle --}}
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="hidden" name="show_subtitle" value="0">
            <input class="form-check-input" type="checkbox" id="show_subtitle" name="show_subtitle" value="1"
                @checked(($options['show_subtitle'] ?? '1' )=='1' )>
            <label class="form-check-label" for="show_subtitle">{{ translate('Show Subtitle') }}</label>
        </div>
    </div>

    {{-- Show Bottom Border Toggle --}}
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="hidden" name="show_bottom_border" value="0">
            <input class="form-check-input" type="checkbox" id="show_bottom_border" name="show_bottom_border" value="1"
                @checked(!empty($options['show_bottom_border']))>
            <label class="form-check-label" for="show_bottom_border">{{ translate('Bottom Border') }}</label>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Title Alignment --}}
    <div class="col-6">
        <label class="form-label">{{ translate('Title Alignment') }}</label>
        <select name="title_alignment" class="form-select selectpicker">
            <option value="" @selected(($options['title_alignment'] ?? '' )=='' )>{{ translate('Left') }}</option>
            <option value="center" @selected(($options['title_alignment'] ?? '' )=='center' )>{{ translate('Center') }}
            </option>
            <option value="end" @selected(($options['title_alignment'] ?? '' )=='end' )>{{ translate('Right') }}
            </option>
        </select>
    </div>

    {{-- Title Icon --}}
    <div class="col-6">
        <label class="form-label">{{ translate('Title Icon') }}</label>
        <select name="title_icon" class="form-select selectpicker" data-live-search="true">
            <option value="">{{ translate('No Icon') }}</option>
            @foreach($bootstrapIcons as $iconClass => $iconLabel)
            <option value="{{ $iconClass }}" data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                @selected(($options['title_icon'] ?? '' )==$iconClass)>
                {{ $iconLabel }}
            </option>
            @endforeach
        </select>
    </div>
    {{-- Title Color --}}
    <div class="col-6">
        <label class="form-label">{{ translate('Title Color') }}</label>
        <input type="text" name="title_color" class="form-control coloris" value="{{ $options['title_color'] ?? '' }}"
            placeholder="{{ translate('Default') }}">
    </div>

    {{-- Font Size --}}
    <div class="col-6">
        <label class="form-label">{{ translate('Font Size') }}</label>
        <select name="title_font_size" class="form-select selectpicker">
            <option value="fs-3" @selected(($options['title_font_size'] ?? '' )=='fs-3' )>{{ translate('Extra Large') }}
            </option>
            <option value="fs-4" @selected(($options['title_font_size'] ?? '' )=='fs-4' )>{{ translate('Large') }}
            </option>
            <option value="fs-5" @selected(($options['title_font_size'] ?? '' )=='fs-5' )>{{ translate('Medium') }}
            </option>
            <option value="fs-6" @selected(($options['title_font_size'] ?? 'fs-6' )=='fs-6' )>{{ translate('Normal') }}
            </option>
            <option value="fs-12" @selected(($options['title_font_size'] ?? '' )=='fs-12' )>{{ translate('Small') }}
            </option>
            <option value="fs-10" @selected(($options['title_font_size'] ?? '' )=='fs-10' )>{{ translate('Extra Small')
                }}</option>
        </select>
    </div>

    {{-- Font Weight --}}
    <div class="col-6">
        <label class="form-label">{{ translate('Font Weight') }}</label>
        <select name="title_font_weight" class="form-select selectpicker">
            <option value="" @selected(($options['title_font_weight'] ?? '' )=='' )>{{ translate('Default') }}</option>
            <option value="fw-bolder" @selected(($options['title_font_weight'] ?? '' )=='fw-bolder' )>{{
                translate('Bolder') }}</option>
            <option value="fw-bold" @selected(($options['title_font_weight'] ?? '' )=='fw-bold' )>{{ translate('Bold')
                }}</option>
            <option value="fw-semibold" @selected(($options['title_font_weight'] ?? '' )=='fw-semibold' )>{{
                translate('Semi Bold') }}</option>
            <option value="fw-normal" @selected(($options['title_font_weight'] ?? '' )=='fw-normal' )>{{
                translate('Normal') }}</option>
            <option value="fw-light" @selected(($options['title_font_weight'] ?? '' )=='fw-light' )>{{
                translate('Light') }}</option>
        </select>
    </div>

    {{-- Text Transform --}}
    <div class="col-6">
        <label class="form-label">{{ translate('Text Transform') }}</label>
        <select name="title_transform" class="form-select selectpicker">
            <option value="" @selected(($options['title_transform'] ?? '' )=='' )>{{ translate('None') }}</option>
            <option value="text-uppercase" @selected(($options['title_transform'] ?? '' )=='text-uppercase' )>{{
                translate('UPPERCASE') }}</option>
            <option value="text-lowercase" @selected(($options['title_transform'] ?? '' )=='text-lowercase' )>{{
                translate('lowercase') }}</option>
            <option value="text-capitalize" @selected(($options['title_transform'] ?? '' )=='text-capitalize' )>{{
                translate('Capitalize') }}</option>
        </select>
    </div>
</div>

@if($homeBlock->id == 'home_products' || $homeBlock->id == 'home_blog_articles')
{{-- Show View More Button --}}
<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="show_view_more" value="0">
        <input class="form-check-input" type="checkbox" id="show_view_more" name="show_view_more" value="1"
            @checked(!empty($options['show_view_more'])) data-slide-toggle="#viewMoreOptions">
        <label class="form-check-label" for="show_view_more">{{ translate('Show View More Button') }}</label>
    </div>
</div>

<div class=d-none id="viewMoreOptions">
    <div class="row g-3 mb-3">
        <div class="col-6">
            <label class="form-label">{{ translate('View More Style') }}</label>
            <select name="view_more_style" class="form-select selectpicker">
                <option value="text" @selected(($options['view_more_style'] ?? '' )=='text' )>{{ translate('Text
                    Only') }}</option>
                <option value="icon_only" @selected(($options['view_more_style'] ?? '' )=='icon_only' )>{{
                    translate('Icon Only') }}</option>
                <option value="pill" @selected(($options['view_more_style'] ?? 'pill' )=='pill' )>{{ translate('Rounded
                    Pill') }}</option>
                <option value="rounded" @selected(($options['view_more_style'] ?? '' )=='rounded' )>{{
                    translate('Rounded Corner') }}</option>
            </select>
        </div>

        <div class="col-6">
            <label class="form-label">{{ translate('View More Icon') }}</label>
            <select name="view_more_icon" class="form-select selectpicker">
                <option value="" @selected(($options['view_more_icon'] ?? '' )=='' )>{{ translate('No Icon') }}</option>
                <option value="bi-chevron-right" @selected(($options['view_more_icon'] ?? 'bi-chevron-right'
                    )=='bi-chevron-right' )>{{ translate('Chevron') }}</option>
                <option value="bi-chevron-double-right" @selected(($options['view_more_icon']
                    ?? 'bi-chevron-double-right' )=='bi-chevron-double-right' )>{{ translate('Chevron Double') }}
                </option>
                <option value="bi-arrow-right" @selected(($options['view_more_icon'] ?? 'bi-arrow-right'
                    )=='bi-arrow-right' )>{{ translate('Arrow Right') }}</option>
                <option value="bi-box-arrow-up-right" @selected(($options['view_more_icon'] ?? 'bi-box-arrow-up-right'
                    )=='bi-box-arrow-up-right' )>{{ translate('Box Arrow Right') }}</option>
                <option value="bi-send" @selected(($options['view_more_icon'] ?? 'bi-send' )=='bi-send' )>{{
                    translate('Send') }}</option>
            </select>
        </div>

        <div class="col-6">
            <label class="form-label">{{ translate('View More Text') }}</label>
            <input type="text" name="view_more_text" class="form-control" value="{{ $options['view_more_text'] ?? '' }}"
                placeholder="{{ translate('View All') }}">
        </div>

        <div class="col-6">
            <label class="form-label">{{ translate('View More URL') }}
                <small class="form-text text-muted">({{ translate('optional') }})</small>
            </label>
            <input type="text" name="view_more_url" class="form-control" value="{{ $options['view_more_url'] ?? '' }}"
                placeholder="https://...">
        </div>
    </div>
</div>
@endif

{{-- Show Category Dropdown Toggle --}}
@if($homeBlock->id == 'home_products')
<div class="mb-3">
    <div class="form-check form-switch">
        <input type="hidden" name="show_category_dropdown" value="0">
        <input class="form-check-input" type="checkbox" id="show_category_dropdown" name="show_category_dropdown"
            value="1" @checked(!empty($options['show_category_dropdown']))
            data-slide-toggle="#categoryDropdownOptions">
        <label class="form-check-label" for="show_category_dropdown">{{ translate('Show Category Dropdown')
            }}</label>
    </div>
</div>

<div class="d-none" id="categoryDropdownOptions">
    <div class="row g-3 mb-3">
        <div class="col-6">
            <label class="form-label">{{ translate('Dropdown Style') }}</label>
            <select name="category_dropdown_style" class="form-select selectpicker">
                <option value="text" @selected(($options['category_dropdown_style'] ?? '' )=='text' )>{{
                    translate('Text Only') }}</option>
                <option value="icon_only" @selected(($options['category_dropdown_style'] ?? '' )=='icon_only' )>{{
                    translate('Icon Only') }}</option>
                <option value="pill" @selected(($options['category_dropdown_style'] ?? 'pill' )=='pill' )>{{
                    translate('Rounded Pill') }}</option>
                <option value="rounded" @selected(($options['category_dropdown_style'] ?? '' )=='rounded' )>{{
                    translate('Rounded Corner') }}</option>
            </select>
        </div>

        @php
            $icons = [
                '' => 'No Icon', 'bi-grid' => 'Grid', 'bi-grid-fill' => 'Grid Fill',
                'bi-grid-3x3-gap-fill' => 'Grid 3x3 Gap Fill', 'bi-grid-3x3-gap' => 'Grid 3x3 Gap',
                'bi-list' => 'List', 'bi-list-ul' => 'List Ul', 'bi-card-list' => 'Card List',
                'bi-funnel' => 'Filter', 'bi-chevron-down' => 'Chevron Down',
            ];
            $selectedIcon = $options['category_dropdown_icon'] ?? '';
        @endphp

        <div class="col-6">
            <label class="form-label">{{ translate('Dropdown Icon') }}</label>
            <select name="category_dropdown_icon" class="form-select selectpicker">
                @foreach($icons as $value => $label)
                    <option value="{{ $value }}" @selected($selectedIcon === $value)
                        data-content="<i class='bi {{ $value }} me-1'></i> {{ $label }}">
                        {{ translate($label) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">{{ translate('Dropdown Label') }}</label>
            <input type="text" name="category_dropdown_label" class="form-control"
                value="{{ $options['category_dropdown_label'] ?? '' }}" placeholder="{{ translate('All Categories') }}">
        </div>
    </div>
</div>
@endif
@endif
