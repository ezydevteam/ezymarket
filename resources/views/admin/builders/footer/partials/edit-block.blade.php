@php
    $options = $footerBlock->options ?? [];
    $isActive = ($options['status'] ?? 1) == 1;
@endphp

<form
    id="editBlockForm"
    action="{{ route('admin.builders.footer.edit-block', $footerBlock->id) }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    <div class="mb-3">
        <label class="form-label">{{ translate('Title') }}</label>
        <input type="text" name="title" class="form-control" value="{{ $footerBlock->title }}" required />
    </div>

    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="show_title" name="show_title" @checked($options['show_title'] ?? false) data-slide-toggle="#titleStyles">
            <label class="form-check-label" for="show_title">{{ translate('Show Title') }}</label>
        </div>
    </div>

    <div id="titleStyles" class="card card-body bg-light border-0 mb-3 {{ ($options['show_title'] ?? false) ? '' : 'd-none' }}">
        <div class="row g-3">
            <div class="col-6">
                 <label class="form-label">{{ translate('Text Size') }}</label>
                 <select name="title_size" class="form-select selectpicker">
                     <option value="h6" @selected(($options['title_size'] ?? 'h6') == 'h6')>{{ translate('Small (H6)') }}</option>
                     <option value="h5" @selected(($options['title_size'] ?? '') == 'h5')>{{ translate('Medium (H5)') }}</option>
                     <option value="h4" @selected(($options['title_size'] ?? '') == 'h4')>{{ translate('Large (H4)') }}</option>
                     <option value="h3" @selected(($options['title_size'] ?? '') == 'h3')>{{ translate('Ex-Large (H3)') }}</option>
                 </select>
            </div>
             <div class="col-6">
                 <label class="form-label">{{ translate('Alignment') }}</label>
                 <select name="title_align" class="form-select selectpicker">
                     <option value="start" @selected(($options['title_align'] ?? 'start') == 'start')>{{ translate('Left') }}</option>
                     <option value="center" @selected(($options['title_align'] ?? '') == 'center')>{{ translate('Center') }}</option>
                     <option value="end" @selected(($options['title_align'] ?? '') == 'end')>{{ translate('Right') }}</option>
                 </select>
            </div>
            <div class="col-6">
                 <label class="form-label">{{ translate('Transform') }}</label>
                 <select name="title_transform" class="form-select selectpicker">
                     <option value="" @selected(($options['title_transform'] ?? '') == '')>{{ translate('Default') }}</option>
                     <option value="uppercase" @selected(($options['title_transform'] ?? '') == 'uppercase')>{{ translate('Uppercase') }}</option>
                     <option value="capitalize" @selected(($options['title_transform'] ?? '') == 'capitalize')>{{ translate('Capitalize') }}</option>
                     <option value="lowercase" @selected(($options['title_transform'] ?? '') == 'lowercase')>{{ translate('Lowercase') }}</option>
                 </select>
            </div>
             <div class="col-6">
                 <label class="form-label">{{ translate('Color') }}</label>
                 <div class="colorpicker">
                    <input type="text" name="title_color" class="form-control coloris" value="{{ $options['title_color'] ?? '' }}" placeholder="Default">
                 </div>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_border_bottom" name="show_border_bottom" @checked($options['show_border_bottom'] ?? false)>
                    <label class="form-check-label" for="show_border_bottom">{{ translate('Show Border Bottom') }}</label>
                </div>
            </div>
        </div>
    </div>

    {{-- Element-Specific Settings --}}
    @switch($footerBlock->id)
        @case('footer_logo')
            <div class="mb-3">
                <label class="form-label">{{ translate('Logo Style') }}</label>
                <select name="logo_style" class="form-select selectpicker" data-conditional-toggle="#footerLogoDimension" data-conditional-value="site_title" data-conditional-logic="not-equal">
                    <option value="logo_dark" @selected(($options['logo_style'] ?? 'logo_dark') == 'logo_dark')>{{ translate('Dark Logo') }}</option>
                    <option value="logo_light" @selected(($options['logo_style'] ?? '') == 'logo_light')>{{ translate('Light Logo') }}</option>
                    <option value="site_title" @selected(($options['logo_style'] ?? '') == 'site_title')>{{ translate('Site Title') }}</option>
                </select>
                <p class="form-text mb-0">{{ translate('Set logo from appearance->themes->general') }}</p>
            </div>
            <div class="row g-3 d-none" id="footerLogoDimension">
                <div class="col-6">
                    <label class="form-label">{{ translate('Logo Width') }} <small class="text-muted">(px)</small></label>
                    <input type="number" name="logo_width" class="form-control" value="{{ $options['logo_width'] ?? '' }}" placeholder="Auto">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Logo Height') }} <small class="text-muted">(px)</small></label>
                    <input type="number" name="logo_height" class="form-control" value="{{ $options['logo_height'] ?? '' }}" placeholder="Auto">
                </div>
            </div>
            @break

        @case('footer_about')
            <div class="mb-3">
                <label class="form-label">{{ translate('About Text') }}</label>
                <textarea name="about_text" class="form-control" rows="4">{{ $options['about_text'] ?? '' }}</textarea>
            </div>
            @break

        @case('footer_menu')
            <div class="mb-3">
                <label class="form-label">{{ translate('Menu Style') }}</label>
                <select name="menu_style" class="form-select selectpicker" data-conditional-toggle="#columnInfo" data-conditional-value="columns">
                    <option value="columns" @selected(($options['menu_style'] ?? 'columns') == 'columns')>{{ translate('Multi Column') }}</option>
                    <option value="vertical" @selected(($options['menu_style'] ?? '') == 'vertical')>{{ translate('Vertical List') }}</option>
                    <option value="horizontal" @selected(($options['menu_style'] ?? '') == 'horizontal')>{{ translate('Horizontal') }}</option>
                </select>
                <p class="form-text mb-0" id="columnInfo">{{ translate('Make menu columns from appearance->menus->footer-menu') }}</p>
            </div>

            <hr>
            <h6 class="mb-3 fw-bold">{{ translate('Menu Label Styles') }}</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ translate('Font Size') }}</label>
                    <div class="input-group">
                        <input type="number" name="root_font_size" class="form-control" value="{{ $options['root_font_size'] ?? '' }}" placeholder="Default">
                        <span class="input-group-text">px</span>
                    </div>
                </div>
                 <div class="col-md-6">
                    <label class="form-label">{{ translate('Text Color') }}</label>
                    <div class="colorpicker">
                        <input type="text" name="root_color" class="form-control coloris" value="{{ $options['root_color'] ?? '' }}" placeholder="Default">
                    </div>
                </div>
                 <div class="col-md-6">
                     <label class="form-label">{{ translate('Font Weight') }}</label>
                     <select name="root_weight" class="form-select selectpicker">
                         <option value="400" @selected(($options['root_weight'] ?? '') == '')>{{ translate('Default') }}</option>
                         <option value="300" @selected(($options['root_weight'] ?? '') == '300')>{{ translate('Light') }}</option>
                         <option value="500" @selected(($options['root_weight'] ?? '') == '500')>{{ translate('Medium') }}</option>
                         <option value="600" @selected(($options['root_weight'] ?? '') == '600')>{{ translate('Semi Bold') }}</option>
                         <option value="700" @selected(($options['root_weight'] ?? '') == '700')>{{ translate('Bold') }}</option>
                     </select>
                </div>
                <div class="col-md-6">
                     <label class="form-label">{{ translate('Transform') }}</label>
                     <select name="root_transform" class="form-select selectpicker">
                         <option value="" @selected(($options['root_transform'] ?? '') == '')>{{ translate('Default') }}</option>
                         <option value="uppercase" @selected(($options['root_transform'] ?? '') == 'uppercase')>{{ translate('Uppercase') }}</option>
                         <option value="capitalize" @selected(($options['root_transform'] ?? '') == 'capitalize')>{{ translate('Capitalize') }}</option>
                         <option value="lowercase" @selected(($options['root_transform'] ?? '') == 'lowercase')>{{ translate('Lowercase') }}</option>
                     </select>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="root_border_bottom" name="root_border_bottom" @checked($options['root_border_bottom'] ?? false)>
                        <label class="form-check-label" for="root_border_bottom">{{ translate('Show Border Bottom') }}</label>
                    </div>
                </div>
            </div>

            <hr>
            <h6 class="mb-3 fw-bold">{{ translate('Menu Items Styles') }}</h6>
             <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">{{ translate('Item Color') }}</label>
                    <div class="colorpicker">
                        <input type="text" name="item_color" class="form-control coloris" value="{{ $options['item_color'] ?? '' }}" placeholder="Default">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ translate('Hover Color') }}</label>
                    <div class="colorpicker">
                        <input type="text" name="item_hover_color" class="form-control coloris" value="{{ $options['item_hover_color'] ?? '' }}" placeholder="Default">
                    </div>
                </div>
                <div class="col-12">
                     <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="item_underline" name="item_underline" @checked($options['item_underline'] ?? false)>
                        <label class="form-check-label" for="item_underline">{{ translate('Underline on Hover') }}</label>
                    </div>
                </div>
            </div>
            @break

        @case('footer_links')
            <div class="mb-3">
                <label class="form-label">{{ translate('Manage Links') }}</label>
                <div id="links-wrapper">
                    @php $links = array_values($options['links'] ?? []); @endphp
                    @foreach($links as $index => $link)
                        <div class="input-group mb-2 link-item">
                            <input type="text" name="links[{{ $index }}][label]" class="form-control" placeholder="{{ translate('Label') }}" value="{{ $link['label'] ?? '' }}">
                            <input type="text" name="links[{{ $index }}][url]" class="form-control" placeholder="{{ translate('URL') }}" value="{{ $link['url'] ?? '' }}">
                            <button type="button" class="btn bg-text-red remove-link-btn"><i class="bi bi-trash"></i></button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-secondary" id="add-link-btn">
                    <i class="bi bi-plus-lg"></i> {{ translate('Add Link') }}
                </button>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Link Color') }}</label>
                    <div class="colorpicker">
                        <input type="text" name="link_color" class="form-control coloris" value="{{ $options['link_color'] ?? '' }}" placeholder="Default">
                    </div>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Hover Color') }}</label>
                    <div class="colorpicker">
                        <input type="text" name="link_hover_color" class="form-control coloris" value="{{ $options['link_hover_color'] ?? '' }}" placeholder="Default">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">{{ translate('Link Display') }}</label>
                    <select name="link_display" class="form-select selectpicker">
                        <option value="horizontal" @selected(($options['link_display'] ?? '') == 'horizontal')>{{ translate('Horizontal') }}</option>
                        <option value="vertical" @selected(($options['link_display'] ?? 'vertical') == 'vertical')>{{ translate('Vertical') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Link Style') }}</label>
                    <select name="link_style" class="form-select selectpicker">
                        <option value="bullet" @selected(($options['link_style'] ?? 'bullet') == 'bullet')>{{ translate('Bullet Points') }}</option>
                        <option value="arrow" @selected(($options['link_style'] ?? '') == 'arrow')>{{ translate('Arrow Icons') }}</option>
                        <option value="plain" @selected(($options['link_style'] ?? '') == 'plain')>{{ translate('None') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Link Target') }}</label>
                    <select name="link_target" class="form-select selectpicker">
                        <option value="_self" @selected(($options['link_target'] ?? '_self') == '_self')>{{ translate('Same Tab') }}</option>
                        <option value="_blank" @selected(($options['link_target'] ?? '') == '_blank')>{{ translate('New Tab') }}</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="link_underline" name="link_underline" value="1" @checked($options['link_underline'] ?? false)>
                        <label class="form-check-label" for="link_underline">{{ translate('Underline on Hover') }}</label>
                    </div>
                </div>
            </div>
            @break

        @case('footer_contact')
            <div class="mb-3">
                <label class="form-label">{{ translate('Address') }}</label>
                <textarea name="address" class="form-control" rows="2" placeholder="{{ $settings->general->address ?? translate('Enter address') }}">{{ $options['address'] ?? '' }}</textarea>
                <small class="text-muted">{{ translate('Leave empty to hide') }}</small>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ $options['phone'] ?? '' }}" placeholder="{{ $settings->general->phone ?? translate('Enter phone') }}">
                <small class="text-muted">{{ translate('Leave empty to hide') }}</small>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Email') }}</label>
                <input type="text" name="email" class="form-control" value="{{ $options['email'] ?? '' }}" placeholder="{{ $settings->general->email ?? translate('Enter email') }}">
                <small class="text-muted">{{ translate('Leave empty to hide') }}</small>
            </div>
             <div class="mb-3">
                <label class="form-label">{{ translate('More Info') }}</label>
                <textarea name="more_info" class="form-control" rows="3" placeholder="{{ translate('Additional information...') }}">{{ $options['more_info'] ?? '' }}</textarea>
            </div>
            @break

        @case('footer_social')
            <div class="mb-3">
                <label class="form-label">{{ translate('Icon Style') }}</label>
                <select name="icon_style" class="form-select selectpicker">
                    <option value="rounded" @selected(($options['icon_style'] ?? 'rounded') == 'rounded')>{{ translate('Rounded') }}</option>
                    <option value="square" @selected(($options['icon_style'] ?? '') == 'square')>{{ translate('Square') }}</option>
                    <option value="circle" @selected(($options['icon_style'] ?? '') == 'circle')>{{ translate('Circle') }}</option>
                    <option value="plain" @selected(($options['icon_style'] ?? '') == 'plain')>{{ translate('Plain') }}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Icon Color') }}</label>
                <select name="icon_color" class="form-select selectpicker">
                    <option value="monochrome" @selected(($options['icon_color'] ?? 'monochrome') == 'monochrome')>{{ translate('Monochrome') }}</option>
                    <option value="multicolor" @selected(($options['icon_color'] ?? '') == 'multicolor')>{{ translate('Brand Colors') }}</option>
                </select>
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="hover_effect" name="hover_effect" @checked($options['hover_effect'] ?? true)>
                    <label class="form-check-label" for="hover_effect">{{ translate('Show Hover Effect') }}</label>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_tooltip" name="show_tooltip" @checked($options['show_tooltip'] ?? false)>
                    <label class="form-check-label" for="show_tooltip">{{ translate('Show Tooltip') }}</label>
                </div>
            </div>
            @break

        @case('footer_newsletter')
            <div class="mb-3">
                <label class="form-label">{{ translate('Heading') }}</label>
                <input type="text" name="heading" class="form-control" value="{{ $options['heading'] ?? translate('Subscribe to newsletter') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Sub Heading') }}</label>
                <input type="text" name="sub_heading" class="form-control" value="{{ $options['sub_heading'] ?? translate('Get the latest updates and offers.') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Heading & Sub-heading Alignment') }}</label>
                <select name="heading_align" class="form-select selectpicker">
                    <option value="start" @selected(($options['heading_align'] ?? 'start') == 'start')>{{ translate('Left') }}</option>
                    <option value="center" @selected(($options['heading_align'] ?? '') == 'center')>{{ translate('Center') }}</option>
                    <option value="end" @selected(($options['heading_align'] ?? '') == 'end')>{{ translate('Right') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Display Style') }}</label>
                <select name="style" class="form-select selectpicker">
                    <option value="default" @selected(($options['style'] ?? 'default') == 'default')>{{ translate('Default') }}</option>
                    <option value="minimal" @selected(($options['style'] ?? '') == 'minimal')>{{ translate('Minimal') }}</option>
                    <option value="boxed" @selected(($options['style'] ?? '') == 'boxed')>{{ translate('Boxed') }}</option>
                    <option value="pill" @selected(($options['style'] ?? '') == 'pill')>{{ translate('Rounded Pill') }}</option>
                    <option value="modern" @selected(($options['style'] ?? '') == 'modern')>{{ translate('Modern') }}</option>
                    <option value="footer" @selected(($options['style'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                    <option value="inline" @selected(($options['style'] ?? '') == 'inline')>{{ translate('Inline') }}</option>
                    <option value="card" @selected(($options['style'] ?? '') == 'card')>{{ translate('Card') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Email Placeholder') }}</label>
                <input type="text" name="placeholder" class="form-control" value="{{ $options['placeholder'] ?? translate('Enter your email') }}">
            </div>

            <div class="mb-3">
                 <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_name" name="show_name" @checked($options['show_name'] ?? false) data-slide-toggle="#namePlaceholderInput">
                    <label class="form-check-label" for="show_name">{{ translate('Show Name Field') }}</label>
                </div>
            </div>

            <div class="mb-3 {{ ($options['show_name'] ?? false) ? '' : 'd-none' }}" id="namePlaceholderInput">
                <label class="form-label">{{ translate('Name Placeholder') }}</label>
                <input type="text" name="name_placeholder" class="form-control" value="{{ $options['name_placeholder'] ?? translate('Your Name') }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Content') }}</label>
                    <select name="button_display"
                        class="form-select selectpicker"
                        data-conditional-toggle="#newsletterBtnTxt, #newsletterBtnIcon"
                        data-conditional-value="icon_only, text_only"
                        data-conditional-logic="not-equal">
                        <option value="text_only" @selected(($options['button_display'] ?? 'text_only') == 'text_only')>{{ translate('Text Only') }}</option>
                        <option value="icon_only" @selected(($options['button_display'] ?? '') == 'icon_only')>{{ translate('Icon Only') }}</option>
                        <option value="both" @selected(($options['button_display'] ?? '') == 'both')>{{ translate('Icon + Text') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="button_style" class="form-select selectpicker" data-live-search="true">
                        <option value="primary" @selected(($options['button_style'] ?? 'primary') == 'primary')>{{ translate('Primary') }}</option>
                        <option value="secondary" @selected(($options['button_style'] ?? '') == 'secondary')>{{ translate('Secondary') }}</option>
                        <option value="success" @selected(($options['button_style'] ?? '') == 'success')>{{ translate('Success') }}</option>
                        <option value="dark" @selected(($options['button_style'] ?? '') == 'dark')>{{ translate('Dark') }}</option>
                        <option value="light" @selected(($options['button_style'] ?? '') == 'light')>{{ translate('Light') }}</option>
                        <option value="info" @selected(($options['button_style'] ?? '') == 'info')>{{ translate('Info') }}</option>
                        <option value="danger" @selected(($options['button_style'] ?? '') == 'danger')>{{ translate('Danger') }}</option>
                        <option value="outline-primary" @selected(($options['button_style'] ?? '') == 'outline-primary')>{{ translate('Outline Primary') }}</option>
                        <option value="outline-secondary" @selected(($options['button_style'] ?? '') == 'outline-secondary')>{{ translate('Outline Secondary') }}</option>
                        <option value="link" @selected(($options['button_style'] ?? '') == 'link')>{{ translate('Link (No BG)') }}</option>
                    </select>
                </div>
            </div>

            <div class="mb-3" id="newsletterBtnTxt">
                <label class="form-label">{{ translate('Button Text') }}</label>
                <input type="text" name="button_text" class="form-control" value="{{ $options['button_text'] ?? translate('Subscribe') }}">
            </div>

            <div class="mb-3" id="newsletterBtnIcon">
                <label class="form-label">{{ translate('Button Icon') }}</label>
                <select name="button_icon" class="form-select selectpicker" data-live-search="true">
                    <option value="">{{ translate('No Icon') }}</option>
                    @foreach($bootstrapIcons as $iconClass => $iconLabel)
                        <option value="{{ $iconClass }}"
                            data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                            @selected(($options['button_icon'] ?? 'bi-send') == $iconClass)>
                            {{ $iconLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            @break

        @case('footer_copyright')
            <div class="mb-3">
                <label class="form-label">{{ translate('Copyright Text') }}</label>
                <input type="text" name="copyright_text" class="form-control" value="{{ $options['copyright_text'] ?? '© {year} {site_name}. All rights reserved.' }}">
                <small class="form-text text-muted">{{ translate('Use {year} for current year and {site_name} for site name') }}</small>
            </div>
            @break

        @case('footer_payment_icons')
            <div class="mb-3">
                <label class="form-label">{{ translate('Heading') }}</label>
                <input type="text" name="heading" class="form-control" value="{{ $options['heading'] ?? translate('We Accept') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Payment Image') }}</label>
                <div class="input-group">
                    <input type="hidden" name="payment_image" value="{{ $options['payment_image'] ?? '' }}">
                    <input type="file" name="payment_image" class="form-control" accept="image/*">
                </div>
            </div>

             <div class="mb-3">
                <label class="form-label">{{ translate('Color Style') }}</label>
                <select name="color_style" class="form-select selectpicker">
                    <option value="original" @selected(($options['color_style'] ?? 'original') == 'original')>{{ translate('Original') }}</option>
                    <option value="monochrome" @selected(($options['color_style'] ?? '') == 'monochrome')>{{ translate('Monochrome') }}</option>
                </select>
            </div>
            @break

        @case('footer_app_download')
            <div class="mb-3">
                <label class="form-label">{{ translate('Section Title') }}</label>
                <input type="text" name="section_title" class="form-control" value="{{ $options['section_title'] ?? translate('Download App') }}">
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_qr" name="show_qr" @checked($options['show_qr'] ?? false)>
                    <label class="form-check-label" for="show_qr">{{ translate('Show QR Code') }}</label>
                </div>
            </div>
            @break

        @case('footer_widget_1')
        @case('footer_widget_2')
        @case('footer_widget_3')
        @case('footer_widget_4')
            <div class="alert alert-info">
                {{ translate('The content of this widget is managed in Appearance -> Widgets.') }}
            </div>
            @break

        @case('footer_divider')
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Divider Type') }}</label>
                    <select name="type"
                        class="form-select selectpicker"
                        data-conditional-toggle="#dividerWidth, #dividerHeight"
                        data-conditional-value="horizontal, vertical">
                        <option value="horizontal" @selected(($options['type'] ?? 'horizontal') == 'horizontal')>{{ translate('Horizontal') }}</option>
                        <option value="vertical" @selected(($options['type'] ?? '') == 'vertical')>{{ translate('Vertical') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Divider Style') }}</label>
                    <select name="style" class="form-select selectpicker">
                        <option value="solid" @selected(($options['style'] ?? 'solid') == 'solid')>{{ translate('Solid') }}</option>
                        <option value="dashed" @selected(($options['style'] ?? '') == 'dashed')>{{ translate('Dashed') }}</option>
                        <option value="dotted" @selected(($options['style'] ?? '') == 'dotted')>{{ translate('Dotted') }}</option>
                        <option value="double" @selected(($options['style'] ?? '') == 'double')>{{ translate('Double') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Divider Color') }}</label>
                    <div class="colorpicker">
                        <input type="text" name="color" class="form-control coloris" value="{{ $options['color'] ?? '#dee2e6' }}">
                    </div>
                </div>
                <div class="col-6 d-none" id="dividerWidth">
                    <label class="form-label">{{ translate('Width') }}</label>
                    <select name="width" class="form-select selectpicker">
                        <option value="25" @selected(($options['width'] ?? '') == '25')>25%</option>
                        <option value="50" @selected(($options['width'] ?? '') == '50')>50%</option>
                        <option value="75" @selected(($options['width'] ?? '') == '75')>75%</option>
                        <option value="100" @selected(($options['width'] ?? '100') == '100')>100%</option>
                    </select>
                </div>
                <div class="col-6 d-none" id="dividerHeight">
                    <label class="form-label">{{ translate('Height') }} <small class="text-muted">(px)</small></label>
                    <input type="number" name="height" class="form-control" value="{{ $options['height'] ?? '50' }}" min="1">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Thickness') }}</label>
                    <select name="thickness" class="form-select selectpicker">
                        <option value="1" @selected(($options['thickness'] ?? '1') == '1')>1px</option>
                        <option value="2" @selected(($options['thickness'] ?? '') == '2')>2px</option>
                        <option value="3" @selected(($options['thickness'] ?? '') == '3')>3px</option>
                        <option value="4" @selected(($options['thickness'] ?? '') == '4')>4px</option>
                        <option value="5" @selected(($options['thickness'] ?? '') == '5')>5px</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Margin') }}</label>
                    <select name="margin" class="form-select selectpicker">
                        <option value="1" @selected(($options['margin'] ?? '') == '1')>{{ translate('Extra Small') }}</option>
                        <option value="2" @selected(($options['margin'] ?? '') == '2')>{{ translate('Small') }}</option>
                        <option value="3" @selected(($options['margin'] ?? '3') == '3')>{{ translate('Medium') }}</option>
                        <option value="4" @selected(($options['margin'] ?? '') == '4')>{{ translate('Large') }}</option>
                        <option value="5" @selected(($options['margin'] ?? '') == '5')>{{ translate('Extra Large') }}</option>
                    </select>
                </div>
            </div>
            @break

        @case('footer_html')
            <div class="mb-3">
                <label class="form-label">{{ translate('HTML Content') }}</label>
                <textarea name="content" class="form-control" rows="6" placeholder="{{ translate('Enter custom HTML content...') }}">{{ $options['content'] ?? '' }}</textarea>
                <small class="form-text text-muted">{{ translate('You can use HTML, inline CSS, and text.') }}</small>
            </div>
            @break

        @case('footer_button')
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Button Text') }}</label>
                    <input type="text" name="btn_text" class="form-control" value="{{ $options['btn_text'] ?? translate('Button') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Button URL') }}</label>
                    <input type="text" name="url" class="form-control" value="{{ $options['url'] ?? '#' }}" placeholder="https://...">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="style" class="form-select selectpicker" data-live-search="true">
                        <option value="primary" @selected(($options['style'] ?? 'primary') == 'primary')>{{ translate('Primary') }}</option>
                        <option value="secondary" @selected(($options['style'] ?? '') == 'secondary')>{{ translate('Secondary') }}</option>
                        <option value="success" @selected(($options['style'] ?? '') == 'success')>{{ translate('Success') }}</option>
                        <option value="danger" @selected(($options['style'] ?? '') == 'danger')>{{ translate('Danger') }}</option>
                        <option value="warning" @selected(($options['style'] ?? '') == 'warning')>{{ translate('Warning') }}</option>
                        <option value="info" @selected(($options['style'] ?? '') == 'info')>{{ translate('Info') }}</option>
                        <option value="light" @selected(($options['style'] ?? '') == 'light')>{{ translate('Light') }}</option>
                        <option value="dark" @selected(($options['style'] ?? '') == 'dark')>{{ translate('Dark') }}</option>
                        <option value="outline-primary" @selected(($options['style'] ?? '') == 'outline-primary')>{{ translate('Outline Primary') }}</option>
                        <option value="outline-secondary" @selected(($options['style'] ?? '') == 'outline-secondary')>{{ translate('Outline Secondary') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Size') }}</label>
                    <select name="size" class="form-select selectpicker">
                        <option value="md" @selected(($options['size'] ?? 'md') == 'md')>{{ translate('Default') }}</option>
                        <option value="sm" @selected(($options['size'] ?? '') == 'sm')>{{ translate('Small') }}</option>
                        <option value="lg" @selected(($options['size'] ?? '') == 'lg')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon') }} <small class="text-muted">({{ translate('optional') }})</small></label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['icon'] ?? '') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Position') }}</label>
                    <select name="icon_position" class="form-select selectpicker">
                        <option value="left" @selected(($options['icon_position'] ?? 'left') == 'left')>{{ translate('Left') }}</option>
                        <option value="right" @selected(($options['icon_position'] ?? '') == 'right')>{{ translate('Right') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="new_tab" name="new_tab" @checked($options['new_tab'] ?? false)>
                        <label class="form-check-label" for="new_tab">{{ translate('Open in New Tab') }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="full_width" name="full_width" @checked($options['full_width'] ?? false)>
                        <label class="form-check-label" for="full_width">{{ translate('Full Width') }}</label>
                    </div>
                </div>
            </div>
            @break

        @case('footer_language')
            <div class="mb-3">
                <label class="form-label">{{ translate('Trigger Type') }}</label>
                <select name="trigger_type"
                    id="fl_trigger_type"
                    class="form-select selectpicker"
                    data-conditional-toggle="#fl_dropdown_content, #fl_lang_label, #fl_curr_label"
                    data-conditional-value="both, currency, language"
                    data-conditional-logic="not-equal">
                    <option value="both" @selected(($options['trigger_type'] ?? 'both') == 'both')>{{ translate('Both (Language & Currency)') }}</option>
                    <option value="language" @selected(($options['trigger_type'] ?? '') == 'language')>{{ translate('Language Only') }}</option>
                    <option value="currency" @selected(($options['trigger_type'] ?? '') == 'currency')>{{ translate('Currency Only') }}</option>
                </select>
            </div>

            <div class="mb-3" id="fl_lang_label">
                <label class="form-label">{{ translate('Language Trigger Label') }}</label>
                <select name="lang_format" class="form-select selectpicker">
                    <option value="code" @selected(($options['lang_format'] ?? 'code') == 'code')>{{ translate('Language Code (e.g. EN)') }}</option>
                    <option value="name" @selected(($options['lang_format'] ?? '') == 'name')>{{ translate('Language Name (e.g. English)') }}</option>
                    <option value="flag" @selected(($options['lang_format'] ?? '') == 'flag')>{{ translate('Flag Only') }}</option>
                    <option value="flag_code" @selected(($options['lang_format'] ?? '') == 'flag_code')>{{ translate('Flag + Code') }}</option>
                </select>
            </div>

            <div class="mb-3" id="fl_curr_label">
                <label class="form-label">{{ translate('Currency Trigger Label') }}</label>
                <select name="currency_format" class="form-select selectpicker">
                    <option value="code" @selected(($options['currency_format'] ?? 'code') == 'code')>{{ translate('Currency Code (e.g. USD)') }}</option>
                    <option value="symbol" @selected(($options['currency_format'] ?? '') == 'symbol')>{{ translate('Symbol (e.g. $)') }}</option>
                    <option value="symbol_code" @selected(($options['currency_format'] ?? '') == 'symbol_code')>{{ translate('Symbol + Code') }}</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Label Position') }}</label>
                    <select name="label_position" class="form-select selectpicker">
                        <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                        <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                        <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Tooltip') }}</option>
                        <option value="hidden" @selected(($options['label_position'] ?? '') == 'hidden')>{{ translate('Hide Label') }}</option>
                    </select>
                </div>
                <div class="col-6">
                     <label class="form-label">{{ translate('Trigger Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        <option value="bi-globe" @selected(($options['icon'] ?? 'bi-globe') == 'bi-globe')>Globe</option>
                        <option value="bi-translate" @selected(($options['icon'] ?? '') == 'bi-translate')>Translate</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['icon'] ?? '') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Display Style') }}</label>
                <select name="display_style" class="form-select selectpicker" data-conditional-toggle="#fl_dropdown_icon" data-conditional-value="dropdown" data-conditional-logic="equal">
                    <option value="dropdown" @selected(($options['display_style'] ?? 'dropdown') == 'dropdown')>{{ translate('Dropdown') }}</option>
                    <option value="modal" @selected(($options['display_style'] ?? '') == 'modal')>{{ translate('Modal') }}</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="fl_dropdown_content">
                <label class="form-label">{{ translate('Dropdown Content') }}</label>
                <select name="dropdown_content" class="form-select selectpicker">
                    <option value="respective" @selected(($options['dropdown_content'] ?? 'respective') == 'respective')>{{ translate('Show Single List') }}</option>
                    <option value="both" @selected(($options['dropdown_content'] ?? '') == 'both')>{{ translate('Show Both Lists') }}</option>
                </select>
                <p class="form-text mb-0">{{ translate('Choose if the dropdown should show only the triggers content or both.') }}</p>
            </div>

            <div class="mb-3 d-none" id="fl_dropdown_icon">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="hide_lc_drop_icon" name="hide_lc_drop_icon" @checked($options['hide_lc_drop_icon'] ?? false)>
                    <label class="form-check-label" for="hide_lc_drop_icon">{{ translate('Hide Dropdown Icon') }}</label>
                </div>
            </div>
            @break

        @case('footer_countdown')
            <div class="mb-3">
                <label class="form-label">{{ translate('Target Date & Time') }}</label>
                <input type="datetime-local" name="target_date" class="form-control" value="{{ $options['target_date'] ?? now()->addDays(7)->format('Y-m-d\TH:i') }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Label') }}</label>
                    <input type="text" name="label" class="form-control" value="{{ $options['label'] ?? '' }}" placeholder="{{ translate('Sale ends in:') }}">
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Label Icon') }}</label>
                    <select name="label_icon" class="form-select selectpicker" data-live-search="true">
                        <option value="" @selected(empty($options['label_icon']))>{{ translate('None') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['label_icon'] ?? '') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="show_label_top" value="0">
                        <input class="form-check-input" type="checkbox" id="show_label_top" name="show_label_top" value="1" @checked($options['show_label_top'] ?? false)>
                        <label class="form-check-label" for="show_label_top">{{ translate('Show Label on Top') }}</label>
                    </div>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Style') }}</label>
                    <select name="style" class="form-select selectpicker" data-conditional-toggle="#boxedStyleOptions" data-conditional-value="boxed">
                        <option value="inline" @selected(($options['style'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                        <option value="boxed" @selected(($options['style'] ?? '') == 'boxed')>{{ translate('Boxed') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Size') }}</label>
                    <select name="size" class="form-select selectpicker" data-conditional-toggle="#boxedStyleOptions" data-conditional-value="boxed">
                        <option value="default" @selected(($options['size'] ?? 'default') == 'default')>{{ translate('Default') }}</option>
                        <option value="fs-5" @selected(($options['size'] ?? '') == 'fs-5')>{{ translate('Medium') }}</option>
                        <option value="fs-4" @selected(($options['size'] ?? '') == 'fs-4')>{{ translate('Large') }}</option>
                        <option value="fs-3" @selected(($options['size'] ?? '') == 'fs-3')>{{ translate('Extra Large') }}</option>
                    </select>
                </div>
            </div>

            <div id="boxedStyleOptions" class="card card-body bg-light border-0 mb-3 {{ ($options['style'] ?? '') == 'boxed' ? '' : 'd-none' }}">
                <div class="mb-3">
                     <label class="form-label">{{ translate('Box Style') }}</label>
                    <select name="box_style" class="form-select selectpicker" data-live-search="true">
                        <option value="primary" @selected(($options['box_style'] ?? 'primary') == 'primary')>{{ translate('Primary') }}</option>
                        <option value="secondary" @selected(($options['box_style'] ?? '') == 'secondary')>{{ translate('Secondary') }}</option>
                        <option value="success" @selected(($options['box_style'] ?? '') == 'success')>{{ translate('Success') }}</option>
                        <option value="danger" @selected(($options['box_style'] ?? '') == 'danger')>{{ translate('Danger') }}</option>
                        <option value="warning" @selected(($options['box_style'] ?? '') == 'warning')>{{ translate('Warning') }}</option>
                        <option value="info" @selected(($options['box_style'] ?? '') == 'info')>{{ translate('Info') }}</option>
                        <option value="dark" @selected(($options['box_style'] ?? '') == 'dark')>{{ translate('Dark') }}</option>
                        <option value="light" @selected(($options['box_style'] ?? '') == 'light')>{{ translate('Light') }}</option>
                    </select>
                </div>
                 <div class="form-check form-switch">
                    <input type="hidden" name="match_label_style" value="0">
                    <input class="form-check-input" type="checkbox" id="match_label_style" name="match_label_style" value="1" @checked($options['match_label_style'] ?? false)>
                    <label class="form-check-label" for="match_label_style">{{ translate('Apply style to label') }}</label>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="show_days" value="0">
                        <input class="form-check-input" type="checkbox" id="show_days" name="show_days" value="1" @checked($options['show_days'] ?? true)>
                        <label class="form-check-label" for="show_days">{{ translate('Days') }}</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="show_hours" value="0">
                        <input class="form-check-input" type="checkbox" id="show_hours" name="show_hours" value="1" @checked($options['show_hours'] ?? true)>
                        <label class="form-check-label" for="show_hours">{{ translate('Hours') }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="show_minutes" value="0">
                        <input class="form-check-input" type="checkbox" id="show_minutes" name="show_minutes" value="1" @checked($options['show_minutes'] ?? true)>
                        <label class="form-check-label" for="show_minutes">{{ translate('Minutes') }}</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="show_seconds" value="0">
                        <input class="form-check-input" type="checkbox" id="show_seconds" name="show_seconds" value="1" @checked($options['show_seconds'] ?? true)>
                        <label class="form-check-label" for="show_seconds">{{ translate('Seconds') }}</label>
                    </div>
                </div>
            </div>
            @break

        @case('footer_search')
            <div class="mb-3">
                <label class="form-label">{{ translate('Placeholder') }}</label>
                <input type="text" name="placeholder" class="form-control" value="{{ $options['placeholder'] ?? translate('Search...') }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Style') }}</label>
                    <select name="style" class="form-select selectpicker">
                        <option value="inline" @selected(($options['style'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                        <option value="stacked" @selected(($options['style'] ?? '') == 'stacked')>{{ translate('Stacked') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Size') }}</label>
                    <select name="size" class="form-select selectpicker">
                        <option value="sm" @selected(($options['size'] ?? '') == 'sm')>{{ translate('Small') }}</option>
                        <option value="lg" @selected(($options['size'] ?? '') == 'lg')>{{ translate('Large') }}</option>
                        <option value="default" @selected(($options['size'] ?? 'default') == 'default')>{{ translate('Default') }}</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_button" value="0">
                    <input class="form-check-input" type="checkbox" id="show_button" name="show_button" @checked($options['show_button'] ?? true) data-slide-toggle="#searchBtnCont">
                    <label class="form-check-label" for="show_button">{{ translate('Show Search Button') }}</label>
                </div>
            </div>

            <div class="row g-3 mb-3 d-none" id="searchBtnCont">
                <div class="col-12">
                    <label class="form-label">{{ translate('Button Text') }}</label>
                    <input type="text" name="button_text" class="form-control" value="{{ $options['button_text'] ?? '' }}" placeholder="{{ translate('Leave empty for icon only') }}">
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Icon') }}</label>
                    <select name="button_icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['button_icon'] ?? 'bi-search') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="search_btn_style" class="form-select selectpicker" data-live-search="true">
                        <option value="primary" @selected(($options['search_btn_style'] ?? 'primary') == 'primary')>{{ translate('Primary') }}</option>
                        <option value="secondary" @selected(($options['search_btn_style'] ?? '') == 'secondary')>{{ translate('Secondary') }}</option>
                        <option value="success" @selected(($options['search_btn_style'] ?? '') == 'success')>{{ translate('Success') }}</option>
                        <option value="danger" @selected(($options['search_btn_style'] ?? '') == 'danger')>{{ translate('Danger') }}</option>
                        <option value="warning" @selected(($options['search_btn_style'] ?? '') == 'warning')>{{ translate('Warning') }}</option>
                        <option value="info" @selected(($options['search_btn_style'] ?? '') == 'info')>{{ translate('Info') }}</option>
                        <option value="light" @selected(($options['search_btn_style'] ?? '') == 'light')>{{ translate('Light') }}</option>
                        <option value="dark" @selected(($options['search_btn_style'] ?? '') == 'dark')>{{ translate('Dark') }}</option>
                        <option value="outline-primary" @selected(($options['search_btn_style'] ?? '') == 'outline-primary')>{{ translate('Outline Primary') }}</option>
                        <option value="outline-secondary" @selected(($options['search_btn_style'] ?? '') == 'outline-secondary')>{{ translate('Outline Secondary') }}</option>
                    </select>
                </div>
            </div>
            @break
    @endswitch

    {{-- Common Settings --}}
    <hr class="my-3">
    <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Display Settings') }}</h6>

    <div class="mb-3">
        <label class="form-label">{{ translate('Visibility') }}</label>
        <select name="visibility" class="form-select selectpicker">
            <option value="all" @selected(($options['visibility'] ?? 'all') == 'all')>{{ translate('All Devices') }}</option>
            <option value="desktop" @selected(($options['visibility'] ?? '') == 'desktop')>{{ translate('Desktop Only') }}</option>
            <option value="mobile" @selected(($options['visibility'] ?? '') == 'mobile')>{{ translate('Mobile Only') }}</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">{{ translate('Custom CSS Class') }}</label>
        <input type="text" name="custom_class" class="form-control" value="{{ $options['custom_class'] ?? '' }}" placeholder="e.g. my-custom-class">
    </div>

    <div class="form-check form-switch">
        <input type="hidden" name="status" value="0">
        <input class="form-check-input" type="checkbox" id="block_status" name="status" value="1" @checked($isActive)>
        <label class="form-check-label" for="block_status">{{ translate('Active :block', ['block' => $footerBlock->title ?? '']) }}</label>
    </div>
</form>
