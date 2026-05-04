<form id="sectionSettingsForm">
    <div class="section-options-panel">
        <h6 class="mb-3 text-uppercase small text-muted fw-bold">{{ translate('Layout') }}</h6>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0" for="is_full_width">{{ translate('Make This Section Full Width')
                    }}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_full_width" id="is_full_width" value="1" @checked($section->is_full_width ?? false)>
                </div>
            </div>
        </div>

        <div class="mb-3" id="containerWidth">
            <label class="form-label">{{ translate('Container Width') }}</label>
            <div class="d-flex gap-2">
                <div class="form-check form-check-inline card p-2 flex-fill justify-content-center text-center m-0">
                    <input class="form-check-input mb-1 mx-auto" type="radio" name="container_width" id="cw_default"
                        value="default" checked>
                    <label class="form-check-label stretched-link small" for="cw_default">{{ translate('Default')
                        }}</label>
                </div>
                <div class="form-check form-check-inline card p-2 flex-fill justify-content-center text-center m-0">
                    <input class="form-check-input mb-1 mx-auto" type="radio" name="container_width" id="cw_boxed"
                        value="boxed">
                    <label class="form-check-label stretched-link small" for="cw_boxed">{{ translate('Boxed') }}</label>
                </div>
                <div class="form-check form-check-inline card p-2 flex-fill justify-content-center text-center m-0">
                    <input class="form-check-input mb-1 mx-auto" type="radio" name="container_width" id="cw_full_width"
                        value="full_width">
                    <label class="form-check-label stretched-link small" for="cw_full_width">{{ translate('Full Width')
                        }}</label>
                </div>
            </div>
            <div id="custom_width_wrapper" class="mt-2 d-none">
                <input type="text" class="form-control form-control-sm" name="container_custom_width"
                    placeholder="e.g. 1200px or 90%">
            </div>
        </div>

        <hr class="border-secondary-subtle">
        <h6 class="mb-3 text-uppercase small text-muted fw-bold">{{ translate('Background') }}</h6>

        <div class="mb-3">
            <label class="form-label">{{ translate('Background Color') }}</label>
            <div class="colorpicker">
                <input type="text" class="form-control coloris" name="bg_color" value="#ffffff">
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">{{ translate('Background Image') }}</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" data-slide-toggle="#bg_image_wrapper">
                </div>
            </div>
            <div id="bg_image_wrapper" class="d-none">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="bg_image_display"
                        placeholder="{{ translate('No image selected') }}" readonly>
                    <input type="hidden" name="bg_image" id="bg_image_hidden">
                    <button type="button" class="btn btn-outline-secondary" onclick="$('#row-bg-file').click()"><i
                            class="bi bi-upload"></i></button>
                    <input type="file" id="row-bg-file" class="d-none" accept="image/*">
                </div>
                <div class="form-text small text-muted mb-2">{{ translate('Choose local image (will be uploaded on
                    save)') }}</div>

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
        <h6 class="mb-3 text-uppercase small text-muted fw-bold">{{ translate('Typography') }}</h6>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label small">{{ translate('Text Color') }}</label>
                <div class="colorpicker">
                    <input type="text" class="form-control coloris" name="text_color" value="#000000">
                </div>
            </div>
            <div class="col-6">
                <label class="form-label small">{{ translate('Font Family') }}</label>
                <select class="form-select selectpicker" name="font_family" data-live-search="true">
                    <option value="">{{ translate('Default') }}</option>
                    @foreach($googleFonts as $name => $family)
                    <option value="{{ $family }}"
                        data-content="<span style='font-family:{{ $family }}'>{{ $name }}</span>">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="border-secondary-subtle">
        <h6 class="mb-3 text-uppercase small text-muted fw-bold">{{ translate('Spacing') }}</h6>

        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label small fw-bold mb-0">{{ translate('Margin') }}</label>
            <select class="form-select form-select-md py-0 px-1" name="margin_unit" style="width:52px;height: 24px;">
                <option value="px">px</option>
                <option value="%">%</option>
                <option value="rem">rem</option>
            </select>
        </div>
        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text"><i class="bi bi-arrow-bar-up"></i></span>
            <input type="text" class="form-control" name="margin_top" placeholder="Top">
            <span class="input-group-text"><i class="bi bi-arrow-bar-right"></i></span>
            <input type="text" class="form-control" name="margin_right" placeholder="Right">
            <span class="input-group-text"><i class="bi bi-arrow-bar-down"></i></span>
            <input type="text" class="form-control" name="margin_bottom" placeholder="Bottom">
            <span class="input-group-text"><i class="bi bi-arrow-bar-left"></i></span>
            <input type="text" class="form-control" name="margin_left" placeholder="Left">
        </div>

        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label small fw-bold mb-0">{{ translate('Padding') }}</label>
            <select class="form-select form-select-md py-0 px-1" name="padding_unit" style="width:52px;height: 24px;">
                <option value="px">px</option>
                <option value="%">%</option>
                <option value="rem">rem</option>
            </select>
        </div>
        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text"><i class="bi bi-arrow-bar-up"></i></span>
            <input type="text" class="form-control" name="padding_top" placeholder="Top">
            <span class="input-group-text"><i class="bi bi-arrow-bar-left"
                    style="transform: rotate(180deg);"></i></span>
            <input type="text" class="form-control" name="padding_right" placeholder="Right">
            <span class="input-group-text"><i class="bi bi-arrow-bar-up" style="transform: rotate(180deg);"></i></span>
            <input type="text" class="form-control" name="padding_bottom" placeholder="Bottom">
            <span class="input-group-text"><i class="bi bi-arrow-bar-right"
                    style="transform: rotate(180deg);"></i></span>
            <input type="text" class="form-control" name="padding_left" placeholder="Left">
        </div>

        <hr class="border-secondary-subtle">
        <h6 class="mb-3 text-uppercase small text-muted fw-bold">{{ translate('Border') }}</h6>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label small">{{ translate('Width') }}</label>
                <input type="text" class="form-control form-control-sm" name="border_width" placeholder="0px">
            </div>
            <div class="col-6">
                <label class="form-label small">{{ translate('Color') }}</label>
                <div class="colorpicker">
                    <input type="text" class="form-control form-control-sm coloris" name="border_color" value="#dee2e6">
                </div>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small">{{ translate('Radius') }}</label>
                <input type="text" class="form-control form-control-sm" name="border_radius" placeholder="0px">
            </div>
            <div class="col-6">
                <label class="form-label small">{{ translate('Style') }}</label>
                <select class="form-select form-select-sm selectpicker" name="border_style">
                    <option value="none">{{ translate('None') }}</option>
                    <option value="solid">{{ translate('Solid') }}</option>
                    <option value="dashed">{{ translate('Dashed') }}</option>
                    <option value="dotted">{{ translate('Dotted') }}</option>
                </select>
            </div>
        </div>
</form>
