@php
$options = $homeBlock->options ?? [];
$isContentBlock = in_array($homeBlock->id, ['home_categories', 'home_faqs', 'home_testimonials', 'home_button',
'home_social_icons', 'home_tabs', 'home_slider']);
$contentItems = $options['content'] ?? [];
$productBlocks = ['home_products', 'home_product_tabs'];
$isProductBlock = in_array($homeBlock->id, $productBlocks);
$isActive = isset($options['is_active']) ? $options['is_active'] == 1 : ($homeBlock->is_active ?? true);
@endphp

<form id="editHomeBlockForm" action="{{ route('admin.builders.home.update', $homeBlock->id) }}" method="POST"
    enctype="multipart/form-data">
    @csrf

    @if($isContentBlock)
    <ul class="nav nav-tabs ezydev-tabs nav-fill mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#general" role="tab">{{ translate('General') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#content" role="tab">{{ translate('Content') }}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            @endif

            <div class="mb-3">
                <label class="form-label">{{ translate('Title') }}</label>
                <input type="text" name="title" class="form-control" value="{{ $homeBlock->title }}" required />
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Subtitle') }}</label>
                <textarea name="subtitle" class="form-control" rows="2">{{ $homeBlock->subtitle }}</textarea>
            </div>

            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Block Options') }}</h6>

            <div class="row g-3 mb-3">
                @if($homeBlock->id === 'home_products')
                @php $selectedProductType = $options['product_type'] ?? 'latest'; @endphp
                <div class="col-12">
                    <label class="form-label">{{ translate('Product Type') }}</label>
                    <select name="product_type" class="form-select selectpicker">
                        <option value="latest" @selected($selectedProductType=='latest' )>{{ translate('Latest') }}
                        </option>
                        <option value="trending" @selected($selectedProductType=='trending' )>{{ translate('Trending')
                            }}</option>
                        <option value="best_selling" @selected($selectedProductType=='best_selling' )>{{ translate('Best
                            Selling') }}</option>
                        <option value="sale" @selected($selectedProductType=='sale' )>{{ translate('Discounted') }}
                        </option>
                        <option value="free" @selected($selectedProductType=='free' )>{{ translate('Free') }}</option>
                        @if(isPremiumAvailable())
                        <option value="premium" @selected($selectedProductType=='premium' )>{{ translate('Premium') }}
                        </option>
                        @endif
                        <option value="featured" @selected($selectedProductType=='featured' )>{{ translate('Featured')
                            }}</option>
                    </select>
                </div>
                @elseif($homeBlock->id == 'home_product_tabs')
                <h6 class="mb-1 small text-muted fw-bold">{{ translate('Show Product Tabs') }}</h6>
                <div class="col-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_latest" id="show_latest" value="1"
                            @checked(!empty($options['show_latest']))>
                        <label class="form-check-label" for="show_latest">{{ translate('Latest') }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_trending" id="show_trending"
                            value="1" @checked(!empty($options['show_trending']))>
                        <label class="form-check-label" for="show_trending">{{ translate('Trending') }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_featured" id="show_featured"
                            value="1" @checked(!empty($options['show_featured']))>
                        <label class="form-check-label" for="show_featured">{{ translate('Featured') }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_best_selling" id="show_best_selling"
                            value="1" @checked(!empty($options['show_best_selling']))>
                        <label class="form-check-label" for="show_best_selling">{{ translate('Best Selling')
                            }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_sale" id="show_sale" value="1"
                            @checked(!empty($options['show_sale']))>
                        <label class="form-check-label" for="show_sale">{{ translate('Discounted') }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_free" id="show_free" value="1"
                            @checked(!empty($options['show_free']))>
                        <label class="form-check-label" for="show_free">{{ translate('Free') }}</label>
                    </div>
                </div>
                @if(isPremiumAvailable())
                <div class="col-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_premium" id="show_premium" value="1"
                            @checked(!empty($options['show_premium']))>
                        <label class="form-check-label" for="show_premium">{{ translate('Premium') }}</label>
                    </div>
                </div>
                @endif
                @endif
            </div>

            @if($homeBlock->id == 'home_product_tabs')
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Tab Style') }}</label>
                    <select name="tab_nav_style" class="form-select selectpicker">
                        <option value="pills" @selected(($options['tab_nav_style'] ?? 'pills' )=='pills' )>{{
                            translate('Pills') }}</option>
                        <option value="underline" @selected(($options['tab_nav_style'] ?? '' )=='underline' )>{{
                            translate('Underline') }}</option>
                        <option value="bordered" @selected(($options['tab_nav_style'] ?? '' )=='bordered' )>{{
                            translate('Bordered') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Tab Alignment') }}</label>
                    <select name="tab_nav_alignment" class="form-select selectpicker">
                        <option value="start" @selected(($options['tab_nav_alignment'] ?? '' )=='start' )>{{
                            translate('Left') }}</option>
                        <option value="center" @selected(($options['tab_nav_alignment'] ?? '' )=='center' )>{{
                            translate('Center') }}</option>
                        <option value="end" @selected(($options['tab_nav_alignment'] ?? 'end' )=='end' )>{{
                            translate('Right') }}</option>
                    </select>
                </div>
            </div>
            @endif

            @if($isProductBlock)
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Display Products') }}</label>
                    <input type="number" name="products_number" class="form-control" min="1" max="100"
                        value="{{ $options['products_number'] ?? 8 }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Title Length') }}</label>
                    <input type="number" name="products_title_length" class="form-control" min="1" max="120"
                        value="{{ $options['products_title_length'] ?? 45 }}">
                </div>
            </div>
            @endif

            {{-- Block Style Options --}}
            @include('admin.builders.home.partials.block-options', [
            'options' => $options,
            'homeBlock' => $homeBlock
            ])

            @if($isProductBlock)
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Body Style') }}</label>
                    <select name="product_body_style" class="form-select selectpicker">
                        <option value="none" @selected(($options['product_body_style'] ?? '' )=='none' )>{{
                            translate('None') }}</option>
                        <option value="outline" @selected(($options['product_body_style'] ?? '' )=='outline' )>{{
                            translate('Outline') }}</option>
                        <option value="shadow" @selected(($options['product_body_style'] ?? 'shadow' )=='shadow' )>{{
                            translate('Shadow') }}</option>
                        <option value="bg_light" @selected(($options['product_body_style'] ?? '' )=='bg_light' )>{{
                            translate('Bg Light Gray') }}</option>
                        <option value="bg_green" @selected(($options['product_body_style'] ?? '' )=='bg_green' )>{{
                            translate('Bg Light Green') }}</option>
                        <option value="bg_purple" @selected(($options['product_body_style'] ?? '' )=='bg_green' )>{{
                            translate('Bg Light Purple') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Meta Style') }}</label>
                    <select name="product_meta_style" class="form-select selectpicker">
                        <option value="none" @selected(($options['product_meta_style'] ?? '' )=='none' )>{{
                            translate('Hide
                            Meta') }}</option>
                        <option value="default" @selected(($options['product_meta_style'] ?? 'default' )=='default' )>{{
                            translate('Default') }}</option>
                        <option value="minimal" @selected(($options['product_meta_style'] ?? '' )=='minimal' )>{{
                            translate('Minimal') }}</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">{{ translate('Actions Button Style') }}</label>
                    <select name="action_button_style" class="form-select selectpicker">
                        <option value="default" @selected(($options['action_button_style'] ?? 'default' )=='default' )>
                            {{ translate('Default') }}</option>
                        <option value="style_1" @selected(($options['action_button_style'] ?? '' )=='style_1' )>{{
                            translate('Style 1 (Text + Icon)') }}</option>
                        <option value="style_2" @selected(($options['action_button_style'] ?? '' )=='style_2' )>{{
                            translate('Style 2 (Icon + Text)') }}</option>
                        <option value="style_3" @selected(($options['action_button_style'] ?? '' )=='style_3' )>{{
                            translate('Style 3 (Small Icons)') }}</option>
                    </select>
                </div>

                @php
                $buttonStyles = [
                'primary' => 'Primary','secondary' => 'Secondary','success' => 'Success',
                'danger' => 'Danger','warning' => 'Warning','info' => 'Info',
                'light' => 'Light','dark' => 'Dark','outline-primary' => 'Outline Primary',
                'outline-secondary' => 'Outline Secondary','outline-success' => 'Outline Success',
                'outline-light' => 'Outline Light','outline-dark' => 'Outline Dark',
                ];
                @endphp
                <div class="col-6">
                    <label class="form-label">{{ translate('Preview Button Style') }}</label>
                    <select name="preview_button_style" class="form-select selectpicker" data-live-search="true">
                        @php
                        $selectedStyle = $options['preview_button_style'] ?? 'primary';
                        @endphp

                        @foreach($buttonStyles as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStyle===$value)>
                            {{ translate($label) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Cart/Download Button') }}</label>
                    <select name="cart_button_style" class="form-select selectpicker" data-live-search="true">
                        @php
                        $selectedStyle = $options['cart_button_style'] ?? 'outline-primary';
                        @endphp

                        @foreach($buttonStyles as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStyle===$value)>
                            {{ translate($label) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">{{ translate('Pagination Style') }}</label>
                    <select name="pagination_style" class="form-select selectpicker"
                        data-conditional-toggle="#pagiBtnStyle" data-conditional-value="none"
                        data-conditional-logic="not-equal">
                        <option value="none" @selected(($options['pagination_style'] ?? '' )=='none' )>{{
                            translate('Hide Pagination') }}</option>
                        <option value="numeric" @selected(($options['pagination_style'] ?? '' )=='numeric' )>{{
                            translate('Numeric') }}</option>
                        <option value="load_more" @selected(($options['pagination_style'] ?? 'load_more' )=='load_more'
                            )>{{
                            translate('Load More') }}</option>
                        <option value="view_more" @selected(($options['pagination_style'] ?? '' )=='view_more' )>{{
                            translate('View More') }}</option>
                    </select>
                </div>
            </div>
            <div class="row g-3 mb-3" id="pagiBtnStyle">
                <div class="col-6">
                    <label class="form-label">{{ translate('Pagination Button Style') }}</label>
                    <select name="pagi_btn_style" class="form-select selectpicker" data-live-search="true">
                        @php
                        $selectedStyle = $options['pagi_btn_style'] ?? 'outline-primary';
                        @endphp

                        @foreach($buttonStyles as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStyle===$value)>
                            {{ translate($label) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Icon') }}</label>
                    <select name="pagi_btn_icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                        <option value="{{ $iconClass }}"
                            data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                            @selected(($options['pagi_btn_icon'] ?? '' )==$iconClass)>
                            {{ $iconLabel }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            @if($homeBlock->id == 'home_faqs')
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('FAQ Toggle Icon') }}</label>
                    <select name="faq_icon" class="form-select selectpicker">
                        <option value="plus_minus" @selected(($options['faq_icon'] ?? 'plus_minus' )=='plus_minus' )>{{
                            translate('Plus / Minus') }}</option>
                        <option value="chevron" @selected(($options['faq_icon'] ?? '' )=='chevron' )>{{
                            translate('Chevron Down') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Position') }}</label>
                    <select name="faq_icon_position" class="form-select selectpicker">
                        <option value="left" @selected(($options['faq_icon_position'] ?? 'left' )=='left' )>{{
                            translate('Left') }}</option>
                        <option value="right" @selected(($options['faq_icon_position'] ?? '' )=='right' )>{{
                            translate('Right') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Toggle Button Style') }}</label>
                    <select name="faq_btn_style" class="form-select selectpicker">
                        <option value="icon_only" @selected(($options['faq_btn_style'] ?? 'icon_only' )=='icon_only' )>
                            {{ translate('Icon Only') }}</option>
                        <option value="bg_rounded" @selected(($options['faq_btn_style'] ?? '' )=='bg_rounded' )>{{
                            translate('Rounded Corner') }}</option>
                        <option value="circle" @selected(($options['faq_btn_style'] ?? '' )=='circle' )>{{
                            translate('Rounded Circle')
                            }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('FAQ Item Style') }}</label>
                    <select name="faq_item_style" class="form-select selectpicker">
                        <option value="default" @selected(($options['faq_item_style'] ?? 'default' )=='default' )>{{
                            translate('Default') }}</option>
                        <option value="rounded" @selected(($options['faq_item_style'] ?? '' )=='rounded' )>{{
                            translate('Rounded Corner') }}</option>
                        <option value="pill" @selected(($options['faq_item_style'] ?? '' )=='pill' )>{{
                            translate('Rounded Pill') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Title Font Size (px)') }}</label>
                    <input type="number" name="faq_title_size" class="form-control" min="10" max="32"
                        value="{{ $options['faq_title_size'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Text Transform') }}</label>
                    <select name="faq_title_transform" class="form-select selectpicker">
                        <option value="default" @selected(($options['faq_title_transform'] ?? 'default' )=='default' )>
                            {{
                            translate('Default') }}</option>
                        <option value="uppercase" @selected(($options['faq_title_transform'] ?? '' )=='uppercase' )>{{
                            translate('Uppercase') }}</option>
                        <option value="lowercase" @selected(($options['faq_title_transform'] ?? '' )=='lowercase' )>{{
                            translate('Lowercase') }}</option>
                        <option value="capitalize" @selected(($options['faq_title_transform'] ?? '' )=='capitalize' )>{{
                            translate('Capitalize') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Item Background') }}</label>
                    <input type="text" name="faq_item_bg" class="form-control coloris"
                        value="{{ $options['faq_item_bg'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Item Text Color') }}</label>
                    <input type="text" name="faq_item_color" class="form-control coloris"
                        value="{{ $options['faq_item_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="faq_collapse_first"
                            id="faq_collapse_first" value="1" @checked(!empty($options['faq_collapse_first']))>
                        <label class="form-check-label" for="faq_collapse_first">{{ translate('Expand First Item')
                            }}</label>
                    </div>
                </div>
            </div>
            @endif

            @if($homeBlock->id == 'home_testimonials')
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="testimonial_disable_bg"
                            id="testimonial_disable_bg" value="1" @checked(!empty($options['testimonial_disable_bg']))>
                        <label class="form-check-label" for="testimonial_disable_bg">{{ translate('Disable Background')
                            }}</label>
                    </div>
                </div>
                <div class="col-12" id="testimonialAutoplay">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="testimonial_disable_autoplay"
                            id="testimonial_disable_autoplay" value="1"
                            @checked(!empty($options['testimonial_disable_autoplay']))>
                        <label class="form-check-label" for="testimonial_disable_autoplay">{{ translate('Disable
                            Autoplay') }}</label>
                    </div>
                </div>
            </div>
            @endif

            @if($homeBlock->id == 'home_countdown')
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Countdown To Date') }}</label>
                    <input type="datetime-local" name="countdown_date" class="form-control"
                        value="{{ $options['countdown_date'] ?? '' }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Background Color') }}</label>
                    <input type="text" name="bg_color" class="form-control coloris"
                        value="{{ $options['bg_color'] ?? '#0d6efd' }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Text Color') }}</label>
                    <input type="text" name="text_color" class="form-control coloris"
                        value="{{ $options['text_color'] ?? '#ffffff' }}">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="use_bg_image" value="0">
                        <input type="checkbox" name="use_bg_image" class="form-check-input" value="1" id="useBhImage"
                            @checked($options['use_bg_image'] ?? true) data-slide-toggle="#cd_bg_image">
                        <label class="form-check-label" for="useBhImage">{{ translate('Use Background Image') }}</label>
                    </div>
                </div>
                <div class="col-12" id="cd_bg_image">
                    <label class="form-label">{{ translate('Image') }}</label>
                    <div class="input-group">
                        <span class="input-group-text p-1 bg-white">
                            <img id="preview-offer-img"
                                src="{{ !empty($options['bg_image']) ? asset($options['bg_image']) : asset('images/placeholders/default.png') }}"
                                width="45" height="30" style="object-fit: contain;">
                        </span>
                        <input type="text" class="form-control" readonly style="cursor: pointer;"
                            onclick="$('#file-offer-img').click()"
                            value="{{ !empty($options['bg_image']) ? basename($options['bg_image']) : translate('Choose Image') }}">
                        <button type="button" class="btn bg-text-primary" onclick="$('#file-offer-img').click()">{{
                            translate('Upload') }}</button>
                    </div>
                    <input id="file-offer-img" type="file" name="bg_image" class="d-none repeater-image-input"
                        data-preview="#preview-offer-img" accept="image/*">
                    @if(!empty($options['bg_image']))
                    <input type="hidden" name="old_bg_image" value="{{ $options['bg_image'] }}">
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Display Units') }}</label>
                    <div class="row row-cols-2 g-3 px-2">
                        <div class="form-check form-switch">
                            <input type="hidden" name="show_days" value="0">
                            <input type="checkbox" name="show_days" class="form-check-input" value="1" id="showDays"
                                @checked($options['show_days'] ?? true)>
                            <label class="form-check-label" for="showDays">{{ translate('Days') }}</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="show_hours" value="0">
                            <input type="checkbox" name="show_hours" class="form-check-input" value="1" id="showHours"
                                @checked($options['show_hours'] ?? true)>
                            <label class="form-check-label" for="showHours">{{ translate('Hours') }}</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="show_minutes" value="0">
                            <input type="checkbox" name="show_minutes" class="form-check-input" value="1"
                                id="showMinutes" @checked($options['show_minutes'] ?? true)>
                            <label class="form-check-label" for="showMinutes">{{ translate('Minutes') }}</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="show_seconds" value="0">
                            <input type="checkbox" name="show_seconds" class="form-check-input" value="1"
                                id="showSeconds" @checked($options['show_seconds'] ?? true)>
                            <label class="form-check-label" for="showSeconds">{{ translate('Seconds') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Text') }}</label>
                    <input type="text" name="btn_text" class="form-control" value="{{ $options['btn_text'] ?? '' }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button URL') }}</label>
                    <input type="text" name="btn_url" class="form-control" value="{{ $options['btn_url'] ?? '#' }}">
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="btn_style" class="form-select selectpicker" data-live-search="true">
                        @foreach(['no-button', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light',
                        'dark',
                        'link', 'outline-primary', 'outline-secondary', 'outline-success', 'outline-danger',
                        'outline-warning', 'outline-info', 'outline-light', 'outline-dark'] as $btnStyle)
                        <option value="btn-{{ $btnStyle }}" @selected(($options['btn_style'] ?? 'no-button' )=='btn-'
                            .$btnStyle)>{{ translate(ucfirst(str_replace('-', ' ', $btnStyle))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Icon') }}</label>
                    <select name="btn_icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                        <option value="{{ $iconClass }}"
                            data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                            @selected(($options['btn_icon'] ?? '' )==$iconClass)>
                            {{ $iconLabel }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @elseif($homeBlock->id == 'home_image')
            <div class="mb-3">
                <label class="form-label">{{ translate('Image') }}</label>
                <div class="input-group">
                    <span class="input-group-text p-1 bg-white">
                        <img id="preview-single-img"
                            src="{{ !empty($options['image']) ? asset($options['image']) : asset('images/placeholders/default.png') }}"
                            width="60" height="40" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control" readonly style="cursor: pointer;"
                        onclick="$('#file-single-img').click()"
                        value="{{ !empty($options['image']) ? basename($options['image']) : translate('Choose Image') }}">
                    <button type="button" class="btn bg-text-primary" onclick="$('#file-single-img').click()">{{
                        translate('Upload') }}</button>
                </div>
                <input id="file-single-img" type="file" name="image" class="d-none repeater-image-input"
                    data-preview="#preview-single-img" accept="image/*">
                @if(!empty($options['image']))
                <input type="hidden" name="old_image" value="{{ $options['image'] }}">
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Link URL') }}</label>
                <input type="text" name="link" class="form-control" value="{{ $options['link'] ?? '' }}"
                    placeholder="https://...">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Image Size') }}</label>
                    <select name="image_size" class="form-select selectpicker">
                        <option value="w-100" @selected(($options['image_size'] ?? 'w-100' )=='w-100' )>{{
                            translate('Full Width') }}</option>
                        <option value="w-75" @selected(($options['image_size'] ?? '' )=='w-75' )>{{ translate('Large')
                            }}</option>
                        <option value="w-50" @selected(($options['image_size'] ?? '' )=='w-50' )>{{ translate('Medium')
                            }}</option>
                        <option value="w-25" @selected(($options['image_size'] ?? '' )=='w-25' )>{{ translate('Small')
                            }}</option>
                        <option value="auto" @selected(($options['image_size'] ?? '' )=='auto' )>{{ translate('Auto') }}
                        </option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Corner Style') }}</label>
                    <select name="image_corner" class="form-select selectpicker">
                        <option value="rounded-0" @selected(($options['image_corner'] ?? '' )=='rounded-0' )>{{
                            translate('Default') }}</option>
                        <option value="rounded-2" @selected(($options['image_corner'] ?? 'rounded' )=='rounded-2' )>{{
                            translate('Rounded 2') }}</option>
                        <option value="rounded-3" @selected(($options['image_corner'] ?? '' )=='rounded-3' )>
                            {{translate('Rounded 3') }}</option>
                        <option value="rounded-4" @selected(($options['image_corner'] ?? '' )=='rounded-4' )>{{
                            translate('Rounded 4') }}</option>
                    </select>
                </div>
            </div>
            <div class="alert alert-info small p-2 mb-3">
                {{ translate('The title options will be effective only if the style is Default.') }}
            </div>

            @elseif($homeBlock->id == 'home_rich_text')
            <div class="mb-3">
                <label class="form-label">{{ translate('Content') }}</label>
                <textarea name="rich_text" id="richTextEditor"
                    class="form-control">{{ $options['rich_text'] ?? '' }}</textarea>
            </div>

            @elseif($homeBlock->id == 'home_html')
            <div class="mb-3">
                <label class="form-label">{{ translate('HTML Code') }}</label>
                <textarea name="custom_html" class="form-control" rows="12"
                    style="font-family: monospace;">{{ $options['custom_html'] ?? '' }}</textarea>
            </div>

            @elseif($homeBlock->id == 'home_widget')
            <div class="alert alert-info">
                {{ translate('The content of this widget is managed in Appearance -> Widgets > Home Sidebar.') }}
            </div>

            @elseif($homeBlock->id == 'home_advertisement')
            <div class="mb-3">
                <label class="form-label">{{ translate('Select Ad Slot') }}</label>
                <select name="ad_alias" class="form-select selectpicker" placeholder="{{ translate('Select an Ad') }}">
                    @forelse($advertisements as $ad)
                    <option value="{{ $ad->alias }}" @selected(($options['ad_alias'] ?? '' )==$ad->alias)>
                        {{ $ad->position }}
                    </option>
                    @empty
                    <option value="">{{ translate('No active home ads found') }}</option>
                    @endforelse
                </select>
                <small class="form-text text-muted">
                    {{ translate('Manage ads in') }} <a href="{{ route('admin.ads.index') }}" target="_blank">{{
                        translate('Advertisements') }}</a>
                </small>
            </div>

            @elseif($homeBlock->id == 'home_login_form')
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Form Width') }}</label>
                    <select name="form_width" class="form-select selectpicker">
                        <option value="col-12" @selected(($options['form_width'] ?? 'col-12' )=='col-12' )>{{
                            translate('Full Width') }}</option>
                        <option value="col-md-8" @selected(($options['form_width'] ?? '' )=='col-md-8' )>{{
                            translate('Large') }}</option>
                        <option value="col-md-6" @selected(($options['form_width'] ?? '' )=='col-md-6' )>{{
                            translate('Medium') }}</option>
                        <option value="col-md-4" @selected(($options['form_width'] ?? '' )=='col-md-4' )>{{
                            translate('Small') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Form Shadow') }}</label>
                    <select name="form_shadow" class="form-select selectpicker">
                        <option value="shadow-none" @selected(($options['form_shadow'] ?? '' )=='shadow-none' )>{{
                            translate('None') }}</option>
                        <option value="shadow-sm" @selected(($options['form_shadow'] ?? 'shadow-sm' )=='shadow-sm' )>{{
                            translate('Small') }}</option>
                        <option value="shadow" @selected(($options['form_shadow'] ?? '' )=='shadow' )>{{
                            translate('Medium') }}</option>
                        <option value="shadow-lg" @selected(($options['form_shadow'] ?? '' )=='shadow-lg' )>{{
                            translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Background Color') }}</label>
                    <input type="text" name="bg_color" class="form-control coloris"
                        value="{{ $options['bg_color'] ?? '#ffffff' }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Text Color') }}</label>
                    <input type="text" name="text_color" class="form-control coloris"
                        value="{{ $options['text_color'] ?? '#333333' }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="lf_btn_style" class="form-select selectpicker" data-live-search="true">
                        @foreach(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light',
                        'dark','link', 'outline-primary', 'outline-secondary', 'outline-success', 'outline-danger',
                        'outline-warning', 'outline-info', 'outline-light', 'outline-dark'] as $btnStyle)
                        <option value="btn-{{ $btnStyle }}" @selected(($options['lf_btn_style'] ?? 'primary' )=='btn-'
                            .$btnStyle)>{{ translate(ucfirst(str_replace('-', ' ', $btnStyle))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Icon') }}</label>
                    <select name="lf_btn_icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                        <option value="{{ $iconClass }}"
                            data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                            @selected(($options['lf_btn_icon'] ?? '' )==$iconClass)>
                            {{ $iconLabel }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @elseif($homeBlock->id == 'home_button')
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Text') }}</label>
                <input type="text" name="btn_text" class="form-control" value="{{ $options['btn_text'] ?? '' }}"
                    placeholder="{{ translate('Enter button text') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Link') }}</label>
                <input type="text" name="btn_link" class="form-control" value="{{ $options['btn_link'] ?? '#' }}"
                    placeholder="https://example.com">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Link Target') }}</label>
                <select name="btn_target" class="form-select selectpicker">
                    <option value="_self" @selected(($options['btn_target'] ?? '_self' )=='_self' )>{{
                        translate('Same Tab') }}</option>
                    <option value="_blank" @selected(($options['btn_target'] ?? '' )=='_blank' )>{{ translate('New
                        Tab') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Style') }}</label>
                <select name="btn_style" class="form-select selectpicker" data-live-search="true">
                    @foreach(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light',
                    'dark','link', 'outline-primary', 'outline-secondary', 'outline-success', 'outline-danger',
                    'outline-warning', 'outline-info', 'outline-light', 'outline-dark'] as $btnStyle)
                    <option value="btn-{{ $btnStyle }}" @selected(($options['btn_style'] ?? 'primary' )=='btn-'
                        .$btnStyle)>{{ translate(ucfirst(str_replace('-', ' ', $btnStyle))) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Size') }}</label>
                <select name="btn_size" class="form-select selectpicker">
                    <option value="btn-sm" @selected(($options['btn_size'] ?? '' )=='btn-sm' )>{{ translate('Small')
                        }}</option>
                    <option value="" @selected(($options['btn_size'] ?? '' )=='' )>{{ translate('Medium') }}
                    </option>
                    <option value="btn-lg" @selected(($options['btn_size'] ?? 'btn-lg' )=='btn-lg' )>{{
                        translate('Large') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Shape') }}</label>
                <select name="btn_shape" class="form-select selectpicker">
                    <option value="" @selected(($options['btn_shape'] ?? '' )=='' )>{{ translate('Default') }}</option>
                    <option value="rounded-0" @selected(($options['btn_shape'] ?? '' )=='rounded-0' )>{{
                        translate('Square') }}</option>
                    <option value="rounded-pill" @selected(($options['btn_shape'] ?? 'rounded-pill' )=='rounded-pill' )>
                        {{ translate('Rounded Pill') }}</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">{{ translate('Button Icon') }}</label>
                <select name="btn_icon" class="form-select selectpicker" data-live-search="true">
                    <option value="">{{ translate('No Icon') }}</option>
                    @foreach($bootstrapIcons as $iconClass => $iconLabel)
                    <option value="{{ $iconClass }}"
                        data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                        @selected(($options['btn_icon'] ?? '' )==$iconClass)>
                        {{ $iconLabel }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Animation Type') }}</label>
                <select name="aos_animation" class="form-select selectpicker">
                    <option value="" @selected(empty($options['aos_animation']))>{{ translate('None') }}</option>
                    <option value="fade-up" @selected(($options['aos_animation'] ?? '' )=='fade-up' )>Fade Up
                    </option>
                    <option value="fade-down" @selected(($options['aos_animation'] ?? '' )=='fade-down' )>Fade Down
                    </option>
                    <option value="fade-left" @selected(($options['aos_animation'] ?? '' )=='fade-left' )>Fade Left
                    </option>
                    <option value="fade-right" @selected(($options['aos_animation'] ?? '' )=='fade-right' )>Fade
                        Right</option>
                    <option value="zoom-in" @selected(($options['aos_animation'] ?? '' )=='zoom-in' )>Zoom In
                    </option>
                    <option value="zoom-out" @selected(($options['aos_animation'] ?? '' )=='zoom-out' )>Zoom Out
                    </option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Animation Delay (ms)') }}</label>
                <input type="number" name="aos_delay" class="form-control" step="50" min="0" max="3000"
                    value="{{ $options['aos_delay'] ?? '0' }}">
            </div>
        </div>

        @elseif($homeBlock->id == 'home_divider')
        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label">{{ translate('Divider Width') }} <small>(px or %)</small></label>
                <input type="text" name="divider_width" class="form-control"
                    value="{{ $options['divider_width'] ?? '100%' }}" placeholder="e.g. 100%, 10px">
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Height') }}</label>
                <div class="input-group">
                    <input type="number" name="divider_height" class="form-control"
                        value="{{ $options['divider_height'] ?? '1' }}" min="1">
                    <span class="input-group-text">px</span>
                </div>
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Color') }}</label>
                <input type="text" name="divider_color" class="form-control coloris"
                    value="{{ $options['divider_color'] ?? 'rgba(0,0,0,0.1)' }}">
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Spacing') }}</label>
                <div class="input-group">
                    <input type="number" name="divider_spacing" class="form-control"
                        value="{{ $options['divider_spacing'] ?? '20' }}" min="0">
                    <span class="input-group-text">px</span>
                </div>
            </div>
        </div>

        @elseif($homeBlock->id == 'home_newsletter')
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ translate('Background Color') }}</label>
                <input type="text" name="nl_bg_color" class="form-control coloris"
                    value="{{ $options['nl_bg_color'] ?? '#f8f9fa' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Title Color') }}</label>
                <input type="text" name="nl_title_color" class="form-control coloris"
                    value="{{ $options['nl_title_color'] ?? '#222222' }}">
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Text Transform') }}</label>
                <select name="nl_title_transform" class="form-select selectpicker">
                    <option value="default" @selected(($options['nl_title_transform'] ?? 'default' )=='default' )>
                        {{
                        translate('Default') }}</option>
                    <option value="uppercase" @selected(($options['nl_title_transform'] ?? '' )=='uppercase' )>{{
                        translate('Uppercase') }}</option>
                    <option value="lowercase" @selected(($options['nl_title_transform'] ?? '' )=='lowercase' )>{{
                        translate('Lowercase') }}</option>
                    <option value="capitalize" @selected(($options['nl_title_transform'] ?? '' )=='capitalize' )>{{
                        translate('Capitalize') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Placeholder Text') }}</label>
                <input type="text" name="nl_placeholder" class="form-control"
                    value="{{ $options['nl_placeholder'] ?? '' }}" placeholder="{{ translate('Enter your email') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Text') }}</label>
                <input type="text" name="nl_button_text" class="form-control"
                    value="{{ $options['nl_button_text'] ?? '' }}" placeholder="{{ translate('Subscribe') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Display') }}</label>
                <select name="nl_button_display" class="form-select selectpicker">
                    <option value="text_only" @selected(($options['nl_button_display'] ?? 'text_only' )=='text_only' )>
                        {{
                        translate('Text Only') }}</option>
                    <option value="icon_only" @selected(($options['nl_button_display'] ?? '' )=='icon_only' )>{{
                        translate('Icon Only') }}</option>
                    <option value="both" @selected(($options['nl_button_display'] ?? '' )=='both' )>{{
                        translate('Text & Icon') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Style') }}</label>
                <select name="nl_button_style" class="form-select selectpicker">
                    <option value="primary" @selected(($options['nl_button_style'] ?? 'primary' )=='primary' )>{{
                        translate('Primary') }}</option>
                    <option value="secondary" @selected(($options['nl_button_style'] ?? '' )=='secondary' )>{{
                        translate('Secondary') }}</option>
                    <option value="success" @selected(($options['nl_button_style'] ?? '' )=='success' )>{{
                        translate('Success') }}</option>
                    <option value="danger" @selected(($options['nl_button_style'] ?? '' )=='danger' )>{{
                        translate('Danger') }}</option>
                    <option value="dark" @selected(($options['nl_button_style'] ?? '' )=='dark' )>{{
                        translate('Dark') }}</option>
                    <option value="outline-primary" @selected(($options['nl_button_style'] ?? '' )=='outline-primary' )>
                        {{
                        translate('Outline Primary') }}</option>
                    <option value="outline-dark" @selected(($options['nl_button_style'] ?? '' )=='outline-dark' )>{{
                        translate('Outline Dark') }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Icon') }}</label>
                <select name="nl_button_icon" class="form-select selectpicker" data-live-search="true">
                    <option value="">{{ translate('No Icon') }}</option>
                    @foreach($bootstrapIcons as $iconClass => $iconLabel)
                    <option value="{{ $iconClass }}"
                        data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                        @selected(($options['nl_button_icon'] ?? '' )==$iconClass)>
                        {{ $iconLabel }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="nl_show_name" id="nl_show_name" value="1"
                        @checked(!empty($options['nl_show_name']))>
                    <label class="form-check-label" for="nl_show_name">{{ translate('Show Name Field') }}</label>
                </div>
            </div>
            <div class="col-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="nl_hide_top_icon" id="nl_hide_top_icon"
                        value="1" @checked(!empty($options['nl_hide_top_icon']))>
                    <label class="form-check-label" for="nl_hide_top_icon">{{ translate('Hide Top Icon') }}</label>
                </div>
            </div>
        </div>

        @elseif($homeBlock->id == 'home_offer_banner')
        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label">{{ translate('Button Text') }}</label>
                <input type="text" name="btn_text" class="form-control" value="{{ $options['btn_text'] ?? '' }}">
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Button URL') }}</label>
                <input type="text" name="btn_url" class="form-control" value="{{ $options['btn_url'] ?? '' }}">
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Regular Price') }}</label>
                <input type="text" name="regular_price" class="form-control"
                    value="{{ $options['regular_price'] ?? '' }}">
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Offer Price') }}</label>
                <input type="text" name="offer_price" class="form-control" value="{{ $options['offer_price'] ?? '' }}">
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Button Style') }}</label>
                <select name="btn_style" class="form-select selectpicker">
                    @foreach(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark',
                    'link', 'outline-primary', 'outline-dark', 'outline-light'] as $btnStyle)
                    <option value="{{ $btnStyle }}" @selected(($options['btn_style'] ?? 'primary' )==$btnStyle)>
                        {{ ucfirst(str_replace('-', ' ', $btnStyle)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label class="form-label">{{ translate('Button Icon') }}</label>
                <select name="btn_icon" class="form-select selectpicker" data-live-search="true">
                    <option value="">{{ translate('No Icon') }}</option>
                    @foreach($bootstrapIcons as $iconClass => $iconLabel)
                    <option value="{{ $iconClass }}"
                        data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                        @selected(($options['btn_icon'] ?? '' )==$iconClass)>
                        {{ $iconLabel }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12" id="ob_bg_style">
                <label class="form-label">{{ translate('Background Style') }}</label>
                <select name="bg_style" class="form-select selectpicker">
                    <option value="">{{ translate('No Background') }}</option>
                    @foreach(['primary-subtle', 'secondary-subtle', 'success-subtle', 'danger-subtle',
                    'warning-subtle', 'info-subtle', 'dark-subtle', 'light'] as $bgStyle)
                    <option value="{{ $bgStyle }}" @selected(($options['bg_style'] ?? '' )==$bgStyle)>
                        {{ ucfirst(str_replace('-', ' ', $bgStyle)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="show_megaphone" id="show_megaphone" value="1"
                        @checked(!empty($options['show_megaphone']))>
                    <label class="form-check-label" for="show_megaphone">{{ translate('Show Megaphone Icon')
                        }}</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Image') }}</label>
                <div class="input-group">
                    <span class="input-group-text p-1 bg-white">
                        <img id="preview-offer-img"
                            src="{{ !empty($options['image']) ? asset($options['image']) : asset('images/placeholders/default.png') }}"
                            width="60" height="40" style="object-fit: contain;">
                    </span>
                    <input type="text" class="form-control" readonly style="cursor: pointer;"
                        onclick="$('#file-offer-img').click()"
                        value="{{ !empty($options['image']) ? basename($options['image']) : translate('Choose Image') }}">
                    <button type="button" class="btn bg-text-primary" onclick="$('#file-offer-img').click()">{{
                        translate('Upload') }}</button>
                </div>
                <input id="file-offer-img" type="file" name="image" class="d-none repeater-image-input"
                    data-preview="#preview-offer-img" accept="image/*">
                @if(!empty($options['image']))
                <input type="hidden" name="old_image" value="{{ $options['image'] }}">
                @endif
            </div>
        </div>

        @elseif($homeBlock->id == 'home_tabs')
        <div class="mb-3">
            <label class="form-label">{{ translate('Tab Content Background') }}</label>
            <select name="tab_bg_style" class="form-select selectpicker">
                <option value="">{{ translate('Default') }}</option>
                @foreach(['primary-subtle', 'secondary-subtle', 'success-subtle', 'danger-subtle',
                'warning-subtle', 'info-subtle', 'dark-subtle', 'light'] as $bgStyle)
                <option value="{{ $bgStyle }}" @selected(($options['tab_bg_style'] ?? '' )==$bgStyle)>
                    {{ ucfirst(str_replace('-', ' ', $bgStyle)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="tab_content_shadow" id="tab_content_shadow"
                    value="1" @checked(!empty($options['tab_content_shadow']))>
                <label class="form-check-label" for="tab_content_shadow">{{ translate('Tab Content Box Shadow')
                    }}</label>
            </div>
        </div>

        @elseif($homeBlock->id == 'home_premium_plans')
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ translate('Action Button Text') }}</label>
                <input type="text" name="button_text" class="form-control" value="{{ $options['button_text'] ?? '' }}"
                    placeholder="{{ translate('Start Now') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Button Position') }}</label>
                <select name="button_position" class="form-select selectpicker">
                    <option value="after_features" @selected(($options['button_position'] ?? '' )=='after_features' )>{{
                        translate('After Features') }}</option>
                    <option value="before_features" @selected(($options['button_position'] ?? '' )=='before_features' )>
                        {{ translate('Before Features') }}</option>
                </select>
            </div>
        </div>

        @elseif($homeBlock->id === 'home_social_icons')
        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="multicolor_icons" value="1" id="multicolor_icons"
                    @checked($options['multicolor_icons'] ?? false)>
                <label class="form-check-label" for="multicolor_icons">{{ translate('Enable Multicolor Icons')
                    }}</label>
            </div>
        </div>
    </div>

    @elseif($homeBlock->id == 'home_hero')
    <div class="row g-3 mb-3">
        <div class="col-6">
            <label class="form-label">{{ translate('Design Style') }}</label>
            <select name="hero_design" class="form-select selectpicker">
                <option value="classic" @selected(($options['hero_design'] ?? '' )=='classic' )>{{
                    translate('Default') }}</option>
                <option value="modern" @selected(($options['hero_design'] ?? '' )=='modern' )>{{
                    translate('Modern Split') }}</option>
                <option value="gradient" @selected(($options['hero_design'] ?? '' )=='gradient' )>{{
                    translate('Gradient Overlay') }}</option>
                <option value="minimal" @selected(($options['hero_design'] ?? '' )=='minimal' )>{{
                    translate('Minimal Centered') }}</option>
                <option value="creative" @selected(($options['hero_design'] ?? '' )=='creative' )>{{
                    translate('Creative Boxed') }}</option>
            </select>
        </div>

        <div class="col-6">
            <label class="form-label">{{ translate('Title Design') }}</label>
            <select name="title_design" class="form-select selectpicker">
                <option value="default" @selected(($options['title_design'] ?? '' )=='default' )>{{
                    translate('Default Heading') }}</option>
                <option value="highlight" @selected(($options['title_design'] ?? '' )=='highlight' )>{{
                    translate('Highlight Accent') }}</option>
                <option value="underline" @selected(($options['title_design'] ?? '' )=='underline' )>{{
                    translate('Creative Underline') }}</option>
                <option value="outline" @selected(($options['title_design'] ?? '' )=='outline' )>{{
                    translate('Outline Style') }}</option>
                <option value="display" @selected(($options['title_design'] ?? '' )=='display' )>{{
                    translate('Display Banner') }}</option>
            </select>
        </div>

        <div class="col-6">
            <label class="form-label">{{ translate('Text Alignment') }}</label>
            <select name="text_align" class="form-select selectpicker">
                <option value="left" @selected(($options['text_align'] ?? '' )=='left' )>{{ translate('Left') }}
                </option>
                <option value="center" @selected(($options['text_align'] ?? '' )=='center' )>{{
                    translate('Center') }}</option>
                <option value="right" @selected(($options['text_align'] ?? '' )=='right' )>{{ translate('Right')
                    }}</option>
            </select>
        </div>

        <div class="col-6">
            <label class="form-label">{{ translate('Text Width (%)') }}</label>
            <input type="number" name="text_width" class="form-control" min="30" max="100" step="5"
                value="{{ $options['text_width'] ?? 60 }}">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">{{ translate('Background Type') }}</label>
        <select name="type" class="form-select selectpicker" data-conditional-toggle="#heroVideoBg, #heroImageBg"
            data-conditional-value="video, image">
            <option value="video" @selected(($options['type'] ?? '' )=='video' )>{{ translate('Video') }}
            </option>
            <option value="image" @selected(($options['type'] ?? '' )=='image' )>{{ translate('Image') }}
            </option>
        </select>
    </div>

    <div class="mb-3" id="heroVideoBg">
        <label class="form-label">{{ translate('Video URL') }}</label>
        <input type="text" name="video_url" class="form-control" value="{{ $options['video_url'] ?? '' }}"
            placeholder="https://... or local path">
    </div>

    <div class="mb-3" id="heroImageBg">
        <label class="form-label">{{ translate('Background Image') }}</label>
        <div class="input-group">
            <span class="input-group-text p-1 bg-white">
                <img id="preview-hero-img"
                    src="{{ !empty($options['image']) ? asset($options['image']) : asset('images/placeholders/default.png') }}"
                    width="60" height="40" style="object-fit: contain;">
            </span>
            <input type="text" class="form-control" readonly style="cursor: pointer;"
                onclick="$('#file-hero-img').click()"
                value="{{ !empty($options['image']) ? basename($options['image']) : translate('Choose Image') }}">
            <button type="button" class="btn bg-text-primary" onclick="$('#file-hero-img').click()">{{
                translate('Upload') }}</button>
        </div>
        <input id="file-hero-img" type="file" name="image" class="d-none repeater-image-input"
            data-preview="#preview-hero-img" accept="image/*">
        @if(!empty($options['image']))
        <input type="hidden" name="old_image" value="{{ $options['image'] }}">
        @endif
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ translate('Overlay Color') }}</label>
            <div class="colorpicker">
                <input type="text" name="overlay_color" class="form-control coloris"
                    value="{{ $options['overlay_color'] ?? '#000000' }}">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ translate('Overlay Opacity (0.0 - 1.0)') }}</label>
            <input type="number" name="overlay_opacity" class="form-control" step="0.1" min="0" max="1"
                value="{{ $options['overlay_opacity'] ?? '0.5' }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ translate('Title Color') }}</label>
            <div class="colorpicker">
                <input type="text" name="title_color" class="form-control coloris"
                    value="{{ $options['title_color'] ?? '' }}" placeholder="{{ translate('Auto') }}">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">{{ translate('Subtitle Color') }}</label>
            <div class="colorpicker">
                <input type="text" name="subtitle_color" class="form-control coloris"
                    value="{{ $options['subtitle_color'] ?? '' }}" placeholder="{{ translate('Auto') }}">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="search_enable" id="search_enable" value="1"
                @checked(!empty($options['search_enable'])) data-slide-toggle="#heroSearchText">
            <label class="form-check-label" for="search_enable">{{ translate('Enable Search Bar') }}</label>
        </div>
    </div>

    <div class="mb-3" id="heroSearchText">
        <label class="form-label">{{ translate('Search Placeholder') }}</label>
        <input type="text" name="search_placeholder" class="form-control"
            value="{{ $options['search_placeholder'] ?? '' }}" placeholder="{{ translate('Search for products...') }}">
    </div>

    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="show_bottom_fade" id="show_bottom_fade" value="1"
                @checked(($options['show_bottom_fade'] ?? '1' )=='1' )>
            <label class="form-check-label" for="show_bottom_fade">{{ translate('Bottom Fade Effect') }}</label>
        </div>
    </div>

    <hr>
    <h6 class="mb-3">{{ translate('Buttons') }}</h6>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-2"><strong>Button 1</strong></div>
            <div class="mb-2">
                <input type="text" name="btn1_text" class="form-control mb-1" placeholder="Text"
                    value="{{ $options['btn1_text'] ?? '' }}">
                <input type="text" name="btn1_url" class="form-control mb-1" placeholder="URL"
                    value="{{ $options['btn1_url'] ?? '' }}">
                <select name="btn1_class" class="form-select selectpicker">
                    <option value="primary" @selected(($options['btn1_class'] ?? '' )=='primary' )>Primary
                    </option>
                    <option value="success" @selected(($options['btn1_class'] ?? '' )=='success' )>Success
                    </option>
                    <option value="danger" @selected(($options['btn1_class'] ?? '' )=='danger' )>Danger</option>
                    <option value="info" @selected(($options['btn1_class'] ?? '' )=='info' )>Info</option>
                    <option value="light" @selected(($options['btn1_class'] ?? '' )=='light' )>Light</option>
                    <option value="outline-light" @selected(($options['btn1_class'] ?? '' )=='outline-light' )>
                        Outline Light</option>
                    <option value="dark" @selected(($options['btn1_class'] ?? '' )=='dark' )>Dark</option>
                    <option value="outline-dark" @selected(($options['btn1_class'] ?? '' )=='outline-dark' )>
                        Dark Outline</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2"><strong>Button 2</strong></div>
            <div class="mb-2">
                <input type="text" name="btn2_text" class="form-control mb-1" placeholder="Text"
                    value="{{ $options['btn2_text'] ?? '' }}">
                <input type="text" name="btn2_url" class="form-control mb-1" placeholder="URL"
                    value="{{ $options['btn2_url'] ?? '' }}">
                <select name="btn2_class" class="form-select selectpicker">
                    <option value="primary" @selected(($options['btn2_class'] ?? '' )=='primary' )>Primary
                    </option>
                    <option value="success" @selected(($options['btn2_class'] ?? '' )=='success' )>Success
                    </option>
                    <option value="danger" @selected(($options['btn2_class'] ?? '' )=='danger' )>Danger</option>
                    <option value="info" @selected(($options['btn2_class'] ?? '' )=='info' )>Info</option>
                    <option value="light" @selected(($options['btn2_class'] ?? '' )=='light' )>Light</option>
                    <option value="outline-light" @selected(($options['btn2_class'] ?? '' )=='outline-light' )>
                        Outline Light</option>
                    <option value="dark" @selected(($options['btn2_class'] ?? '' )=='dark' )>Dark</option>
                    <option value="outline-dark" @selected(($options['btn2_class'] ?? '' )=='outline-dark' )>
                        Dark Outline</option>
                </select>
            </div>
        </div>
    </div>

    @elseif($homeBlock->id == 'home_slider')
    <div class="mb-3">
        <label class="form-label">{{ translate('Slider Height (px)') }}</label>
        <input type="number" name="height" class="form-control" value="{{ $options['height'] ?? 400 }}">
    </div>
    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="autoplay" id="autoplay" value="1"
                @checked(!empty($options['autoplay'])) data-slide-toggle="#home_slider_options">
            <label class="form-check-label" for="autoplay">{{ translate('Enable Autoplay') }}</label>
        </div>
    </div>
    <div class="row g-3" id="home_slider_options">
        <div class="col-12">
            <label class="form-label">{{ translate('Autoplay Delay (ms)') }}</label>
            <input type="number" name="autoplay_delay" class="form-control form-control-sm"
                value="{{ $options['autoplay_delay'] ?? 3000 }}" placeholder="e.g. 3000">
            <small class="text-muted">{{ translate('Time between slide transitions. Default is 3000ms.') }}</small>
        </div>
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="loop" id="loop" value="1"
                    @checked($options['loop'] ?? true)>
                <label class="form-check-label" for="loop">{{ translate('Enable Infinite Loop') }}</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="pause_on_hover" id="pause_on_hover" value="1"
                    @checked($options['pause_on_hover'] ?? false)>
                <label class="form-check-label" for="pause_on_hover">{{ translate('Pause on Hover/Interaction')
                    }}</label>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="show_navigation" id="show_navigation" value="1"
                    @checked($options['show_navigation'] ?? true)>
                <label class="form-check-label" for="show_navigation">{{ translate('Show Navigation (Arrows)')
                    }}</label>
            </div>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="show_pagination" id="show_pagination" value="1"
                    @checked($options['show_pagination'] ?? true)>
                <label class="form-check-label" for="show_pagination">{{ translate('Show Pagination (Bullets)')
                    }}</label>
            </div>
        </div>
    </div>
    @endif

    @if($isProductBlock)
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="show_category" id="show_category" value="1"
                    @checked(!empty($options['show_category']) ? $options['show_category'] : 1)>
                <label class="form-check-label" for="show_category">{{ translate('Category') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="seller_avatar" id="seller_avatar" value="1"
                    @checked(!empty($options['seller_avatar']) ? $options['seller_avatar'] : 1)>
                <label class="form-check-label" for="seller_avatar">{{ translate('Seller Avatar') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="seller_name" id="seller_name" value="1"
                    @checked(!empty($options['seller_name']) ? $options['seller_name'] : 1)>
                <label class="form-check-label" for="seller_name">{{ translate('Seller Name') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="total_sales" id="total_sales" value="1"
                    @checked(!empty($options['total_sales']) ? $options['total_sales'] : 1)>
                <label class="form-check-label" for="total_sales">{{ translate('Total Sales') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="total_reviews" id="total_reviews" value="1"
                    @checked(!empty($options['total_reviews']) ? $options['total_reviews'] : 1)>
                <label class="form-check-label" for="total_reviews">{{ translate('Total Reviews') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="post_date" id="post_date" value="1"
                    @checked(!empty($options['post_date']) ? $options['post_date'] : 1)>
                <label class="form-check-label" for="post_date">{{ translate('Post Date') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="cart_btn" id="cart_btn" value="1"
                    @checked(!empty($options['cart_btn']) ? $options['cart_btn'] : 1)>
                <label class="form-check-label" for="cart_btn">{{ translate('Cart Button') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="live_preview_btn" id="live_preview_btn" value="1"
                    @checked(!empty($options['live_preview_btn']) ? $options['live_preview_btn'] : 1)>
                <label class="form-check-label" for="live_preview_btn">{{ translate('Preview Button') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="favorite_btn" id="favorite_btn" value="1"
                    @checked(!empty($options['favorite_btn']) ? $options['favorite_btn'] : 1)>
                <label class="form-check-label" for="favorite_btn">{{ translate('Favorite Button') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="product_badge" id="product_badge" value="1"
                    @checked(!empty($options['product_badge']) ? $options['product_badge'] : 1)>
                <label class="form-check-label" for="product_badge">{{ translate('Product Badge') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="total_downloads" id="total_downloads" value="1"
                    @checked(!empty($options['total_downloads']) ? $options['total_downloads'] : 1)>
                <label class="form-check-label" for="total_downloads">{{ translate('Total Downloads') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="download_btn" id="download_btn" value="1"
                    @checked(!empty($options['download_btn']) ? $options['download_btn'] : 1)>
                <label class="form-check-label" for="download_btn">{{ translate('Download Button') }}</label>
            </div>
        </div>
    </div>
    @elseif($homeBlock->id === 'home_blog_articles')
    <div class="mb-3">
        <label class="form-label">{{ translate('Number of Posts to Show') }}</label>
        <input type="number" name="blog_number" class="form-control" value="{{ $options['blog_number'] ?? 3 }}"
            min="1">
    </div>
    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="show_category" id="show_category" value="1"
                    @checked(!isset($options['show_category']) ? true : !empty($options['show_category']))>
                <label class="form-check-label" for="show_category">{{ translate('Category') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="author_name" id="author_name" value="1"
                    @checked(!isset($options['author_name']) ? true : !empty($options['author_name']))>
                <label class="form-check-label" for="author_name">{{ translate('Author Name') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="post_date" id="post_date" value="1"
                    @checked(!isset($options['post_date']) ? true : !empty($options['post_date']))>
                <label class="form-check-label" for="post_date">{{ translate('Post Date') }}</label>
            </div>
        </div>
        <div class="col-6">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="readmore_btn" id="readmore_btn" value="1"
                    @checked(!isset($options['readmore_btn']) ? true : !empty($options['readmore_btn']))>
                <label class="form-check-label" for="readmore_btn">{{ translate('Read More Button') }}</label>
            </div>
        </div>
    </div>
    @elseif($homeBlock->id === 'home_featured_seller')
    <div class="mb-3">
        <label class="form-label">{{ translate('Number of Products to Show') }}</label>
        <input type="number" name="featured_products_number" class="form-control"
            value="{{ $options['featured_products_number'] ?? 3 }}" min="1">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ translate('Background Style') }}</label>
        <select name="featured_products_bg_style" class="form-select selectpicker">
            <option value="">{{ translate('No Background') }}</option>
            @foreach(['primary-subtle', 'secondary-subtle', 'success-subtle', 'danger-subtle',
            'warning-subtle', 'info-subtle', 'dark-subtle', 'light'] as $bgStyle)
            <option value="{{ $bgStyle }}" @selected(($options['featured_products_bg_style'] ?? '' )==$bgStyle)>
                {{ ucfirst(str_replace('-', ' ', $bgStyle)) }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Title Area Options --}}
    @include('admin.builders.home.partials.title-options', [
    'options' => $options,
    'homeBlock' => $homeBlock
    ])

    {{-- Common Display Settings --}}
    @include('admin.builders.home.partials.display-options', [
    'options' => $options,
    'isActive' => $isActive,
    'homeBlock' => $homeBlock
    ])

    @if($isContentBlock)
    </div>

    <div class="tab-pane fade" id="content" role="tabpanel">
        <div id="content-repeater">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ translate(':item', ['item' => $homeBlock->title]) }}</h6>
                <button type="button" class="btn btn-sm btn-primary add-item-btn"><i
                        class="bi bi-plus-lg me-1"></i>{{translate('Add New') }}</button>
            </div>

            <div class="content-list accordion accordion-flush" id="accordionContent">
                @forelse($contentItems as $key => $item)
                @include('admin.builders.home.partials.repeater-item', ['key' => $key, 'item' => $item, 'type' =>
                $homeBlock->id])
                @empty
                <div class="text-center p-3 text-muted empty-state">{{ translate('No items added yet.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    </div>

    <div class="d-none">
        <template id="item-template">
            @include('admin.builders.home.partials.repeater-item', ['key' => 'INDEX', 'item' => [], 'type' =>
            $homeBlock->id])
        </template>
    </div>
    @endif
</form>
