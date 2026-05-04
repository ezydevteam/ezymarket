@php
$excludedBlocks = ['home_hero', 'home_rich_text', 'home_html', 'home_widget',
'home_advertisement', 'home_featured_seller'];
$excludedStyle = ['home_button'];
$hideBlockOptions = in_array($homeBlock->id, $excludedBlocks);
$hideBlockStyle = in_array($homeBlock->id, $excludedStyle);
$productBlocks = ['home_products', 'home_product_tabs'];
$isProductBlock = in_array($homeBlock->id, $productBlocks);
@endphp

@if(!$hideBlockOptions)
<div class="row g-3 mb-3">
    @if(!$hideBlockStyle)
    <div class="col-6">
        <label class="form-label">{{ translate('Block Style') }}</label>
        <select name="block_style" class="form-select selectpicker"
            data-conditional-toggle="#testimonialAutoplay, #ob_bg_style" data-conditional-value="swiper, glass"
            data-conditional-logic="equal, not-equal">
            @if($homeBlock->id === 'home_categories')
            <option value="swiper" @selected(($options['block_style'] ?? 'swiper' )=='swiper' )>{{ translate('Swiper
                Carousel') }}</option>
            <option value="classic" @selected(($options['block_style'] ?? '' )=='classic' )>{{ translate('Classic Grid')
                }}</option>
            @elseif($homeBlock->id === 'home_faqs')
            <option value="12" @selected(($options['block_style'] ?? '12' )=='12' )>{{ translate('1 Column') }}
            </option>
            <option value="6" @selected(($options['block_style'] ?? '' )=='6' )>{{ translate('2 Columns')
                }}</option>
            <option value="4" @selected(($options['block_style'] ?? '' )=='4' )>{{ translate('3 Columns') }}
            </option>
            @elseif($homeBlock->id === 'home_testimonials')
            <option value="swiper" @selected(($options['block_style'] ?? 'swiper' )=='swiper' )>{{ translate('Swiper
                Carousel') }}</option>
            <option value="grid" @selected(($options['block_style'] ?? '' )=='grid' )>{{ translate('Classic Grid') }}
            </option>
            @elseif($homeBlock->id === 'home_newsletter')
            <option value="generic" @selected(($options['block_style'] ?? 'generic' )=='generic' )>{{
                translate('Default') }}</option>
            <option value="card" @selected(($options['block_style'] ?? '' )=='card' )>{{ translate('Card') }}</option>
            <option value="inline" @selected(($options['block_style'] ?? '' )=='inline' )>{{ translate('Inline') }}
            </option>
            <option value="minimal" @selected(($options['block_style'] ?? '' )=='minimal' )>{{ translate('Minimal') }}
            </option>
            <option value="boxed" @selected(($options['block_style'] ?? '' )=='boxed' )>{{ translate('Boxed') }}
            </option>
            <option value="pill" @selected(($options['block_style'] ?? '' )=='pill' )>{{ translate('Rounded Pill') }}
            </option>
            <option value="modern" @selected(($options['block_style'] ?? '' )=='modern' )>{{ translate('Modern
                Floating') }}</option>
            @elseif($homeBlock->id === 'home_offer_banner')
            <option value="modern" @selected(($options['block_style'] ?? 'modern' )=='modern' )>{{ translate('Modern
                Card') }}</option>
            <option value="glass" @selected(($options['block_style'] ?? '' )=='glass' )>{{ translate('Glassmorphism') }}
            </option>
            <option value="creative" @selected(($options['block_style'] ?? '' )=='creative' )>{{ translate('Creative
                Split') }}</option>
            <option value="minimal" @selected(($options['block_style'] ?? '' )=='minimal' )>{{ translate('Minimal
                Border') }}</option>
            @elseif($homeBlock->id === 'home_countdown')
            <option value="default" @selected(($options['block_style'] ?? 'default' )=='default' )>{{
                translate('Default')
                }}</option>
            <option value="circle" @selected(($options['block_style'] ?? '' )=='circle' )>{{ translate('Circle Ring') }}
            </option>
            <option value="digital" @selected(($options['block_style'] ?? '' )=='digital' )>{{ translate('Digital Box')
                }}
            </option>
            <option value="minimal" @selected(($options['block_style'] ?? '' )=='minimal' )>{{ translate('Minimal Text')
                }}</option>
            @elseif($homeBlock->id === 'home_login_form')
            <option value="default" @selected(($options['block_style'] ?? 'default' )=='default' )>{{
                translate('Default') }}</option>
            <option value="icons" @selected(($options['block_style'] ?? '' )=='icons' )>{{ translate('With Icons') }}
            </option>
            <option value="rounded" @selected(($options['block_style'] ?? '' )=='rounded' )>{{ translate('Rounded Pill')
                }}</option>
            @elseif($homeBlock->id === 'home_divider')
            <option value="horizontal" @selected(($options['block_style'] ?? 'horizontal' )=='horizontal' )>{{
                translate('Horizontal') }}</option>
            <option value="vertical" @selected(($options['block_style'] ?? '' )=='vertical' )>{{ translate('Vertical')
                }}</option>
            @elseif($homeBlock->id === 'home_premium_plans')
            <option value="default" @selected(($options['block_style'] ?? 'default' )=='default' )>{{
                translate('Default Card') }}</option>
            <option value="glass" @selected(($options['block_style'] ?? '' )=='glass' )>{{ translate('Glassmorphism')
                }}</option>
            <option value="bordered" @selected(($options['block_style'] ?? '' )=='bordered' )>{{ translate('Clean
                Bordered')
                }}</option>
            <option value="vibrant" @selected(($options['block_style'] ?? '' )=='vibrant' )>{{ translate('Vibrant
                Gradient')
                }}</option>
            @elseif($homeBlock->id === 'home_image')
            <option value="default" @selected(($options['block_style'] ?? 'default' )=='default' )>{{
                translate('Default') }}</option>
            <option value="overlay" @selected(($options['block_style'] ?? '' )=='overlay' )>{{ translate('Overlay
                Title')
                }}</option>
            <option value="card" @selected(($options['block_style'] ?? '' )=='card' )>{{ translate('Bottom Card') }}
            </option>
            <option value="creative_split" @selected(($options['block_style'] ?? '' )=='creative_split' )>{{
                translate('Creative Split') }}</option>
            @elseif($homeBlock->id === 'home_social_icons')
            <option value="default" @selected(($options['block_style'] ?? 'default' )=='default' )>{{
                translate('Default (Circle)') }}</option>
            <option value="square" @selected(($options['block_style'] ?? '' )=='square' )>{{ translate('Rounded Square')
                }}</option>
            <option value="outline" @selected(($options['block_style'] ?? '' )=='outline' )>{{ translate('Outline
                Circle') }}</option>
            <option value="glass" @selected(($options['block_style'] ?? '' )=='glass' )>{{ translate('Glassmorphism') }}
            </option>
            <option value="minimal" @selected(($options['block_style'] ?? '' )=='minimal' )>{{ translate('Minimal') }}
            </option>
            <option value="inline_name" @selected(($options['block_style'] ?? '' )=='inline_name' )>{{ translate('Inline
                Name') }}
            </option>
            <option value="bottom_name" @selected(($options['block_style'] ?? '' )=='bottom_name' )>{{ translate('Bottom
                Name') }}
            </option>
            @elseif($isProductBlock)
            <option value="grid" @selected(($options['block_style'] ?? 'grid' )=='grid' )>{{ translate('Grid')
                }}</option>
            <option value="list" @selected(($options['block_style'] ?? '' )=='list' )>{{ translate('List') }}
            </option>
            <option value="mixed" @selected(($options['block_style'] ?? '' )=='mixed' )>{{ translate('Mixed') }}
            </option>
            <option value="split" @selected(($options['block_style'] ?? '' )=='split' )>{{ translate('Split')
                }}</option>
            <option value="background" @selected(($options['block_style'] ?? '' )=='background' )>{{
                translate('Background') }}
            </option>
            <option value="overlay" @selected(($options['block_style'] ?? '' )=='overlay' )>{{ translate('Overlay')
                }}</option>
            @elseif($homeBlock->id === 'home_tabs')
            <option value="pills" @selected(($options['block_style'] ?? 'pills' )=='pills' )>{{ translate('Rounded
                Pills') }}</option>
            <option value="underline" @selected(($options['block_style'] ?? '' )=='underline' )>{{
                translate('Underline') }}</option>
            <option value="boxed" @selected(($options['block_style'] ?? '' )=='boxed' )>{{ translate('Boxed Card') }}
            </option>
            <option value="vertical" @selected(($options['block_style'] ?? '' )=='vertical' )>{{ translate('Vertical
                Side') }}</option>
            @elseif($homeBlock->id === 'home_slider')
            <option value="default" @selected(($options['block_style'] ?? 'default' )=='default' )>{{ translate('Fade
                Caption') }}</option>
            <option value="modern" @selected(($options['block_style'] ?? '' )=='modern' )>{{ translate('Floating
                Card') }}</option>
            <option value="creative" @selected(($options['block_style'] ?? '' )=='creative' )>{{ translate('Split
                Layout') }}</option>
            <option value="centered" @selected(($options['block_style'] ?? '' )=='centered' )>{{ translate('Centered
                Bubble') }}</option>
            <option value="minimal" @selected(($options['block_style'] ?? '' )=='minimal' )>{{ translate('Minimal
                Image') }}</option>

            @elseif($homeBlock->id === 'home_blog_articles')
            <option value="grid" @selected(($options['block_style'] ?? 'grid' )=='grid' )>{{ translate('Grid') }}
            </option>
            <option value="list" @selected(($options['block_style'] ?? '' )=='list' )>{{ translate('List') }}
            </option>
            <option value="split" @selected(($options['block_style'] ?? '' )=='split' )>{{ translate('Split') }}
            </option>
            @else
            <option value="style1" @selected(($options['block_style'] ?? '' )=='style1' )>{{ translate('Style 1') }}
            </option>
            <option value="style2" @selected(($options['block_style'] ?? '' )=='style2' )>{{ translate('Style 2') }}
            </option>
            @endif
        </select>
    </div>
    @endif
    <div class="{{ $hideBlockStyle ? 'col-12' : 'col-6' }}">
        <label class="form-label">{{ translate('Block Alignment') }}</label>
        <select name="block_alignment" class="form-select selectpicker">
            <option value="start" @selected(($options['block_alignment'] ?? 'start' )=='start' )>{{ translate('Left') }}
            </option>
            <option value="center" @selected(($options['block_alignment'] ?? '' )=='center' )>{{ translate('Center') }}
            </option>
            <option value="end" @selected(($options['block_alignment'] ?? '' )=='end' )>{{ translate('Right') }}
            </option>
        </select>
    </div>
</div>
@endif
