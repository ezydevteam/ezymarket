<form id="widgetSettingsForm" action="{{ route('admin.appearance.widgets.update', $instance->id) }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Widget Title --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">{{ translate('Widget Title') }}</label>
        <input type="text" class="form-control" name="title" value="{{ $instance->title }}"
            placeholder="{{ $widget->getTitle() }}">
        <small class="text-muted">{{ translate('Leave empty to use default widget title') }}</small>
    </div>

    {{-- Separate checkbox/switch fields from other fields --}}
    @php
    $bootstrapIcons = App\Classes\BootstrapIcons::all(true);
    $switchFields = [];
    $otherFields = [];
    foreach ($fields as $field) {
        $field = is_object($field) ? (array) $field : $field;

        // Skip show_title as it is handled globally in Widget Title Styling
        if (($field['name'] ?? '') === 'show_title') {
            continue;
        }

        if (($field['type'] ?? '') === 'checkbox') {
            $switchFields[] = $field;
        } else {
            $otherFields[] = $field;
        }
    }
    @endphp

    {{-- Regular Fields --}}
    @foreach($otherFields as $field)
    @php
    $fieldName = $field['name'];
    $fieldValue = $widgetSettings[$fieldName] ?? ($field['default'] ?? '');
    @endphp
    <div class="mb-3">
        @switch($field['type'])
        @case('text')
        @case('url')
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <input type="{{ $field['type'] }}" class="form-control" name="{{ $fieldName }}" value="{{ $fieldValue }}"
            placeholder="{{ $field['placeholder'] ?? '' }}" {{ !empty($field['required']) ? 'required' : '' }}>
        @break

        @case('number')
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <input type="number" class="form-control" name="{{ $fieldName }}" value="{{ $fieldValue }}"
            placeholder="{{ $field['placeholder'] ?? '' }}" min="{{ $field['min'] ?? '' }}"
            max="{{ $field['max'] ?? '' }}" {{ !empty($field['required']) ? 'required' : '' }}>
        @break

        @case('textarea')
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <textarea class="form-control" name="{{ $fieldName }}" rows="{{ $field['rows'] ?? 3 }}"
            placeholder="{{ $field['placeholder'] ?? '' }}" {{ !empty($field['required']) ? 'required' : ''
            }}>{{ $fieldValue }}</textarea>
        @break

        @case('code')
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <textarea class="form-control font-monospace" name="{{ $fieldName }}" rows="{{ $field['rows'] ?? 5 }}"
            placeholder="{{ $field['placeholder'] ?? '' }}" style="font-size: 13px;" {{ !empty($field['required'])
            ? 'required' : '' }}>{{ $fieldValue }}</textarea>
        @break

        @case('select')
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <select class="form-select selectpicker" name="{{ $fieldName }}" {{ !empty($field['required']) ? 'required' : '' }}>
            @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ $fieldValue==$optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
            @endforeach
        </select>
        @break

        @case('icon')
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <select class="form-select selectpicker" name="{{ $fieldName }}"
            data-live-search="true" {{ !empty($field['required']) ? 'required' : '' }}>
            <option value="">{{ translate('None') }}</option>
            @foreach($bootstrapIcons as $iconClass => $iconLabel)
            <option value="{{ $iconClass }}" data-icon="bi {{ $iconClass }}" {{ $fieldValue == $iconClass ? 'selected' : '' }}>
                {{ $iconLabel }}
            </option>
            @endforeach
        </select>
        @break

        @case('color')
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <div class="colorpicker">
            <input type="text" class="form-control coloris" name="{{ $fieldName }}" value="{{ $fieldValue }}"
                placeholder="{{ $field['placeholder'] ?? '' }}" {{ !empty($field['required']) ? 'required' : '' }}>
        </div>
        @break

        @case('image')
        @php
        $imageUrl = $fieldValue ? storageUrl($fieldValue) : null;
        @endphp
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <div class="input-group">
            <input type="file" class="form-control" name="{{ $fieldName }}" id="field_{{ $fieldName }}"
                accept="image/*">
            @if($imageUrl)
            <a href="{{ $imageUrl }}" target="_blank" class="btn btn-outline-secondary"
                title="{{ translate('View Current Image') }}">
                <i class="bi bi-eye"></i>
            </a>
            @endif
        </div>
        @if($imageUrl)
        <div class="mt-2">
            <img src="{{ $imageUrl }}" class="img-thumbnail" style="max-height: 80px;">
            <small class="text-muted d-block mt-1">
                {{ translate('Select a new image to replace the current one') }}
            </small>
        </div>
        @endif
        @break

        @case('gallery')
        @php
            $existingImages = $fieldValue;
            if (is_string($existingImages)) {
                $existingImages = json_decode($existingImages, true) ?? [];
            }
            $existingImages = array_filter($existingImages);
        @endphp
        <label class="form-label fw-semibold">{{ $field['label'] }}
            <span class="text-muted fw-normal">({{ translate('Max. 8 images') }})</span>
        </label>
        <!-- Image Upload Input -->
        <div class="gallery-upload-wrapper">
            <input type="file"
                   name="gallery_images[]"
                   id="galleryImagesInput_{{ $instance->id }}"
                   class="form-control gallery-images-input"
                   multiple
                   accept="image/*">
            <small class="text-muted d-block mt-1">{{ translate('Select multiple images to upload. Max 8 images total.') }}</small>
        </div>
        <!-- Image Previews Container -->
        <div class="gallery-previews mt-3" id="galleryPreviews_{{ $instance->id }}">
            <div class="row g-2" id="galleryPreviewsRow_{{ $instance->id }}">
                @foreach($existingImages as $index => $imagePath)
                    <div class="col-4 col-md-3 gallery-item" data-index="{{ $index }}">
                        <div class="position-relative border rounded overflow-hidden ratio ratio-1x1">
                            <img src="{{ storageUrl($imagePath) }}"
                                 class="w-100 h-100 object-fit-cover"
                                 alt="Gallery Image {{ $index + 1 }}">
                            <button type="button"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-gallery-image"
                                    data-index="{{ $index }}"
                                    title="{{ translate('Remove') }}">
                                <i class="bi bi-x"></i>
                            </button>
                            <input type="hidden" name="existing_images[]" value="{{ $imagePath }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Hidden input to track removed images -->
        <div id="removedImagesContainer_{{ $instance->id }}"></div>
        @break

        @default
        <label class="form-label fw-semibold">{{ $field['label'] }}</label>
        <input type="text" class="form-control" name="{{ $fieldName }}" value="{{ $fieldValue }}"
            placeholder="{{ $field['placeholder'] ?? '' }}">
        @endswitch

        @if(!empty($field['help']))
        <small class="text-muted">{{ $field['help'] }}</small>
        @endif
    </div>
    @endforeach

    {{-- Switch Fields in 2 columns --}}
    @if(count($switchFields) > 0)
    <div class="row g-2">
        @foreach($switchFields as $field)
        @php
        $fieldName = $field['name'];
        $fieldValue = $widgetSettings[$fieldName] ?? ($field['default'] ?? false);
        // Convert string "on" or truthy values to boolean
        $isChecked = $fieldValue === 'on' || $fieldValue === true || $fieldValue === 1 || $fieldValue === '1';
        @endphp
        <div class="col-6">
            <div class="form-check form-switch">
                {{-- Hidden field to ensure unchecked value is sent --}}
                <input type="hidden" name="{{ $fieldName }}" value="0">
                <input type="checkbox" class="form-check-input" name="{{ $fieldName }}" id="field_{{ $fieldName }}"
                    value="1" {{ $isChecked ? 'checked' : '' }}>
                <label class="form-check-label" for="field_{{ $fieldName }}">
                    {{ $field['label'] }}
                </label>
            </div>
            @if(!empty($field['help']))
            <small class="text-muted">{{ $field['help'] }}</small>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Global Widget Title Settings --}}
    <div class="border-bottom pb-2 my-3 fw-medium text-muted text-uppercase fs-14">{{ translate('Widget Title Styling') }}</div>
    <div class="row g-3">
        <div class="col-12">
            <div class="form-check form-switch">
                <input type="hidden" name="show_title" value="0">
                <input type="checkbox" class="form-check-input" name="show_title" id="show_title_switch"
                    value="1" {{ ($widgetSettings['show_title'] ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="show_title_switch">{{ translate('Show Widget
                    Title') }}</label>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label small text-muted mb-1">{{ translate('Title Style') }}</label>
            <select name="title_style" class="form-select selectpicker">
                @php $currentStyle = $widgetSettings['title_style'] ?? 'default'; @endphp
                <option value="default" {{ $currentStyle=='default' ? 'selected' : '' }}>{{ translate('Default')
                    }}</option>
                <option value="style_1" {{ $currentStyle=='style_1' ? 'selected' : '' }}>{{ translate('Style 1')
                    }}</option>
                <option value="style_2" {{ $currentStyle=='style_2' ? 'selected' : '' }}>{{ translate('Style 2')
                    }}</option>
                <option value="style_3" {{ $currentStyle=='style_3' ? 'selected' : '' }}>{{ translate('Style 3')
                    }}</option>
                <option value="style_4" {{ $currentStyle=='style_4' ? 'selected' : '' }}>{{ translate('Style 4')
                    }}</option>
                <option value="style_5" {{ $currentStyle=='style_5' ? 'selected' : '' }}>{{ translate('Style 5')
                    }}</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small text-muted mb-1">{{ translate('Title Color') }}</label>
            <div class="colorpicker">
                <input type="text" class="form-control coloris" name="title_color"
                    value="{{ $widgetSettings['title_color'] ?? '#000000' }}">
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label small text-muted mb-1">{{ translate('Title Icon') }}</label>
            <select name="title_icon" class="form-select selectpicker" data-live-search="true">
                <option value="">{{ translate('None') }}</option>
                @php $currentIcon = $widgetSettings['title_icon'] ?? ''; @endphp
                @foreach($bootstrapIcons as $iconClass => $iconLabel)
                <option value="{{ $iconClass }}" data-icon="bi {{ $iconClass }}" {{ $currentIcon==$iconClass
                    ? 'selected' : '' }}>
                    {{ $iconLabel }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small text-muted mb-1">{{ translate('Title Size') }}</label>
            <select name="title_size" class="form-select selectpicker">
                @php $currentSize = $widgetSettings['title_size'] ?? 'fs-5'; @endphp
                <option value="fs-3" {{ $currentSize=='fs-3' ? 'selected' : '' }}>Extra Large</option>
                <option value="fs-4" {{ $currentSize=='fs-4' ? 'selected' : '' }}>Large</option>
                <option value="fs-5" {{ $currentSize=='fs-5' ? 'selected' : 'fs-5' }}>Medium</option>
                <option value="fs-6" {{ $currentSize=='fs-6' ? 'selected' : '' }}>Small</option>
                <option value="fs-14" {{ $currentSize=='fs-14' ? 'selected' : '' }}>Extra Small</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small text-muted mb-1">{{ translate('Text Transform') }}</label>
            <select name="title_transform" class="form-select selectpicker">
                @php $currentTransform = $widgetSettings['title_transform'] ?? 'text-capitalize'; @endphp
                <option value="text-none" {{ $currentTransform=='text-none' ? 'selected' : '' }}>{{
                    translate('None') }}</option>
                <option value="text-capitalize" {{ $currentTransform=='text-capitalize' ? 'selected' : '' }}>
                    Capitalize</option>
                <option value="text-uppercase" {{ $currentTransform=='text-uppercase' ? 'selected' : '' }}>
                    UPPERCASE</option>
                <option value="text-lowercase" {{ $currentTransform=='text-lowercase' ? 'selected' : '' }}>
                    lowercase</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small text-muted mb-1">{{ translate('Font Weight') }}</label>
            <select name="title_weight" class="form-select selectpicker">
                @php $currentWeight = $widgetSettings['title_weight'] ?? 'fw-medium'; @endphp
                <option value="fw-normal" {{ $currentWeight=='fw-normal' ? 'selected' : '' }}>{{
                    translate('Normal') }}</option>
                <option value="fw-medium" {{ $currentWeight=='fw-medium' ? 'selected' : '' }}>{{
                    translate('Medium') }}</option>
                <option value="fw-semibold" {{ $currentWeight=='fw-semibold' ? 'selected' : '' }}>{{
                    translate('Semi Bold') }}</option>
                <option value="fw-bold" {{ $currentWeight=='fw-bold' ? 'selected' : '' }}>{{ translate('Bold')
                    }}</option>
                <option value="fw-bolder" {{ $currentWeight=='fw-bolder' ? 'selected' : '' }}>{{
                    translate('Bolder') }}</option>
            </select>
        </div>
    </div>

    {{-- Global Widget Card Settings --}}
    <div class="border-bottom pb-2 my-3 fw-medium text-muted text-uppercase fs-14">{{ translate('Widget Card Styling') }}</div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label small text-muted mb-1">{{ translate('Card Style') }}</label>
            <select name="widget_card_style" class="form-select selectpicker">
                @php $currentCardStyle = $widgetSettings['widget_card_style'] ?? 'default'; @endphp
                <option value="card-border" {{ $currentCardStyle=='card-border' ? 'selected' : 'card-border' }}>{{ translate('Card with Border') }}</option>
                <option value="card-shadow" {{ $currentCardStyle=='card-shadow' ? 'selected' : '' }}>{{ translate('Card with Shadow') }}</option>
                <option value="modern-card" {{ $currentCardStyle=='modern-card' ? 'selected' : '' }}>{{ translate('Modern Card') }}</option>
                <option value="modern-card-2" {{ $currentCardStyle=='modern-card-2' ? 'selected' : '' }}>{{ translate('Modern Card 2') }}</option>
                <option value="none" {{ $currentCardStyle=='none' ? 'selected' : '' }}>{{ translate('None') }}</option>
            </select>
        </div>
    </div>
</form>
