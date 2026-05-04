<form id="sectionSettingsForm">

    <div class="section-options-panel p-2">
        <!-- Layout -->
        <h6 class="mb-3 text-uppercase small text-muted fw-bold">
            <i class="bi bi-layout-text-sidebar-reverse me-2"></i>{{ translate('Layout') }}</h6>
        <div class="mb-3">
            <label class="form-label">{{ translate('Container Width') }}</label>
            <div class="d-flex gap-2">
                <div class="form-check form-check-inline card p-2 flex-fill justify-content-center text-center m-0">
                    <input class="form-check-input mb-1 mx-auto" type="radio" name="container_width" id="cw_default" value="default" checked>
                    <label class="form-check-label stretched-link small" for="cw_default">{{ translate('Default') }}</label>
                </div>
                <div class="form-check form-check-inline card p-2 flex-fill justify-content-center text-center m-0">
                    <input class="form-check-input mb-1 mx-auto" type="radio" name="container_width" id="cw_boxed" value="boxed">
                    <label class="form-check-label stretched-link small" for="cw_boxed">{{ translate('Boxed') }}</label>
                </div>
                <div class="form-check form-check-inline card p-2 flex-fill justify-content-center text-center m-0">
                    <input class="form-check-input mb-1 mx-auto" type="radio" name="container_width" id="cw_full" value="full_width">
                    <label class="form-check-label stretched-link small" for="cw_full">{{ translate('Full Width') }}</label>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label">{{ translate('Gap Between Columns') }}</label>
                <div class="input-group">
                    <select class="form-select selectpicker" name="gap_between_columns" id="gap_between_columns_select">
                        <option value="">{{ translate('Default') }}</option>
                        <option value="gap-2">{{ translate('Small') }}</option>
                        <option value="gap-4">{{ translate('Medium') }}</option>
                        <option value="gap-5">{{ translate('Large') }}</option>
                    </select>
                </div>
            </div>

             <div class="col-6">
                <label class="form-label">{{ translate('Gap Between Blocks') }}</label>
                <div class="input-group">
                    <select class="form-select selectpicker" name="gap_between_blocks" id="gap_between_blocks_select">
                        <option value="">{{ translate('Default') }}</option>
                        <option value="gap-2">{{ translate('Small') }}</option>
                        <option value="gap-4">{{ translate('Medium') }}</option>
                        <option value="gap-5">{{ translate('Large') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ translate('Content Direction') }}</label>
            <div class="row g-2">
                <div class="col-12">
                    <select class="form-select form-select-sm selectpicker" name="flex_direction" id="flex_direction_select">
                        <option value="row">{{ translate('Row (Horizontal)') }}</option>
                        <option value="column">{{ translate('Column (Vertical)') }}</option>
                        <option value="row-reverse">{{ translate('Row Reverse') }}</option>
                        <option value="column-reverse">{{ translate('Column Reverse') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3">
           <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="mobile_col_reverse" name="mobile_col_reverse" @checked($options['mobile_col_reverse'] ?? false)>
                <label class="form-check-label" for="mobile_col_reverse">{{ translate('Column Reverse in Small Screens(<991px)') }}</label>
            </div>
        </div>

        <hr class="border-secondary-subtle">

        <!-- Background -->
        <h6 class="mb-3 text-uppercase small text-muted fw-bold">
            <i class="bi bi-image me-2"></i>{{ translate('Background') }}</h6>
        <div class="mb-3" id="bgColor">
           <label class="form-label small">{{ translate('Background Color') }}</label>
           <div class="colorpicker">
               <input type="text" class="form-control coloris" name="bg_color" value="#ffffff">
           </div>
       </div>
        <div class="mb-3">
           <div class="d-flex justify-content-between align-items-center mb-2">
               <label class="form-label mb-0 small">{{ translate('Background Image') }}</label>
               <div class="form-check form-switch">
                   <input class="form-check-input" type="checkbox" id="bg_image_toggle" data-slide-toggle="#bg_image_wrapper">
               </div>
           </div>
           <div id="bg_image_wrapper" class="d-none">
               <div class="input-group mb-2">
                   <input type="text" class="form-control form-control-sm" id="bg_image_display" placeholder="{{ translate('No image selected') }}" readonly>
                   <input type="hidden" name="bg_image" id="bg_image_hidden">
                   <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#row-bg-file').click()"><i class="bi bi-upload"></i></button>
                   <input type="file" id="row-bg-file" class="d-none" accept="image/*">
               </div>
                <div class="row g-2 mb-3">
                   <div class="col-6">
                       <label class="form-label small">{{ translate('Repeat') }}</label>
                       <select class="form-select form-select-sm selectpicker" name="bg_repeat">
                           <option value="no-repeat">{{ translate('No Repeat') }}</option>
                           <option value="repeat">{{ translate('Repeat') }}</option>
                           <option value="repeat-x">{{ translate('Repeat X') }}</option>
                           <option value="repeat-y">{{ translate('Repeat Y') }}</option>
                       </select>
                   </div>
                   <div class="col-6">
                      <label class="form-label small">{{ translate('Size') }}</label>
                      <select class="form-select form-select-sm selectpicker" name="bg_size">
                          <option value="cover">{{ translate('Cover') }}</option>
                          <option value="contain">{{ translate('Contain') }}</option>
                          <option value="auto">{{ translate('Auto') }}</option>
                      </select>
                   </div>
                    <div class="col-12">
                       <label class="form-label small">{{ translate('Position') }}</label>
                       <select class="form-select form-select-sm selectpicker" name="bg_position">
                           <option value="center center">{{ translate('Center Center') }}</option>
                           <option value="center top">{{ translate('Center Top') }}</option>
                           <option value="center bottom">{{ translate('Center Bottom') }}</option>
                           <option value="left top">{{ translate('Left Top') }}</option>
                           <option value="left center">{{ translate('Left Center') }}</option>
                           <option value="left bottom">{{ translate('Left Bottom') }}</option>
                           <option value="right top">{{ translate('Right Top') }}</option>
                           <option value="right center">{{ translate('Right Center') }}</option>
                           <option value="right bottom">{{ translate('Right Bottom') }}</option>
                       </select>
                   </div>
               </div>
           </div>
       </div>

       <hr class="border-secondary-subtle">

       <!-- Typography -->
       <h6 class="mb-3 text-uppercase small text-muted fw-bold">
            <i class="bi bi-file-earmark-font me-2"></i>{{ translate('Typography') }}</h6>
       <div class="mb-3">
            <label class="form-label small">{{ translate('Font Family') }}</label>
            <select class="form-select form-select-sm selectpicker" name="font_family" data-live-search="true">
                <option value="">{{ translate('Default') }}</option>
                @foreach($googleFonts as $name => $family)
                    <option value="{{ $family }}" data-content="<span style='font-family:{{ $family }}'>{{ $name }}</span>">{{ $name }}</option>
                @endforeach
            </select>
       </div>
       <div class="row g-2 mb-4">
            <div class="col-6">
               <label class="form-label small">{{ translate('Text Color') }}</label>
               <div class="colorpicker">
                   <input type="text" class="form-control coloris" name="text_color" value="#000000">
               </div>
           </div>
            <div class="col-6">
               <label class="form-label small">{{ translate('Link Color') }}</label>
               <div class="colorpicker">
                   <input type="text" class="form-control coloris" name="link_color" value="#0d6efd">
               </div>
           </div>
       </div>

        <hr class="border-secondary-subtle">

       <!-- Spacing -->
       <h6 class="mb-3 text-uppercase small text-muted fw-bold">
            <i class="bi bi-arrows-expand me-2"></i>{{ translate('Spacing') }}</h6>
       <div class="row g-2 mb-4">
           <div class="col-12">
               <label class="form-label small">{{ translate('Padding (px)') }}</label>
               <div class="linked-input-group input-group input-group-sm">
                   <span class="input-group-text px-2" title="{{ translate('Top padding') }}"><i class="bi bi-arrow-bar-up"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="padding_top" placeholder="0" min="0">

                   <span class="input-group-text px-2" title="{{ translate('Right padding') }}"><i class="bi bi-arrow-bar-right"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="padding_right" placeholder="0" min="0">

                   <span class="input-group-text px-2" title="{{ translate('Bottom padding') }}"><i class="bi bi-arrow-bar-down"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="padding_bottom" placeholder="0" min="0">

                   <span class="input-group-text px-2" title="{{ translate('Left padding') }}"><i class="bi bi-arrow-bar-left"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="padding_left" placeholder="0" min="0">

                   <div class="input-group-text p-0 bg-white">
                        <input type="checkbox" class="btn-check linked-toggle" id="link_padding_values" autocomplete="off">
                        <label class="btn btn-sm h-100 d-flex align-items-center px-2" for="link_padding_values" data-bs-toggle="tooltip" title="{{ translate('Link values together') }}">
                           <i class="bi bi-link-45deg text-muted opacity-50"></i>
                        </label>
                   </div>
               </div>
           </div>
            <div class="col-12">
               <label class="form-label small">{{ translate('Margin (px)') }}</label>
               <div class="linked-input-group input-group input-group-sm">
                   <span class="input-group-text px-2" title="{{ translate('Top margin') }}"><i class="bi bi-arrow-bar-up"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="margin_top" placeholder="0">

                   <span class="input-group-text px-2" title="{{ translate('Right margin') }}"><i class="bi bi-arrow-bar-right"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="margin_right" placeholder="0">

                   <span class="input-group-text px-2" title="{{ translate('Bottom margin') }}"><i class="bi bi-arrow-bar-down"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="margin_bottom" value="0">

                   <span class="input-group-text px-2" title="{{ translate('Left margin') }}"><i class="bi bi-arrow-bar-left"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="margin_left" placeholder="0">

                   <div class="input-group-text p-0 bg-white">
                        <input type="checkbox" class="btn-check linked-toggle" id="link_margin_values" autocomplete="off">
                        <label class="btn btn-sm h-100 d-flex align-items-center px-2" for="link_margin_values" data-bs-toggle="tooltip" title="{{ translate('Link values together') }}">
                           <i class="bi bi-link-45deg text-muted opacity-50"></i>
                        </label>
                   </div>
               </div>
           </div>
       </div>

        <hr class="border-secondary-subtle">

       <!-- Border -->
       <h6 class="mb-3 text-uppercase small text-muted fw-bold">
            <i class="bi bi-border-width me-2"></i>{{ translate('Border') }}</h6>
        <div class="mb-3">
           <label class="form-label small">{{ translate('Border Type') }}</label>
           <select class="form-select form-select-sm selectpicker" name="border_style" id="border_style_select"
               data-conditional-toggle="#border_details_wrapper"
               data-conditional-value="none"
               data-conditional-logic="not-equal">
               <option value="none">{{ translate('None') }}</option>
               <option value="solid">{{ translate('Solid') }}</option>
               <option value="dashed">{{ translate('Dashed') }}</option>
               <option value="dotted">{{ translate('Dotted') }}</option>
               <option value="double">{{ translate('Double') }}</option>
           </select>
       </div>

       <div id="border_details_wrapper" class="row g-3 d-none">
            <div class="col-12">
                <label class="form-label small">{{ translate('Border Color') }}</label>
                <div class="colorpicker">
                    <input type="text" class="form-control coloris" name="border_color" value="#dee2e6">
                </div>
            </div>

           <div class="col-12">
               <label class="form-label small mb-1">{{ translate('Border Width (px)') }}</label>
               <div class="linked-input-group input-group input-group-sm">
                   <span class="input-group-text px-2" title="{{ translate('Top border width') }}"><i class="bi bi-arrow-bar-up"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="border_top_width" placeholder="0" min="0">

                   <span class="input-group-text px-2" title="{{ translate('Right border width') }}"><i class="bi bi-arrow-bar-right"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="border_right_width" placeholder="0" min="0">

                   <span class="input-group-text px-2" title="{{ translate('Bottom border width') }}"><i class="bi bi-arrow-bar-down"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="border_bottom_width" placeholder="0" min="0">

                   <span class="input-group-text px-2" title="{{ translate('Left border width') }}"><i class="bi bi-arrow-bar-left"></i></span>
                   <input type="number" class="form-control text-center px-1 linked-input" name="border_left_width" placeholder="0" min="0">

                   <div class="input-group-text p-0 bg-white">
                        <input type="checkbox" class="btn-check linked-toggle" id="link_border_values" checked autocomplete="off">
                        <label class="btn btn-sm h-100 d-flex align-items-center px-2" for="link_border_values" data-bs-toggle="tooltip" title="{{ translate('Link values together') }}">
                           <i class="bi bi-link-45deg"></i>
                        </label>
                   </div>
               </div>
           </div>
       </div>
    </div>
</form>
