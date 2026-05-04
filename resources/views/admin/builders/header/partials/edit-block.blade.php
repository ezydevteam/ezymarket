@php
    $options = $headerBlock->options ?? [];
    $isActive = ($options['status'] ?? 1) == 1;
@endphp

<form
    id="editBlockForm"
    action="{{ route('admin.builders.header.edit-block', $headerBlock->id) }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    <div class="mb-3">
        <label class="form-label">{{ translate('Title') }}</label>
        <input type="text" name="title" class="form-control" value="{{ $headerBlock->title }}" required />
    </div>

    {{-- Element-Specific Settings --}}
    @switch($headerBlock->id)
        @case('header_logo')
            <div class="mb-3">
                <label class="form-label">{{ translate('Logo Style') }}</label>
                <select name="logo_style" class="form-select selectpicker" data-conditional-toggle="#logoDimension" data-conditional-value="site_title" data-conditional-logic="not-equal">
                    <option value="logo_dark" @selected(($options['logo_style'] ?? 'logo_dark') == 'logo_dark')>{{ translate('Dark Logo') }}</option>
                    <option value="logo_light" @selected(($options['logo_style'] ?? '') == 'logo_light')>{{ translate('Light Logo') }}</option>
                    <option value="site_title" @selected(($options['logo_style'] ?? '') == 'site_title')>{{ translate('Site Title') }}</option>
                </select>
                <p class="form-text mb-0">{{ translate('Set logo from appearance->themes->general') }}</p>
            </div>
            <div class="row g-3 d-none" id="logoDimension">
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

        @case('header_menu')
            <div class="mb-3">
                <label class="form-label">{{ translate('Menu Location') }}</label>
                <select name="menu_location" class="form-select selectpicker">
                    <option value="top" @selected(($options['menu_location'] ?? 'top') == 'top')>{{ translate('Top Navigation') }}</option>
                    <option value="bottom" @selected(($options['menu_location'] ?? '') == 'bottom')>{{ translate('Bottom Navigation') }}</option>
                    <option value="mobile" @selected(($options['menu_location'] ?? '') == 'mobile')>{{ translate('Mobile Navigation') }}</option>
                    <option value="footer" @selected(($options['menu_location'] ?? '') == 'footer')>{{ translate('Footer Menu') }}</option>
                </select>
                <p class="form-text mb-0">{{ translate('Menu from this location will be displayed here.') }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Menu Style') }}</label>
                <select name="menu_style" class="form-select selectpicker" data-conditional-toggle="#verticalMenuOps" data-conditional-value="vertical" data-conditional-logic="equal">
                    <option value="horizontal" @selected(($options['menu_style'] ?? 'horizontal') == 'horizontal')>{{ translate('Horizontal') }}</option>
                    <option value="vertical" @selected(($options['menu_style'] ?? '') == 'vertical')>{{ translate('Vertical') }}</option>
                </select>
            </div>

            <div class="row g-3 mb-3 d-none" id="verticalMenuOps">
                <h6 class="text-uppercase small text-muted fw-bold mb-1">{{ translate('Vertical Menu Styling') }}</h6>
                 <div class="col-6">
                    <label class="form-label">{{ translate('Vertical Menu Label') }}</label>
                    <input type="text" name="vertical_menu_label" class="form-control" value="{{ $options['vertical_menu_label'] ?? translate('Browse Categories') }}" placeholder="{{ translate('All Categories') }}">
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Menu Icon') }}</label>
                     <select name="vertical_menu_icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['vertical_menu_icon'] ?? '') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                 <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="btn_style" class="form-select selectpicker" data-live-search="true">
                        <option value="primary" @selected(($options['btn_style'] ?? 'primary') == 'primary')>Primary</option>
                        <option value="secondary" @selected(($options['btn_style'] ?? '') == 'secondary')>Secondary</option>
                        <option value="success" @selected(($options['btn_style'] ?? '') == 'success')>Success</option>
                        <option value="danger" @selected(($options['btn_style'] ?? '') == 'danger')>Danger</option>
                        <option value="warning" @selected(($options['btn_style'] ?? '') == 'warning')>Warning</option>
                        <option value="info" @selected(($options['btn_style'] ?? '') == 'info')>Info</option>
                        <option value="light" @selected(($options['btn_style'] ?? '') == 'light')>Light</option>
                        <option value="dark" @selected(($options['btn_style'] ?? '') == 'dark')>Dark</option>
                        <option value="outline-primary" @selected(($options['btn_style'] ?? '') == 'outline-primary')>Outline Primary</option>
                        <option value="outline-secondary" @selected(($options['btn_style'] ?? '') == 'outline-secondary')>Outline Secondary</option>
                        <option value="link" @selected(($options['btn_style'] ?? '') == 'link')>Link</option>
                        <option value="none" @selected(($options['btn_style'] ?? '') == 'none')>None (Clean)</option>
                    </select>
                </div>

                 <div class="col-6">
                    <label class="form-label">{{ translate('Button Size') }}</label>
                    <select name="btn_size" class="form-select selectpicker">
                        <option value="" @selected(($options['btn_size'] ?? '') == '')>Default</option>
                        <option value="sm" @selected(($options['btn_size'] ?? '') == 'sm')>Small</option>
                        <option value="md" @selected(($options['btn_size'] ?? '') == 'md')>Medium</option>
                        <option value="lg" @selected(($options['btn_size'] ?? '') == 'lg')>Large</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="initially_open" value="0">
                        <input class="form-check-input" type="checkbox" id="initially_open" name="initially_open" value="1" @checked($options['initially_open'] ?? false)>
                        <label class="form-check-label" for="initially_open">{{ translate('Opened Initially') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="vr_hide_dropdown_icon" value="0">
                        <input class="form-check-input" type="checkbox" id="vr_hide_dropdown_icon" name="vr_hide_dropdown_icon" value="1" @checked($options['vr_hide_dropdown_icon'] ?? false)>
                        <label class="form-check-label" for="vr_hide_dropdown_icon">{{ translate('Hide Dropdown Icon') }}</label>
                    </div>
                </div>
            </div>

            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Main Menu Styling') }}</h6>

            <div class="mb-3">
                <label class="form-label">{{ translate('Trigger Type') }}</label>
                <select name="trigger_type" class="form-select selectpicker">
                    <option value="hover" @selected(($options['trigger_type'] ?? 'hover') == 'hover')>{{ translate('Hoverable') }}</option>
                    <option value="click" @selected(($options['trigger_type'] ?? '') == 'click')>{{ translate('Clickable') }}</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6 colorpicker">
                    <label class="form-label">{{ translate('Text Color') }}</label>
                    <input type="text" name="text_color" class="form-control coloris" value="{{ $options['text_color'] ?? '' }}">
                </div>

                 <div class="col-6 colorpicker">
                    <label class="form-label">{{ translate('Hover Text Color') }}</label>
                     <input type="text" name="hover_text_color" class="form-control coloris" value="{{ $options['hover_text_color'] ?? '' }}">
                 </div>

                 <div class="col-6 colorpicker">
                    <label class="form-label">{{ translate('Hover Background Color') }}</label>
                     <input type="text" name="hover_bg_color" class="form-control coloris" value="{{ $options['hover_bg_color'] ?? '' }}">
                 </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Hover Style') }}</label>
                    <select name="hover_style" class="form-select selectpicker">
                        <option value="none" @selected(($options['hover_style'] ?? 'none') == 'none')>{{ translate('None') }}</option>
                        <option value="underline" @selected(($options['hover_style'] ?? '') == 'underline')>{{ translate('Underline') }}</option>
                        <option value="border_top" @selected(($options['hover_style'] ?? '') == 'border_top')>{{ translate('Border Top') }}</option>
                        <option value="border_top_bottom" @selected(($options['hover_style'] ?? '') == 'border_top_bottom')>{{ translate('Border Top & Bottom') }}</option>
                        <option value="background" @selected(($options['hover_style'] ?? '') == 'background')>{{ translate('Background Pill') }}</option>
                        <option value="background_rounded" @selected(($options['hover_style'] ?? '') == 'background_rounded')>{{ translate('Background Rounded') }}</option>
                        <option value="glow" @selected(($options['hover_style'] ?? '') == 'glow')>{{ translate('Glow Background') }}</option>
                        <option value="parallelogram" @selected(($options['hover_style'] ?? '') == 'parallelogram')>{{ translate('Parallelogram') }}</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                     <label class="form-label">{{ translate('Font Size') }}</label>
                     <div class="input-group">
                        <input type="number" name="font_size" class="form-control" value="{{ $options['font_size'] ?? '' }}">
                        <span class="input-group-text">px</span>
                     </div>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Font Weight') }}</label>
                    <select name="font_weight" class="form-select selectpicker">
                        <option value="" @selected(($options['font_weight'] ?? '') == '')>{{ translate('Default') }}</option>
                        <option value="400" @selected(($options['font_weight'] ?? '') == '400')>Normal</option>
                        <option value="500" @selected(($options['font_weight'] ?? '') == '500')>Medium</option>
                        <option value="600" @selected(($options['font_weight'] ?? '') == '600')>Semi Bold</option>
                        <option value="700" @selected(($options['font_weight'] ?? '') == '700')>Bold</option>
                    </select>
                </div>
            </div>

           <div class="row g-3 mb-3">
                <div class="col-6">
                     <label class="form-label">{{ translate('Horizontal Padding') }}</label>
                     <div class="input-group">
                        <input type="number" name="padding_x" class="form-control" value="{{ $options['padding_x'] ?? '' }}">
                        <span class="input-group-text">px</span>
                     </div>
                </div>
                 <div class="col-6">
                     <label class="form-label">{{ translate('Vertical Padding') }}</label>
                     <div class="input-group">
                        <input type="number" name="padding_y" class="form-control" value="{{ $options['padding_y'] ?? '' }}">
                        <span class="input-group-text">px</span>
                     </div>
                </div>
           </div>

            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Dropdown Styling') }}</h6>

            <div class="row g-3 mb-3">
                 <div class="col-6 colorpicker">
                    <label class="form-label">{{ translate('Dropdown Background') }}</label>
                     <input type="text" name="dropdown_bg" class="form-control coloris" value="{{ $options['dropdown_bg'] ?? '' }}">
                 </div>
                  <div class="col-6 colorpicker">
                    <label class="form-label">{{ translate('Dropdown Text Color') }}</label>
                     <input type="text" name="dropdown_color" class="form-control coloris" value="{{ $options['dropdown_color'] ?? '' }}">
                 </div>
                 <div class="col-6 colorpicker">
                    <label class="form-label">{{ translate('Hover Background') }}</label>
                     <input type="text" name="dropdown_hover_bg" class="form-control coloris" value="{{ $options['dropdown_hover_bg'] ?? '' }}">
                 </div>
                   <div class="col-6 colorpicker">
                    <label class="form-label">{{ translate('Hover Text Color') }}</label>
                     <input type="text" name="dropdown_hover_color" class="form-control coloris" value="{{ $options['dropdown_hover_color'] ?? '' }}">
                 </div>
                 <div class="col-12">
                     <label class="form-label">{{ translate('Dropdown Padding') }}</label>
                     <div class="input-group">
                        <input type="number" name="dropdown_padding" class="form-control" value="{{ $options['dropdown_padding'] ?? '' }}">
                        <span class="input-group-text">px</span>
                     </div>
                 </div>
            </div>
             <div class="mb-3">
                <label class="form-label">{{ translate('Dropdown Hover Style') }}</label>
                <select name="dropdown_hover_style" class="form-select selectpicker">
                    <option value="none" @selected(($options['dropdown_hover_style'] ?? 'none') == 'none')>{{ translate('Color Only') }}</option>
                    <option value="underline" @selected(($options['dropdown_hover_style'] ?? '') == 'underline')>{{ translate('Underline') }}</option>
                    <option value="background_rounded" @selected(($options['dropdown_hover_style'] ?? '') == 'background_rounded')>{{ translate('Background Rounded') }}</option>
                    <option value="background_pill" @selected(($options['dropdown_hover_style'] ?? '') == 'background_pill')>{{ translate('Background Pill') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="hide_dropdown_icon" value="0">
                    <input class="form-check-input" type="checkbox" id="hide_dropdown_icon_menu" name="hide_dropdown_icon" value="1" @checked($options['hide_dropdown_icon'] ?? false)>
                    <label class="form-check-label" for="hide_dropdown_icon_menu">{{ translate('Hide Dropdown Arrow Icon') }}</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_dropdown_border" value="0">
                    <input class="form-check-input" type="checkbox" id="show_dropdown_border" name="show_dropdown_border" value="1" @checked($options['show_dropdown_border'] ?? true)>
                    <label class="form-check-label" for="show_dropdown_border">{{ translate('Show Dropdown Item Bottom Border') }}</label>
                </div>
            </div>
            @break

        @case('header_search')
            <div class="mb-3">
                <label class="form-label">{{ translate('Search Style') }}</label>
                <select name="search_style" class="form-select selectpicker" data-conditional-toggle="#searchTriggerOptions" data-conditional-value="standard" data-conditional-logic="not-equal">
                    <option value="standard" @selected(($options['search_style'] ?? 'standard') == 'standard')>{{ translate('Standard') }}</option>
                    <option value="expandable" @selected(($options['search_style'] ?? '') == 'expandable')>{{ translate('Expandable') }}</option>
                    <option value="full_width" @selected(($options['search_style'] ?? '') == 'full_width')>{{ translate('Full Width Overlay') }}</option>
                    <option value="modal" @selected(($options['search_style'] ?? '') == 'modal')>{{ translate('Modal Popup') }}</option>
                </select>
            </div>

            {{-- Trigger Options (For Expandable/Modal) --}}
            <div id="searchTriggerOptions" class="card card-body bg-light border-0 mb-3 d-none">
                 <div class="mb-3">
                    <label class="form-label">{{ translate('Trigger Display Mode') }}</label>
                    <select name="trigger_display_mode" class="form-select selectpicker" data-conditional-toggle="#tiggerText" data-conditional-value="icon" data-conditional-logic="not-equal">
                        <option value="icon" @selected(($options['trigger_display_mode'] ?? 'icon') == 'icon')>{{ translate('Icon Only') }}</option>
                        <option value="text" @selected(($options['trigger_display_mode'] ?? '') == 'text')>{{ translate('Text Only') }}</option>
                        <option value="icon_text" @selected(($options['trigger_display_mode'] ?? '') == 'icon_text')>{{ translate('Icon + Text') }}</option>
                    </select>
                </div>
                <div class="mb-3 d-none" id="tiggerText">
                    <label class="form-label">{{ translate('Trigger Label') }}</label>
                    <input type="text" name="trigger_text" class="form-control" value="{{ $options['trigger_text'] ?? translate('Search') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ translate('Label Position') }}</label>
                    <select name="trigger_icon_position" class="form-select selectpicker">
                        <option value="left" @selected(($options['trigger_icon_position'] ?? 'left') == 'left')>{{ translate('Left') }}</option>
                        <option value="right" @selected(($options['trigger_icon_position'] ?? '') == 'right')>{{ translate('Right') }}</option>
                        <option value="bottom" @selected(($options['trigger_icon_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="trigger_icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['trigger_icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['trigger_icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['trigger_icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['trigger_icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
            </div>

            {{-- Standard Input Options --}}
            <div class="mb-3">
                <label class="form-label">{{ translate('Placeholder Text') }}</label>
                <input type="text" name="placeholder" class="form-control" value="{{ $options['placeholder'] ?? translate('Search...') }}">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label d-block">{{ translate('Input Background Color') }}</label>
                    <input type="text" name="input_bg_color" class="form-control coloris" value="{{ $options['input_bg_color'] ?? '' }}">
                </div>
                <div class="col-6">
                    <label class="form-label d-block">{{ translate('Input Text Color') }}</label>
                    <input type="text" name="input_text_color" class="form-control coloris" value="{{ $options['input_text_color'] ?? '' }}">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="input_transparent" value="0">
                    <input class="form-check-input" type="checkbox" id="input_transparent" name="input_transparent" value="1" @checked($options['input_transparent'] ?? false)>
                    <label class="form-check-label" for="input_transparent">{{ translate('Transparent Input Background (Border Only)') }}</label>
                </div>
            </div>

            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Button Style') }}</h6>

            <div class="mb-3">
                <label class="form-label">{{ translate('Button Position') }}</label>
                <select name="search_btn_position" class="form-select selectpicker">
                    <option value="none" @selected(($options['search_btn_position'] ?? '') == 'none')>{{ translate('No Button') }}</option>
                    <option value="left" @selected(($options['search_btn_position'] ?? '') == 'left')>{{ translate('Left') }}</option>
                    <option value="right" @selected(($options['search_btn_position'] ?? 'right') == 'right')>{{ translate('Right') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_btn_text" value="0">
                    <input class="form-check-input" type="checkbox" id="show_btn_text" name="show_btn_text" value="1" @checked($options['show_btn_text'] ?? false)>
                    <label class="form-check-label" for="show_btn_text">{{ translate('Button with Text') }}</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label d-block">{{ translate('Button Background') }}</label>
                <input type="text" name="btn_bg_color" class="form-control coloris" value="{{ $options['btn_bg_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="btn_transparent" value="0">
                    <input class="form-check-input" type="checkbox" id="btn_transparent" name="btn_transparent" value="1" @checked($options['btn_transparent'] ?? false)>
                    <label class="form-check-label" for="btn_transparent">{{ translate('Transparent Button Background (Border Only)') }}</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Button Icon') }}</label>
                 <select name="btn_icon" class="form-select selectpicker">
                    <option value="bi-search" @selected(($options['btn_icon'] ?? 'bi-search') == 'bi-search')>Magnifier</option>
                    <option value="bi-arrow-right" @selected(($options['btn_icon'] ?? '') == 'bi-arrow-right')>Arrow Right</option>
                    <option value="bi-send" @selected(($options['btn_icon'] ?? '') == 'bi-send')>Send</option>
                </select>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="live_search" value="0">
                    <input class="form-check-input" type="checkbox" id="live_search" name="live_search" value="1" @checked($options['live_search'] ?? false)>
                    <label class="form-check-label" for="live_search">{{ translate('Enable Live Search') }}</label>
                </div>
            </div>
            @break

        @case('header_auth')
            <div class="row g-3">
                <div class="col-12">
                   <h6 class="fw-bold mt-2">{{ translate('Login Settings') }}</h6>
                </div>
                {{-- Login Trigger --}}
                <div class="col-6">
                    <label class="form-label">{{ translate('Action Type') }}</label>
                    <select name="login_trigger_type" class="form-select selectpicker">
                        <option value="link" @selected(($options['login_trigger_type'] ?? 'link') == 'link')>{{ translate('Full Page') }}</option>
                        <option value="modal" @selected(($options['login_trigger_type'] ?? '') == 'modal')>{{ translate('Modal') }}</option>
                    </select>
                </div>
                {{-- Login Style --}}
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="login_btn_style" class="form-select selectpicker">
                        <option value="primary" @selected(($options['login_btn_style'] ?? 'primary') == 'primary')>Primary</option>
                        <option value="secondary" @selected(($options['login_btn_style'] ?? '') == 'secondary')>Secondary</option>
                        <option value="success" @selected(($options['login_btn_style'] ?? '') == 'success')>Success</option>
                        <option value="danger" @selected(($options['login_btn_style'] ?? '') == 'danger')>Danger</option>
                        <option value="warning" @selected(($options['login_btn_style'] ?? '') == 'warning')>Warning</option>
                        <option value="info" @selected(($options['login_btn_style'] ?? '') == 'info')>Info</option>
                        <option value="light" @selected(($options['login_btn_style'] ?? '') == 'light')>Light</option>
                        <option value="dark" @selected(($options['login_btn_style'] ?? '') == 'dark')>Dark</option>
                        <option value="outline-primary" @selected(($options['login_btn_style'] ?? '') == 'outline-primary')>Outline Primary</option>
                        <option value="outline-secondary" @selected(($options['login_btn_style'] ?? '') == 'outline-secondary')>Outline Secondary</option>
                        <option value="link" @selected(($options['login_btn_style'] ?? '') == 'link')>Link</option>
                        <option value="none" @selected(($options['login_btn_style'] ?? '') == 'none')>None (Clean)</option>
                    </select>
                </div>

                {{-- Login Display Mode --}}
                <div class="col-6">
                    <label class="form-label">{{ translate('Display Mode') }}</label>
                    <select name="login_display_mode" class="form-select selectpicker" data-conditional-toggle="#loginLabelText" data-conditional-value="icon" data-conditional-logic="not-equal">
                        <option value="icon" @selected(($options['login_display_mode'] ?? 'icon') == 'icon')>{{ translate('Icon Only') }}</option>
                        <option value="text" @selected(($options['login_display_mode'] ?? '') == 'text')>{{ translate('Text Only') }}</option>
                        <option value="icon_text" @selected(($options['login_display_mode'] ?? '') == 'icon_text')>{{ translate('Icon + Text (Inline)') }}</option>
                        <option value="icon_text_bottom" @selected(($options['login_display_mode'] ?? '') == 'icon_text_bottom')>{{ translate('Icon + Text (Bottom)') }}</option>
                        <option value="icon_text_tooltip" @selected(($options['login_display_mode'] ?? '') == 'icon_text_tooltip')>{{ translate('Icon + Tooltip') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Icon') }}</label>
                    <select name="login_icon" class="form-select selectpicker">
                        <option value="bi-box-arrow-in-right" @selected(($options['login_icon'] ?? 'bi-box-arrow-in-right') == 'bi-box-arrow-in-right')>Login Box</option>
                        <option value="bi-person" @selected(($options['login_icon'] ?? '') == 'bi-person')>Person</option>
                        <option value="bi-person-circle" @selected(($options['login_icon'] ?? '') == 'bi-person-circle')>Person Circle</option>
                        <option value="bi-lock" @selected(($options['login_icon'] ?? '') == 'bi-lock')>Lock</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="login_icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['login_icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['login_icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['login_icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['login_icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-12" id="loginLabelText"
                    <label class="form-label">{{ translate('Label Text') }}</label>
                    <input type="text" name="login_text" class="form-control" value="{{ $options['login_text'] ?? translate('Login') }}">
                </div>

                {{-- Login Redirect --}}
                <div class="col-12">
                    <label class="form-label">{{ translate('Redirect After Login') }}</label>
                    <select name="login_redirect" class="form-select selectpicker" data-conditional-toggle="#loginCustomUrl" data-conditional-value="custom" data-conditional-logic="equal">
                        <option value="same_page" @selected(($options['login_redirect'] ?? 'same_page') == 'same_page')>{{ translate('Same Page') }}</option>
                        <option value="home" @selected(($options['login_redirect'] ?? '') == 'home')>{{ translate('Home') }}</option>
                        <option value="profile" @selected(($options['login_redirect'] ?? '') == 'profile')>{{ translate('User Profile') }}</option>
                        <option value="dashboard" @selected(($options['login_redirect'] ?? '') == 'dashboard')>{{ translate('Dashboard') }}</option>
                        <option value="custom" @selected(($options['login_redirect'] ?? '') == 'custom')>{{ translate('Custom URL') }}</option>
                    </select>
                </div>
                <div class="col-12 d-none" id="loginCustomUrl">
                    <label class="form-label">{{ translate('Custom Login URL') }}</label>
                    <input type="text" name="login_redirect_url" class="form-control" value="{{ $options['login_redirect_url'] ?? '' }}" placeholder="https://example.com/page">
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12">
                   <h6 class="fw-bold mt-2">{{ translate('Register Settings') }}</h6>
                </div>
                 <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="show_register_btn" value="0">
                        <input class="form-check-input" type="checkbox" id="show_register_btn" name="show_register_btn" value="1" @checked($options['show_register_btn'] ?? true) data-slide-toggle="#registrationOptions">
                        <label class="form-check-label" for="show_register_btn">{{ translate('Show Register Button') }}</label>
                    </div>
                    <small class="text-muted">{{ translate('Overrides global registration setting if disabled.') }}</small>
                </div>
            </div>

            <div class="row g-3 mt-1 @if(!($options['show_register_btn'] ?? true)) d-none @endif" id="registrationOptions">
                {{-- Register Trigger --}}
                <div class="col-6">
                    <label class="form-label">{{ translate('Action Type') }}</label>
                    <select name="register_trigger_type" class="form-select selectpicker">
                        <option value="link" @selected(($options['register_trigger_type'] ?? 'link') == 'link')>{{ translate('Full Page') }}</option>
                        <option value="modal" @selected(($options['register_trigger_type'] ?? '') == 'modal')>{{ translate('Modal') }}</option>
                    </select>
                </div>
                {{-- Register Style --}}
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="register_btn_style" class="form-select selectpicker">
                        <option value="primary" @selected(($options['register_btn_style'] ?? 'primary') == 'primary')>Primary</option>
                        <option value="secondary" @selected(($options['register_btn_style'] ?? '') == 'secondary')>Secondary</option>
                        <option value="success" @selected(($options['register_btn_style'] ?? '') == 'success')>Success</option>
                        <option value="danger" @selected(($options['register_btn_style'] ?? '') == 'danger')>Danger</option>
                        <option value="warning" @selected(($options['register_btn_style'] ?? '') == 'warning')>Warning</option>
                        <option value="info" @selected(($options['register_btn_style'] ?? '') == 'info')>Info</option>
                        <option value="light" @selected(($options['register_btn_style'] ?? '') == 'light')>Light</option>
                        <option value="dark" @selected(($options['register_btn_style'] ?? '') == 'dark')>Dark</option>
                        <option value="outline-primary" @selected(($options['register_btn_style'] ?? '') == 'outline-primary')>Outline Primary</option>
                        <option value="outline-secondary" @selected(($options['register_btn_style'] ?? '') == 'outline-secondary')>Outline Secondary</option>
                        <option value="link" @selected(($options['register_btn_style'] ?? '') == 'link')>Link</option>
                        <option value="none" @selected(($options['register_btn_style'] ?? '') == 'none')>None (Clean)</option>
                    </select>
                </div>

                {{-- Register Display Mode --}}
                <div class="col-6">
                    <label class="form-label">{{ translate('Display Mode') }}</label>
                    <select name="register_display_mode" class="form-select selectpicker" data-conditional-toggle="#registerLabelText" data-conditional-value="text" data-conditional-logic="not-equal">
                        <option value="icon" @selected(($options['register_display_mode'] ?? 'icon') == 'icon')>{{ translate('Icon Only') }}</option>
                        <option value="text" @selected(($options['register_display_mode'] ?? '') == 'text')>{{ translate('Text Only') }}</option>
                        <option value="icon_text" @selected(($options['register_display_mode'] ?? '') == 'icon_text')>{{ translate('Icon + Text (Inline)') }}</option>
                        <option value="icon_text_bottom" @selected(($options['register_display_mode'] ?? '') == 'icon_text_bottom')>{{ translate('Icon + Text (Bottom)') }}</option>
                        <option value="icon_text_tooltip" @selected(($options['register_display_mode'] ?? '') == 'icon_text_tooltip')>{{ translate('Icon + Tooltip') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Icon') }}</label>
                    <select name="register_icon" class="form-select selectpicker">
                        <option value="bi-person-plus" @selected(($options['register_icon'] ?? 'bi-person-plus') == 'bi-person-plus')>Add Person</option>
                        <option value="bi-person-check" @selected(($options['register_icon'] ?? '') == 'bi-person-check')>Person Check</option>
                        <option value="bi-pencil-square" @selected(($options['register_icon'] ?? '') == 'bi-pencil-square')>Pencil</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="register_icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['register_icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['register_icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['register_icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['register_icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-12" id="registerLabelText">
                    <label class="form-label">{{ translate('Label Text') }}</label>
                    <input type="text" name="register_text" class="form-control" value="{{ $options['register_text'] ?? translate('Register') }}">
                </div>

                {{-- Register Redirect --}}
                <div class="col-12">
                    <label class="form-label">{{ translate('Redirect After Register') }}</label>
                    <select name="register_redirect" class="form-select selectpicker" data-conditional-toggle="#registerCustomUrl" data-conditional-value="custom" data-conditional-logic="equal">
                         <option value="same_page" @selected(($options['register_redirect'] ?? 'same_page') == 'same_page')>{{ translate('Same Page') }}</option>
                        <option value="home" @selected(($options['register_redirect'] ?? '') == 'home')>{{ translate('Home') }}</option>
                        <option value="profile" @selected(($options['register_redirect'] ?? '') == 'profile')>{{ translate('User Profile') }}</option>
                        <option value="dashboard" @selected(($options['register_redirect'] ?? '') == 'dashboard')>{{ translate('Dashboard') }}</option>
                        <option value="custom" @selected(($options['register_redirect'] ?? '') == 'custom')>{{ translate('Custom URL') }}</option>
                    </select>
                </div>
                 <div class="col-12 d-none" id="registerCustomUrl">
                    <label class="form-label">{{ translate('Custom Register URL') }}</label>
                    <input type="text" name="register_redirect_url" class="form-control" value="{{ $options['register_redirect_url'] ?? '' }}" placeholder="https://example.com/page">
                </div>
            </div>

              <div class="mb-3">
                  <label class="form-label">{{ translate('Logged In User Display') }}</label>
                  <select name="auth_display" class="form-select selectpicker">
                      <option value="avatar_name" @selected(($options['auth_display'] ?? 'avatar_name') == 'avatar_name')>{{ translate('Avatar + Name (Inline)') }}</option>
                      <option value="avatar_name_bottom" @selected(($options['auth_display'] ?? '') == 'avatar_name_bottom')>{{ translate('Avatar + Name (Bottom)') }}</option>
                      <option value="avatar_only" @selected(($options['auth_display'] ?? '') == 'avatar_only')>{{ translate('Avatar Only') }}</option>
                  </select>
              </div>
            @break

        @case('header_cart')
            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Behavior & Display') }}</h6>

            <div class="mb-3">
                <label class="form-label">{{ translate('View Mode') }}</label>
                <select name="view_mode" class="form-select selectpicker">
                    <option value="page" @selected(($options['view_mode'] ?? 'page') == 'page')>{{ translate('Full Page') }}</option>
                    <option value="offcanvas" @selected(($options['view_mode'] ?? '') == 'offcanvas')>{{ translate('Offcanvas') }}</option>
                </select>
            </div>

            <div class="mb-3" id="cartLabel">
                <label class="form-label">{{ translate('Cart Label') }}</label>
                <input type="text" name="cart_label" class="form-control" value="{{ $options['cart_label'] ?? '' }}" placeholder="{{ translate('Cart') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Label Position') }}</label>
                <select name="label_position" class="form-select selectpicker" data-conditional-toggle="#cartLabel" data-conditional-value="none" data-conditional-logic="not-equal">
                    <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                    <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                    <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Tooltip') }}</option>
                    <option value="none" @selected(($options['label_position'] ?? '') == 'none')>{{ translate('Hidden') }}</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Cart Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['icon'] ?? 'bi-cart3') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <input type="text" name="icon_color" class="form-control coloris" value="{{ $options['icon_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="form-check form-switch">
                        <input type="hidden" name="show_count" value="0">
                        <input class="form-check-input" type="checkbox" id="show_count" name="show_count" value="1" @checked($options['show_count'] ?? true)>
                        <label class="form-check-label" for="show_count">{{ translate('Show Item Count') }}</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check form-switch">
                        <input type="hidden" name="show_total" value="0">
                        <input class="form-check-input" type="checkbox" id="show_total" name="show_total" value="1" @checked($options['show_total'] ?? false)>
                        <label class="form-check-label" for="show_total">{{ translate('Show Cart Total') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="require_login" value="0">
                        <input class="form-check-input" type="checkbox" id="require_login" name="require_login" value="1" @checked($options['require_login'] ?? false)>
                        <label class="form-check-label" for="require_login">{{ translate('Require Login to View Cart') }}</label>
                    </div>
                </div>
            </div>
            @break

        @case('header_favorites')
            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Behavior & Display') }}</h6>

            <div class="mb-3" id="favoritesLabel">
                <label class="form-label">{{ translate('Favorites Label') }}</label>
                <input type="text" name="favorites_label" class="form-control" value="{{ $options['favorites_label'] ?? '' }}" placeholder="{{ translate('Favorites') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Label Position') }}</label>
                <select name="label_position" class="form-select selectpicker" data-conditional-toggle="#favoritesLabel" data-conditional-value="none" data-conditional-logic="not-equal">
                    <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                    <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                    <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Tooltip') }}</option>
                    <option value="none" @selected(($options['label_position'] ?? 'none') == 'none')>{{ translate('Hidden') }}</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Favorites Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['icon'] ?? 'bi-heart') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <input type="text" name="icon_color" class="form-control coloris" value="{{ $options['icon_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="show_count" value="0">
                        <input class="form-check-input" type="checkbox" id="show_count_fav" name="show_count" value="1" @checked($options['show_count'] ?? true)>
                        <label class="form-check-label" for="show_count_fav">{{ translate('Show Favorites Count') }}</label>
                    </div>
                </div>
            </div>
            @break

        @case('header_notification')
            <div class="mb-3" id="notificationLabel">
                <label class="form-label">{{ translate('Notification Label') }}</label>
                <input type="text" name="notification_label" class="form-control" value="{{ $options['notification_label'] ?? '' }}" placeholder="{{ translate('Notifications') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Label Position') }}</label>
                <select name="label_position" class="form-select selectpicker" data-conditional-toggle="#notificationLabel" data-conditional-value="none" data-conditional-logic="not-equal">
                    <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                    <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                    <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Tooltip') }}</option>
                    <option value="none" @selected(($options['label_position'] ?? '') == 'none')>{{ translate('Hidden') }}</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Notification Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['icon'] ?? 'bi-bell') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <input type="text" name="icon_color" class="form-control coloris" value="{{ $options['icon_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_badge" name="show_badge" value="1" @checked($options['show_badge'] ?? true)>
                    <label class="form-check-label" for="show_badge">{{ translate('Show Unread Badge') }}</label>
                </div>
            </div>
            @break

        @case('header_language_currency')
            <div class="mb-3">
                <label class="form-label">{{ translate('Trigger Type') }}</label>
                <select name="trigger_type"
                    id="lc_trigger_type"
                    class="form-select selectpicker"
                    data-conditional-toggle="#lc_dropdown_content, #lc_lang_label, #lc_curr_label"
                    data-conditional-value="both, currency, language"
                    data-conditional-logic="not-equal">
                    <option value="both" @selected(($options['trigger_type'] ?? 'both') == 'both')>{{ translate('Both (Language & Currency)') }}</option>
                    <option value="language" @selected(($options['trigger_type'] ?? '') == 'language')>{{ translate('Language Only') }}</option>
                    <option value="currency" @selected(($options['trigger_type'] ?? '') == 'currency')>{{ translate('Currency Only') }}</option>
                </select>
            </div>

            <div class="mb-3" id="lc_lang_label">
                <label class="form-label">{{ translate('Language Trigger Label') }}</label>
                <select name="lang_format" class="form-select selectpicker">
                    <option value="code" @selected(($options['lang_format'] ?? 'code') == 'code')>{{ translate('Language Code (e.g. EN)') }}</option>
                    <option value="name" @selected(($options['lang_format'] ?? '') == 'name')>{{ translate('Language Name (e.g. English)') }}</option>
                    <option value="flag" @selected(($options['lang_format'] ?? '') == 'flag')>{{ translate('Flag Only') }}</option>
                    <option value="flag_code" @selected(($options['lang_format'] ?? '') == 'flag_code')>{{ translate('Flag + Code') }}</option>
                </select>
            </div>

            <div class="mb-3" id="lc_curr_label">
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
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Display Style') }}</label>
                <select name="display_style" class="form-select selectpicker" data-conditional-toggle="#lc_dropdown_icon" data-conditional-value="dropdown" data-conditional-logic="equal">
                    <option value="dropdown" @selected(($options['display_style'] ?? 'dropdown') == 'dropdown')>{{ translate('Dropdown Menu') }}</option>
                    <option value="modal" @selected(($options['display_style'] ?? '') == 'modal')>{{ translate('Modal Popup') }}</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="lc_dropdown_content">
                <label class="form-label">{{ translate('Dropdown Content') }}</label>
                <select name="dropdown_content" class="form-select selectpicker">
                    <option value="respective" @selected(($options['dropdown_content'] ?? 'respective') == 'respective')>{{ translate('Show Single List') }}</option>
                    <option value="both" @selected(($options['dropdown_content'] ?? '') == 'both')>{{ translate('Show Both Lists') }}</option>
                </select>
                <p class="form-text mb-0">{{ translate('Choose if the dropdown should show only the triggers content or both.') }}</p>
            </div>

            <div class="mb-3 d-none" id="lc_dropdown_icon">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="hide_lc_drop_icon" name="hide_lc_drop_icon" @checked($options['hide_lc_drop_icon'] ?? false)>
                    <label class="form-check-label" for="hide_lc_drop_icon">{{ translate('Hide Dropdown Icon') }}</label>
                </div>
            </div>
            @break

        @case('header_theme_toggle')
            <div class="row g-3 mb-3">
                <div class="col-12" id="toggleLabel">
                    <label class="form-label">{{ translate('Toggle Label') }}</label>
                    <input type="text" name="toggle_label" class="form-control" value="{{ $options['toggle_label'] ?? '' }}" placeholder="{{ translate('Theme') }}">
                </div>

                <div class="col-12">
                    <label class="form-label">{{ translate('Label Position') }}</label>
                    <select name="label_position" class="form-select selectpicker" data-conditional-toggle="#toggleLabel" data-conditional-value="hidden" data-conditional-logic="not-equal">
                        <option value="hidden" @selected(($options['label_position'] ?? 'hidden') == 'hidden')>{{ translate('Hide Label') }}</option>
                        <option value="inline" @selected(($options['label_position'] ?? '') == 'inline')>{{ translate('Inline') }}</option>
                        <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                        <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Tooltip') }}</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">{{ translate('Toggle Style') }}</label>
                    <select name="toggle_style" class="form-select selectpicker">
                        <option value="icon" @selected(($options['toggle_style'] ?? 'icon') == 'icon')>{{ translate('Icon') }}</option>
                        <option value="switch" @selected(($options['toggle_style'] ?? '') == 'switch')>{{ translate('Switch') }}</option>
                        <option value="dropdown" @selected(($options['toggle_style'] ?? '') == 'dropdown')>{{ translate('Dropdown') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <input type="text" name="icon_color" class="form-control coloris" value="{{ $options['icon_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
            </div>
            @break

        @case('header_button')
            <div class="mb-3" id="buttonText">
                <label class="form-label">{{ translate('Button Label') }}</label>
                <input type="text" name="button_text" class="form-control" value="{{ $options['button_text'] ?? translate('Get Started') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Label Position') }}</label>
                <select name="label_position" class="form-select selectpicker" data-conditional-toggle="#buttonText" data-conditional-value="hidden" data-conditional-logic="not-equal">
                    <option value="hidden" @selected(($options['label_position'] ?? '') == 'hidden')>{{ translate('Icon Only') }}</option>
                    <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Icon + Text (Inline)') }}</option>
                    <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Icon + Text (Bottom)') }}</option>
                    <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Icon + Tooltip') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Button URL') }}</label>
                <input type="text" name="button_url" class="form-control" value="{{ $options['button_url'] ?? '#' }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true" id="btnIconSelect">
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
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="button_style" class="form-select selectpicker" data-live-search="true">
                        <option value="primary" @selected(($options['button_style'] ?? 'primary') == 'primary')>Primary</option>
                        <option value="secondary" @selected(($options['button_style'] ?? '') == 'secondary')>Secondary</option>
                        <option value="success" @selected(($options['button_style'] ?? '') == 'success')>Success</option>
                        <option value="danger" @selected(($options['button_style'] ?? '') == 'danger')>Danger</option>
                        <option value="warning" @selected(($options['button_style'] ?? '') == 'warning')>Warning</option>
                        <option value="info" @selected(($options['button_style'] ?? '') == 'info')>Info</option>
                        <option value="light" @selected(($options['button_style'] ?? '') == 'light')>Light</option>
                        <option value="dark" @selected(($options['button_style'] ?? '') == 'dark')>Dark</option>
                        <option value="link" @selected(($options['button_style'] ?? '') == 'link')>Link</option>
                        <option value="purple" @selected(($options['button_style'] ?? '') == 'purple')>Purple</option>
                        <option value="orange" @selected(($options['button_style'] ?? '') == 'orange')>Orange</option>
                        <option value="pink" @selected(($options['button_style'] ?? '') == 'pink')>Pink</option>
                        <option value="outline-primary" @selected(($options['button_style'] ?? '') == 'outline-primary')>Outline Primary</option>
                        <option value="outline-secondary" @selected(($options['button_style'] ?? '') == 'outline-secondary')>Outline Secondary</option>
                        <option value="outline-success" @selected(($options['button_style'] ?? '') == 'outline-success')>Outline Success</option>
                        <option value="outline-danger" @selected(($options['button_style'] ?? '') == 'outline-danger')>Outline Danger</option>
                        <option value="outline-warning" @selected(($options['button_style'] ?? '') == 'outline-warning')>Outline Warning</option>
                        <option value="outline-info" @selected(($options['button_style'] ?? '') == 'outline-info')>Outline Info</option>
                        <option value="outline-light" @selected(($options['button_style'] ?? '') == 'outline-light')>Outline Light</option>
                        <option value="outline-dark" @selected(($options['button_style'] ?? '') == 'outline-dark')>Outline Dark</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Shape') }}</label>
                    <select name="button_shape" class="form-select selectpicker">
                        <option value="rounded-0" @selected(($options['button_shape'] ?? '') == 'rounded-0')>{{ translate('Square') }}</option>
                        <option value="rounded-3" @selected(($options['button_shape'] ?? '') == 'rounded-3')>{{ translate('Rounded') }}</option>
                        <option value="rounded-pill" @selected(($options['button_shape'] ?? '') == 'rounded-pill')>{{ translate('Rounded Pill') }}</option>
                        <option value="rounded-circle" @selected(($options['button_shape'] ?? '') == 'rounded-circle')>{{ translate('Circle') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Size') }}</label>
                    <select name="button_size" class="form-select selectpicker">
                        <option value="xs" @selected(($options['button_size'] ?? '') == 'xs')>{{ translate('Extra Small') }}</option>
                        <option value="sm" @selected(($options['button_size'] ?? '') == 'sm')>{{ translate('Small') }}</option>
                        <option value="md" @selected(($options['button_size'] ?? 'md') == 'md')>{{ translate('Medium') }}</option>
                        <option value="lg" @selected(($options['button_size'] ?? '') == 'lg')>{{ translate('Large') }}</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="open_new_tab" name="open_new_tab" @checked($options['open_new_tab'] ?? false)>
                    <label class="form-check-label" for="open_new_tab">{{ translate('Open in New Tab') }}</label>
                </div>
            </div>
            @break

        @case('header_message')
            <div class="mb-3" id="messageLabel">
                <label class="form-label">{{ translate('Message Label') }}</label>
                <input type="text" name="message_label" class="form-control" value="{{ $options['message_label'] ?? '' }}" placeholder="{{ translate('Messages') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Label Position') }}</label>
                <select name="label_position" class="form-select selectpicker" data-conditional-toggle="#messageLabel" data-conditional-value="none" data-conditional-logic="not-equal">
                    <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                    <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                    <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Tooltip') }}</option>
                    <option value="none" @selected(($options['label_position'] ?? '') == 'none')>{{ translate('Hidden') }}</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Message Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['icon'] ?? 'bi-chat-dots') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <input type="text" name="icon_color" class="form-control coloris" value="{{ $options['icon_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_badge" name="show_badge" @checked($options['show_badge'] ?? true)>
                    <label class="form-check-label" for="show_badge">{{ translate('Show Unread Badge') }}</label>
                </div>
            </div>
            @break

        @case('header_premium')
            <div class="mb-3" id="premiumButtonText">
                <label class="form-label">{{ translate('Button Label') }}</label>
                <input type="text" name="button_text" class="form-control" value="{{ $options['button_text'] ?? translate('Premium') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Label Position') }}</label>
                <select name="label_position" class="form-select selectpicker" data-conditional-toggle="#premiumButtonText" data-conditional-value="hidden" data-conditional-logic="not-equal">
                    <option value="hidden" @selected(($options['label_position'] ?? '') == 'hidden')>{{ translate('Icon Only') }}</option>
                    <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Icon + Text (Inline)') }}</option>
                    <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Icon + Text (Bottom)') }}</option>
                    <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Icon + Tooltip') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Custom URL') }}</label>
                <input type="text" name="button_url" class="form-control" value="{{ $options['button_url'] ?? '' }}" placeholder="{{ translate('Default: /premium/plans') }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Button Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true" id="premiumIconSelect">
                        <option value="">{{ translate('No Icon') }}</option>
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                            @selected(($options['icon'] ?? 'bi-gem') == $iconClass)
                            data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}">
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Style') }}</label>
                    <select name="button_style" class="form-select selectpicker" data-live-search="true">
                        <option value="warning" @selected(($options['button_style'] ?? 'warning') == 'warning')>{{ translate('Warning') }}</option>
                        <option value="primary" @selected(($options['button_style'] ?? '') == 'primary')>{{ translate('Primary') }}</option>
                        <option value="success" @selected(($options['button_style'] ?? '') == 'success')>{{ translate('Success') }}</option>
                        <option value="info" @selected(($options['button_style'] ?? '') == 'info')>{{ translate('Info') }}</option>
                        <option value="danger" @selected(($options['button_style'] ?? '') == 'danger')>{{ translate('Danger') }}</option>
                        <option value="dark" @selected(($options['button_style'] ?? '') == 'dark')>{{ translate('Dark') }}</option>
                        <option value="light" @selected(($options['button_style'] ?? '') == 'light')>{{ translate('Light') }}</option>
                        <option value="link" @selected(($options['button_style'] ?? '') == 'link')>{{ translate('Link') }}</option>
                        <option value="outline-warning" @selected(($options['button_style'] ?? '') == 'outline-warning')>{{ translate('Outline Warning') }}</option>
                        <option value="outline-primary" @selected(($options['button_style'] ?? '') == 'outline-primary')>{{ translate('Outline Primary') }}</option>
                        <option value="outline-secondary" @selected(($options['button_style'] ?? '') == 'outline-secondary')>{{ translate('Outline Secondary') }}</option>
                        <option value="outline-success" @selected(($options['button_style'] ?? '') == 'outline-success')>{{ translate('Outline Success') }}</option>
                        <option value="outline-danger" @selected(($options['button_style'] ?? '') == 'outline-danger')>{{ translate('Outline Danger') }}</option>
                        <option value="outline-info" @selected(($options['button_style'] ?? '') == 'outline-info')>{{ translate('Outline Info') }}</option>
                        <option value="outline-light" @selected(($options['button_style'] ?? '') == 'outline-light')>{{ translate('Outline Light') }}</option>
                        <option value="outline-dark" @selected(($options['button_style'] ?? '') == 'outline-dark')>{{ translate('Outline Dark') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Shape') }}</label>
                    <select name="button_shape" class="form-select selectpicker">
                        <option value="rounded-0" @selected(($options['button_shape'] ?? '') == 'rounded-0')>{{ translate('Square') }}</option>
                        <option value="rounded-3" @selected(($options['button_shape'] ?? '') == 'rounded-3')>{{ translate('Rounded') }}</option>
                        <option value="rounded-pill" @selected(($options['button_shape'] ?? '') == 'rounded-pill')>{{ translate('Rounded Pill') }}</option>
                        <option value="rounded-circle" @selected(($options['button_shape'] ?? '') == 'rounded-circle')>{{ translate('Circle') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Button Size') }}</label>
                    <select name="button_size" class="form-select selectpicker">
                        <option value="sm" @selected(($options['button_size'] ?? 'sm') == 'sm')>{{ translate('Small') }}</option>
                        <option value="md" @selected(($options['button_size'] ?? '') == 'md')>{{ translate('Medium') }}</option>
                        <option value="lg" @selected(($options['button_size'] ?? '') == 'lg')>{{ translate('Large') }}</option>
                    </select>
                </div>
            </div>
            @break

        @case('header_divider')
            <div class="mb-3">
                <label class="form-label">{{ translate('Height') }} (px)</label>
                <input type="number" name="height" class="form-control" value="{{ $options['height'] ?? 24 }}" min="10" max="100">
            </div>
            <div class="mb-3">
                <label class="form-label d-block">{{ translate('Color') }}</label>
                <input type="text" name="color" class="form-control coloris" value="{{ $options['color'] ?? '#dee2e6' }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Margin (horizontal spacing)') }}</label>
                <select name="margin" class="form-select selectpicker">
                    <option value="1" @selected(($options['margin'] ?? '') == '1')>{{ translate('Small') }}</option>
                    <option value="2" @selected(($options['margin'] ?? '') == '2')>{{ translate('Medium') }}</option>
                    <option value="3" @selected(($options['margin'] ?? '3') == '3')>{{ translate('Large') }}</option>
                    <option value="4" @selected(($options['margin'] ?? '') == '4')>{{ translate('Extra Large') }}</option>
                </select>
            </div>
            @break

        @case('header_social')
            <div class="mb-3">
                <label class="form-label">{{ translate('View Style') }}</label>
                <select name="view_style" class="form-select selectpicker" data-conditional-toggle="#socialDropdownOptions" data-conditional-value="dropdown">
                    <option value="regular" @selected(($options['view_style'] ?? 'regular') == 'regular')>{{ translate('Regular (List)') }}</option>
                    <option value="dropdown" @selected(($options['view_style'] ?? '') == 'dropdown')>{{ translate('Dropdown') }}</option>
                </select>
            </div>

            {{-- Dropdown Trigger Options --}}
            <div id="socialDropdownOptions" class="card card-body bg-light border-0 mb-3 d-none">
                <div class="mb-3" id="socialBtnLabel">
                    <label class="form-label">{{ translate('Trigger Button Label') }}</label>
                    <input type="text" name="trigger_label" class="form-control" value="{{ $options['trigger_label'] ?? translate('Follow Us') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ translate('Label Position') }}</label>
                    <select name="trigger_label_position" class="form-select selectpicker" data-conditional-toggle="#socialBtnLabel" data-conditional-value="hidden" data-conditional-logic="not-equal">
                        <option value="hidden" @selected(($options['trigger_label_position'] ?? '') == 'hidden')>{{ translate('Icon Only') }}</option>
                        <option value="inline" @selected(($options['trigger_label_position'] ?? 'inline') == 'inline')>{{ translate('Icon + Text (Inline)') }}</option>
                        <option value="bottom" @selected(($options['trigger_label_position'] ?? '') == 'bottom')>{{ translate('Icon + Text (Bottom)') }}</option>
                        <option value="tooltip" @selected(($options['trigger_label_position'] ?? '') == 'tooltip')>{{ translate('Icon + Tooltip') }}</option>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">{{ translate('Button Icon') }}</label>
                        <select name="trigger_icon" class="form-select selectpicker" data-live-search="true">
                            <option value="">{{ translate('No Icon') }}</option>
                            @foreach($bootstrapIcons as $iconClass => $iconLabel)
                                <option value="{{ $iconClass }}"
                                    data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                    @selected(($options['trigger_icon'] ?? 'bi-share') == $iconClass)>
                                    {{ $iconLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="form-label">{{ translate('Button Style') }}</label>
                        <select name="trigger_button_style" class="form-select selectpicker" data-live-search="true" data-conditional-toggle="#btnWithStyle" data-conditional-value="none" data-conditional-logic="not-equal">
                            <option value="primary" @selected(($options['trigger_button_style'] ?? 'light') == 'primary')>Primary</option>
                            <option value="secondary" @selected(($options['trigger_button_style'] ?? '') == 'secondary')>Secondary</option>
                            <option value="success" @selected(($options['trigger_button_style'] ?? '') == 'success')>Success</option>
                            <option value="danger" @selected(($options['trigger_button_style'] ?? '') == 'danger')>Danger</option>
                            <option value="warning" @selected(($options['trigger_button_style'] ?? '') == 'warning')>Warning</option>
                            <option value="info" @selected(($options['trigger_button_style'] ?? '') == 'info')>Info</option>
                            <option value="light" @selected(($options['trigger_button_style'] ?? 'light') == 'light')>Light</option>
                            <option value="dark" @selected(($options['trigger_button_style'] ?? '') == 'dark')>Dark</option>
                            <option value="link" @selected(($options['trigger_button_style'] ?? '') == 'link')>Link</option>
                            <option value="purple" @selected(($options['trigger_button_style'] ?? '') == 'purple')>Purple</option>
                            <option value="orange" @selected(($options['trigger_button_style'] ?? '') == 'orange')>Orange</option>
                            <option value="pink" @selected(($options['trigger_button_style'] ?? '') == 'pink')>Pink</option>
                            <option value="outline-primary" @selected(($options['trigger_button_style'] ?? '') == 'outline-primary')>Outline Primary</option>
                            <option value="outline-secondary" @selected(($options['trigger_button_style'] ?? '') == 'outline-secondary')>Outline Secondary</option>
                            <option value="outline-success" @selected(($options['trigger_button_style'] ?? '') == 'outline-success')>Outline Success</option>
                            <option value="outline-danger" @selected(($options['trigger_button_style'] ?? '') == 'outline-danger')>Outline Danger</option>
                            <option value="outline-warning" @selected(($options['trigger_button_style'] ?? '') == 'outline-warning')>Outline Warning</option>
                            <option value="outline-info" @selected(($options['trigger_button_style'] ?? '') == 'outline-info')>Outline Info</option>
                            <option value="outline-light" @selected(($options['trigger_button_style'] ?? '') == 'outline-light')>Outline Light</option>
                            <option value="outline-dark" @selected(($options['trigger_button_style'] ?? '') == 'outline-dark')>Outline Dark</option>
                            <option value="none" @selected(($options['trigger_button_style'] ?? '') == 'none')>None</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="btnWithStyle">
                    <div class="col-6">
                        <label class="form-label">{{ translate('Button Shape') }}</label>
                        <select name="trigger_shape" class="form-select selectpicker">
                            <option value="rounded-0" @selected(($options['trigger_shape'] ?? '') == 'rounded-0')>{{ translate('Square') }}</option>
                            <option value="rounded-3" @selected(($options['trigger_shape'] ?? '') == 'rounded-3')>{{ translate('Rounded') }}</option>
                            <option value="rounded-pill" @selected(($options['trigger_shape'] ?? '') == 'rounded-pill')>{{ translate('Rounded Pill') }}</option>
                            <option value="rounded-circle" @selected(($options['trigger_shape'] ?? '') == 'rounded-circle')>{{ translate('Circle') }}</option>
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="form-label">{{ translate('Button Size') }}</label>
                        <select name="trigger_size" class="form-select selectpicker">
                            <option value="xs" @selected(($options['trigger_size'] ?? '') == 'xs')>{{ translate('Extra Small') }}</option>
                            <option value="sm" @selected(($options['trigger_size'] ?? '') == 'sm')>{{ translate('Small') }}</option>
                            <option value="md" @selected(($options['trigger_size'] ?? 'md') == 'md')>{{ translate('Medium') }}</option>
                            <option value="lg" @selected(($options['trigger_size'] ?? '') == 'lg')>{{ translate('Large') }}</option>
                        </select>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="hide_dropdown_icon" name="hide_dropdown_icon" @checked($options['hide_dropdown_icon'] ?? false)>
                        <label class="form-check-label" for="hide_dropdown_icon">{{ translate('Hide Dropdown Icon') }}</label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Social Display Style') }}</label>
                    <select name="display_style" class="form-select selectpicker">
                        <option value="icon_only" @selected(($options['display_style'] ?? 'icon_only') == 'icon_only')>{{ translate('Icon Only') }}</option>
                        <option value="icon_name" @selected(($options['display_style'] ?? '') == 'icon_name')>{{ translate('Icon + Name') }}</option>
                        <option value="tooltip" @selected(($options['display_style'] ?? '') == 'tooltip')>{{ translate('Icon with Tooltip') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <select name="color_style" class="form-select selectpicker">
                        <option value="monochrome" @selected(($options['color_style'] ?? 'monochrome') == 'monochrome')>{{ translate('Monochrome') }}</option>
                        <option value="multicolor" @selected(($options['color_style'] ?? '') == 'multicolor')>{{ translate('Multicolor') }}</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Style') }}</label>
                    <select name="icon_style" class="form-select selectpicker">
                        <option value="default" @selected(($options['icon_style'] ?? 'default') == 'default')>{{ translate('Default') }}</option>
                        <option value="circle" @selected(($options['icon_style'] ?? '') == 'circle')>{{ translate('Circle') }}</option>
                        <option value="square" @selected(($options['icon_style'] ?? '') == 'square')>{{ translate('Rounded') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? 'fs-6') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? '') == 'fs-5')>{{ translate('Medium') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Gap Between Icons') }}</label>
                    <select name="gap" class="form-select selectpicker">
                        <option value="1" @selected(($options['gap'] ?? '') == '1')>{{ translate('Small') }}</option>
                        <option value="2" @selected(($options['gap'] ?? '2') == '2')>{{ translate('Medium') }}</option>
                        <option value="3" @selected(($options['gap'] ?? '') == '3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="active_hover_effects" name="active_hover_effects" value="1" @checked($options['active_hover_effects'] ?? false)>
                        <label class="form-check-label" for="active_hover_effects">{{ translate('Active Hover Effects') }}</label>
                    </div>
                </div>
            </div>
            <p class="text-muted small">{{ translate('Social links are configured in Settings > General > Social Links') }}</p>
            @break

        @case('header_offcanvas')
            {{-- Trigger Settings --}}
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">{{ translate('Trigger Icon') }}</label>
                    <select name="icon" class="form-select selectpicker">
                        <option value="bi-list" @selected(($options['icon'] ?? 'bi-list') == 'bi-list')>☰ {{ translate('Hamburger') }}</option>
                        <option value="bi-grid" @selected(($options['icon'] ?? '') == 'bi-grid')>⊞ {{ translate('Grid') }}</option>
                        <option value="bi-three-dots-vertical" @selected(($options['icon'] ?? '') == 'bi-three-dots-vertical')>⋮ {{ translate('Dots Vertical') }}</option>
                        <option value="bi-three-dots" @selected(($options['icon'] ?? '') == 'bi-three-dots')>⋯ {{ translate('Dots Horizontal') }}</option>
                        <option value="bi-person" @selected(($options['icon'] ?? '') == 'bi-person')>{{ translate('User') }}</option>
                        <option value="bi-gear" @selected(($options['icon'] ?? '') == 'bi-gear')>{{ translate('Settings') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <input type="text" name="icon_color" class="form-control coloris" value="{{ $options['icon_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Trigger Label') }}</label>
                    <input type="text" name="label" class="form-control" value="{{ $options['label'] ?? translate('Menu') }}" placeholder="{{ translate('Label') }}">
                </div>

                <div class="col-6">
                    <label class="form-label">{{ translate('Label Position') }}</label>
                    <select name="label_position" class="form-select selectpicker">
                        <option value="hidden" @selected(($options['label_position'] ?? '') == 'hidden')>{{ translate('Icon Only') }}</option>
                        <option value="inline" @selected(($options['label_position'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                        <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
                        <option value="tooltip" @selected(($options['label_position'] ?? '') == 'tooltip')>{{ translate('Tooltip') }}</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="hide_label_offcanvas" name="hide_label_offcanvas" value="1" @checked($options['hide_label_offcanvas'] ?? false)>
                        <label class="form-check-label" for="hide_label_offcanvas">{{ translate('Hide Label in Offcanvas') }}</label>
                    </div>
                </div>
            </div>

            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Offcanvas Style') }}</h6>
            <div class="row g-3 mb-3">
                 <div class="col-12">
                    <label class="form-label">{{ translate('Width') }}</label>
                    <select name="offcanvas_width" class="form-select selectpicker">
                        <option value="default" @selected(($options['offcanvas_width'] ?? 'default') == 'default')>{{ translate('Default') }}</option>
                        <option value="sm" @selected(($options['offcanvas_width'] ?? '') == 'sm')>{{ translate('Small') }}</option>
                        <option value="md" @selected(($options['offcanvas_width'] ?? '') == 'md')>{{ translate('Medium') }}</option>
                        <option value="lg" @selected(($options['offcanvas_width'] ?? '') == 'lg')>{{ translate('Large') }}</option>
                        <option value="full" @selected(($options['offcanvas_width'] ?? '') == 'full')>{{ translate('Full Width') }}</option>
                    </select>
                </div>
                 <div class="col-6">
                    <label class="form-label">{{ translate('Background Color') }}</label>
                    <div class="input-group">
                         <input type="text" name="bg_color" class="form-control coloris" value="{{ $options['bg_color'] ?? '#ffffff' }}" placeholder="#ffffff">
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Text Color') }}</label>
                     <div class="input-group">
                         <input type="text" name="text_color" class="form-control coloris" value="{{ $options['text_color'] ?? '#212529' }}" placeholder="#212529">
                    </div>
                </div>
            </div>

            <hr class="my-3">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Offcanvas Content') }}</h6>
            <p class="small text-muted mb-3">{{ translate('Select elements to display inside the offcanvas panel.') }}</p>

            {{-- 1. Menu Element --}}
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[menu][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_menu" name="elements[menu][enabled]" value="1"
                               @checked($options['elements']['menu']['enabled'] ?? true)
                               data-slide-toggle="#menuOptions">
                        <label class="form-check-label fw-bold" for="enable_menu">{{ translate('Navigation Menu') }}</label>
                    </div>

                    <div id="menuOptions" class="mt-3 {{ ($options['elements']['menu']['enabled'] ?? true) ? '' : 'd-none' }}">
                        <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[menu][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['menu']['section'] ?? '') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['menu']['section'] ?? 'main') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['menu']['section'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">{{ translate('Menu Source') }}</label>
                                <select name="elements[menu][location]" class="form-select form-select-sm selectpicker">
                                    <option value="mobile" @selected(($options['elements']['menu']['location'] ?? 'mobile') == 'mobile')>{{ translate('Mobile Navigation') }}</option>
                                    <option value="top" @selected(($options['elements']['menu']['location'] ?? '') == 'top')>{{ translate('Top Navigation') }}</option>
                                    <option value="main" @selected(($options['elements']['menu']['location'] ?? '') == 'main')>{{ translate('Main Navigation') }}</option>
                                    <option value="footer" @selected(($options['elements']['menu']['location'] ?? '') == 'footer')>{{ translate('Footer Menu') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Search Element --}}
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[search][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_search" name="elements[search][enabled]" value="1"
                               @checked($options['elements']['search']['enabled'] ?? false)
                               data-slide-toggle="#searchOptions">
                        <label class="form-check-label fw-bold" for="enable_search">{{ translate('Search Box') }}</label>
                    </div>

                    <div id="searchOptions" class="mt-3 {{ ($options['elements']['search']['enabled'] ?? false) ? '' : 'd-none' }}">
                         <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[search][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['search']['section'] ?? 'header') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['search']['section'] ?? '') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['search']['section'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                            {{-- Add more search options if needed --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Social Icons --}}
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[social][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_social" name="elements[social][enabled]" value="1"
                               @checked($options['elements']['social']['enabled'] ?? false)
                               data-slide-toggle="#socialOptions">
                        <label class="form-check-label fw-bold" for="enable_social">{{ translate('Social Icons') }}</label>
                    </div>

                    <div id="socialOptions" class="mt-3 {{ ($options['elements']['social']['enabled'] ?? false) ? '' : 'd-none' }}">
                         <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[social][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['social']['section'] ?? '') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['social']['section'] ?? '') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['social']['section'] ?? 'footer') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">{{ translate('Icon Color') }}</label>
                                <select name="elements[social][color_style]" class="form-select form-select-sm selectpicker">
                                    <option value="monochrome" @selected(($options['elements']['social']['color_style'] ?? 'monochrome') == 'monochrome')>{{ translate('Monochrome (Text Color)') }}</option>
                                    <option value="brand" @selected(($options['elements']['social']['color_style'] ?? '') == 'brand')>{{ translate('Brand Colors') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Cart Element --}}
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[cart][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_cart" name="elements[cart][enabled]" value="1"
                               @checked($options['elements']['cart']['enabled'] ?? false)
                               data-slide-toggle="#cartOptions">
                        <label class="form-check-label fw-bold" for="enable_cart">{{ translate('Cart Icon') }}</label>
                    </div>

                    <div id="cartOptions" class="mt-3 {{ ($options['elements']['cart']['enabled'] ?? false) ? '' : 'd-none' }}">
                         <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[cart][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['cart']['section'] ?? 'header') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['cart']['section'] ?? '') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['cart']['section'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">{{ translate('Cart Label') }}</label>
                                <input type="text" name="elements[cart][label]" class="form-control form-control-sm" value="{{ $options['elements']['cart']['label'] ?? '' }}" placeholder="{{ translate('Cart') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. Premium Button --}}
            @if (isPremiumAvailable())
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[premium][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_premium" name="elements[premium][enabled]" value="1"
                               @checked($options['elements']['premium']['enabled'] ?? false)
                               data-slide-toggle="#premiumOptions">
                        <label class="form-check-label fw-bold" for="enable_premium">{{ translate('Premium Button') }}</label>
                    </div>

                    <div id="premiumOptions" class="mt-3 {{ ($options['elements']['premium']['enabled'] ?? false) ? '' : 'd-none' }}">
                         <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[premium][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['premium']['section'] ?? 'header') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['premium']['section'] ?? '') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['premium']['section'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">{{ translate('Button Label') }}</label>
                                <input type="text" name="elements[premium][label]" class="form-control form-control-sm" value="{{ $options['elements']['premium']['label'] ?? translate('Premium') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">{{ translate('Button Icon') }}</label>
                                <select name="elements[premium][icon]" class="form-select form-select-sm selectpicker">
                                    <option value="bi-gem" @selected(($options['elements']['premium']['icon'] ?? 'bi-gem') == 'bi-gem')>{{ translate('Gem') }}</option>
                                    <option value="bi-award" @selected(($options['elements']['premium']['icon'] ?? '') == 'bi-award')>{{ translate('Award') }}</option>
                                    <option value="bi-box-arrow-up" @selected(($options['elements']['premium']['icon'] ?? '') == 'bi-box-arrow-up')>{{ translate('Box Arrow Up') }}</option>
                                </select>
                            </div>
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Style') }}</label>
                                <select name="elements[premium][style]" class="form-select form-select-sm selectpicker">
                                    <option value="none" @selected(($options['elements']['premium']['style'] ?? '') == 'none')>{{ translate('None') }}</option>
                                    <option value="btn-warning" @selected(($options['elements']['premium']['style'] ?? 'btn-warning') == 'btn-warning')>{{ translate('Warning') }}</option>
                                    <option value="btn-primary" @selected(($options['elements']['premium']['style'] ?? '') == 'btn-primary')>{{ translate('Primary') }}</option>
                                    <option value="btn-dark" @selected(($options['elements']['premium']['style'] ?? '') == 'btn-dark')>{{ translate('Dark') }}</option>
                                    <option value="btn-outline-warning" @selected(($options['elements']['premium']['style'] ?? '') == 'btn-outline-warning')>{{ translate('Outline Warning') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 6. Language & Currency --}}
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[language][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_language" name="elements[language][enabled]" value="1"
                               @checked($options['elements']['language']['enabled'] ?? false)
                               data-slide-toggle="#languageOptions">
                        <label class="form-check-label fw-bold" for="enable_language">{{ translate('Language & Currency') }}</label>
                    </div>

                    <div id="languageOptions" class="mt-3 {{ ($options['elements']['language']['enabled'] ?? false) ? '' : 'd-none' }}">
                         <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[language][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['language']['section'] ?? 'header') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['language']['section'] ?? '') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['language']['section'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 7. Theme Toggle --}}
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[theme][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_theme" name="elements[theme][enabled]" value="1"
                               @checked($options['elements']['theme']['enabled'] ?? false)
                               data-slide-toggle="#themeOptions">
                        <label class="form-check-label fw-bold" for="enable_theme">{{ translate('Theme Toggle') }}</label>
                    </div>

                    <div id="themeOptions" class="mt-3 {{ ($options['elements']['theme']['enabled'] ?? false) ? '' : 'd-none' }}">
                         <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[theme][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['theme']['section'] ?? 'header') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['theme']['section'] ?? '') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['theme']['section'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 8. Custom HTML --}}
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="elements[html][enabled]" value="0">
                        <input class="form-check-input" type="checkbox" id="enable_html" name="elements[html][enabled]" value="1"
                               @checked($options['elements']['html']['enabled'] ?? false)
                               data-slide-toggle="#htmlOptions">
                        <label class="form-check-label fw-bold" for="enable_html">{{ translate('Custom HTML') }}</label>
                    </div>

                    <div id="htmlOptions" class="mt-3 {{ ($options['elements']['html']['enabled'] ?? false) ? '' : 'd-none' }}">
                         <div class="row g-2">
                             <div class="col-12">
                                <label class="form-label small">{{ translate('Section') }}</label>
                                <select name="elements[html][section]" class="form-select form-select-sm selectpicker">
                                    <option value="header" @selected(($options['elements']['html']['section'] ?? '') == 'header')>{{ translate('Header') }}</option>
                                    <option value="main" @selected(($options['elements']['html']['section'] ?? 'main') == 'main')>{{ translate('Main') }}</option>
                                    <option value="footer" @selected(($options['elements']['html']['section'] ?? '') == 'footer')>{{ translate('Footer') }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">{{ translate('HTML Content') }}</label>
                                <textarea name="elements[html][content]" class="form-control form-control-sm font-monospace" rows="4">{{ $options['elements']['html']['content'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @break

        @case('header_html')
            <div class="mb-3">
                <label class="form-label">{{ translate('HTML Content') }}</label>
                <textarea name="html_content" class="form-control font-monospace" rows="6" placeholder="<div>Your HTML here</div>">{{ $options['html_content'] ?? '' }}</textarea>
                <small class="text-orange">{{ translate('Warning: Be careful with custom HTML. Invalid code may break the layout.') }}</small>
            </div>
            @break

        @case('header_countdown')
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
            </div>

            <div class="mb-3">
                <label class="form-label">{{ translate('Style') }}</label>
                <select name="style" class="form-select selectpicker" data-conditional-toggle="#boxedStyleOptions" data-conditional-value="boxed">
                    <option value="inline" @selected(($options['style'] ?? 'inline') == 'inline')>{{ translate('Inline') }}</option>
                    <option value="boxed" @selected(($options['style'] ?? '') == 'boxed')>{{ translate('Boxed') }}</option>
                </select>
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
            <div class="row">
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

        @case('header_icon')
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">{{ translate('Icon') }}</label>
                    <select name="icon" class="form-select selectpicker" data-live-search="true">
                        @foreach($bootstrapIcons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}"
                                data-content="<i class='bi {{ $iconClass }} me-1'></i> {{ $iconLabel }}"
                                @selected(($options['icon'] ?? 'bi-star') == $iconClass)>
                                {{ $iconLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Size') }}</label>
                    <select name="icon_size" class="form-select selectpicker">
                        <option value="fs-6" @selected(($options['icon_size'] ?? '') == 'fs-6')>{{ translate('Small') }}</option>
                        <option value="fs-5" @selected(($options['icon_size'] ?? 'fs-5') == 'fs-5')>{{ translate('Default') }}</option>
                        <option value="fs-4" @selected(($options['icon_size'] ?? '') == 'fs-4')>{{ translate('Medium') }}</option>
                        <option value="fs-3" @selected(($options['icon_size'] ?? '') == 'fs-3')>{{ translate('Large') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Icon Color') }}</label>
                    <input type="text" name="icon_color" class="form-control coloris" value="{{ $options['icon_color'] ?? '' }}" placeholder="{{ translate('Default') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Link URL') }}</label>
                    <input type="text" name="link" class="form-control" value="{{ $options['link'] ?? '' }}" placeholder="https://...">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Link Target') }}</label>
                    <select name="link_target" class="form-select selectpicker">
                        <option value="_self" @selected(($options['link_target'] ?? '_self') == '_self')>{{ translate('Same Window') }}</option>
                        <option value="_blank" @selected(($options['link_target'] ?? '') == '_blank')>{{ translate('New Tab') }}</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Tooltip') }}</label>
                    <input type="text" name="tooltip" class="form-control" value="{{ $options['tooltip'] ?? '' }}" placeholder="{{ translate('Hover text') }}">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="show_label" value="0">
                        <input class="form-check-input" type="checkbox" id="show_label" name="show_label" value="1" @checked(($options['show_label'] ?? '0') == '1') data-slide-toggle="#iconLabelOptions">
                        <label class="form-check-label" for="show_label">{{ translate('Show Label') }}</label>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-3 d-none" id="iconLabelOptions">
                <div class="col-6">
                    <label class="form-label">{{ translate('Label Text') }}</label>
                    <input type="text" name="label_text" class="form-control" value="{{ $options['label_text'] ?? '' }}" placeholder="{{ translate('Enter label') }}">
                </div>
                <div class="col-6">
                    <label class="form-label">{{ translate('Label Position') }}</label>
                    <select name="label_position" class="form-select selectpicker">
                        <option value="left" @selected(($options['label_position'] ?? '') == 'left')>{{ translate('Left') }}</option>
                        <option value="right" @selected(($options['label_position'] ?? 'right') == 'right')>{{ translate('Right') }}</option>
                        <option value="bottom" @selected(($options['label_position'] ?? '') == 'bottom')>{{ translate('Bottom') }}</option>
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

    <div class="form-check form-switch mb-3">
        <input type="hidden" name="status" value="0">
        <input class="form-check-input" type="checkbox" id="block_status" name="status" value="1" @checked($isActive)>
        <label class="form-check-label" for="block_status">{{ translate('Active :block', ['block' => $headerBlock->title ?? '']) }}</label>
    </div>
</form>
