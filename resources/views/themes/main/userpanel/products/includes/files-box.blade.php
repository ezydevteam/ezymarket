@php
$isEditable = isset($product) && $product->id && !$product->isDraft();
$productSettings = settings('product');
@endphp
<div id="upload-files-box" class="product-submission-card mb-4">
    <div class="card-v-header">
        <h5 class="submission-title"><span class="title-icon"><i class="bi bi-upload"></i>
            </span>{{ translate('Files Upload') }}</h5>
    </div>
    <div class="card-v-body">
        @if($category)
        <div id="dropzone-wrapper" class="dropzone-container">
            <div class="dropzone-wrapper">
                <div class="dropzone-drag" data-dz-click>
                    <div class="dropzone-drag-inner">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-upload fs-1"></i>
                        </div>
                        <h5>
                            {{ translate('Drag & Drop files here or click to browse') }}
                        </h5>
                        <p class="text-gray-600 mb-0 small">
                            {{ translate('Supported formats: :types and Maximum size: :max_file_size',[
                            'types' => strtoupper(str_replace(['.', ','], [' ', ', '],
                            $category->getAllowedFileTypes())),
                            'max_file_size' => formatFileSize(@$productSettings->max_file_size),],)
                            }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="dropzone-box">
                <div class="dropzone-box-cont">
                    <div class="dropzone-files">
                        <div class="dropzone-files-container">
                            <div id="dropzone" class="dropzone"></div>
                        </div>
                        <div id="upload-previews">
                            <div class="dz-preview dz-file-preview">
                                <div class="dz-fileicon">
                                    <img data-dz-thumbnail />
                                    <span class="bi bi-file-earmark" data-dz-extension></span>
                                </div>
                                <div class="dz-preview-content">
                                    <div class="dz-details">
                                        <div class="dz-details-info">
                                            <div class="dz-filename">
                                                <div class="dz-success-mark">
                                                    <span><i class="bi bi-check-circle"></i></span>
                                                </div>
                                                <div class="dz-error-mark">
                                                    <span><i class="bi bi-x-circle"></i></span>
                                                </div>
                                                <span data-dz-name></span>
                                                <div class="dz-size ms-1"></div>
                                            </div>
                                            <div class="dz-upload-percentage"></div>
                                        </div>
                                        <a class="dz-remove" data-dz-remove>
                                            <i class="bi bi-x fs-4"></i>
                                        </a>
                                    </div>
                                    <div class="dz-progress">
                                        <span class="dz-upload" data-dz-uploadprogress></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="uploaded-files">
            @foreach ($uploadedFiles ?? [] as $uploadedFile)
            <div class="uploaded-file uploaded-file-{{ hash_encode($uploadedFile->id) }}">
                <div class="uploaded-file-icon">
                    @if ($uploadedFile->isImage())
                    <img src="{{ $uploadedFile->getFileLink() }}" alt="{{ $uploadedFile->name }}" />
                    @else
                    <span class="bi bi-file-earmark-zip fs-3 text-primary"
                        data-type="{{ $uploadedFile->extension }}"></span>
                    @endif
                </div>
                <div class="uploaded-file-info">
                    <h6 class="uploaded-file-name"><span class="success-mark"><i
                                class="bi bi-check-circle me-1"></i></span>{{ $uploadedFile->getShortName() }}

                    </h6>
                    <p class="uploaded-file-time mb-0">{{ $uploadedFile->created_at->diffforhumans() }}<span
                            class="dot-seperator"></span>{{ $uploadedFile->getSize() }}</p>
                </div>
                <button type="button" class="uploaded-file-remove" data-id="{{ hash_encode($uploadedFile->id) }}"
                    data-delete-link="{{ route('user.product.files.delete', [hash_encode($category->id), hash_encode($uploadedFile->id)]) }}"
                    title="{{ translate('Remove') }}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
            <p class="mb-0">{{ translate('Please select a category first to enable file uploads.') }}</p>
        </div>
        @endif
    </div>
</div>

@if($category)
<div class="product-submission-card mb-4">
    <div class="card-v-header">
        <h5 class="submission-title"><span class="title-icon"><i class="bi bi-file-earmark-image"></i>
            </span>{{ translate('Preview & Main File') }}</h5>
    </div>
    <div class="row card-v-body g-3">
        {{-- Preview Image --}}
        @if (!$category->isAudioPreview())
        <div class="col-12 col-lg-6 product-submission-select">
            @if($isEditable && $product->preview_image)
            <div class="current-media-badge d-flex align-items-center gap-3 p-2 mb-3 bg-light rounded-2">
                <img src="{{ $product->preview_image_url }}" class="rounded object-fit-cover" width="40" height="30">
                <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>{{
                    translate('Currently assigned') }}</span>
            </div>
            @endif
            <label class="form-label fw-medium">{{ translate('Preview Image') }}
                @unless($isEditable)<span class="text-danger ms-1">*</span>@endunless
            </label>
            <select name="preview_image" class="form-select form-select-md selectpicker product-files-select"
                title="{{ $isEditable ? translate('Select to replace...') : translate('Select one') }}"
                data-live-search="true">
                @foreach ($uploadedFiles->filter->isImage() as $uploadedFile)
                <option value="{{ hash_encode($uploadedFile->id) }}"
                    data-content="{{ $uploadedFile->generateDataContent() }}"
                    data-width="{{ $uploadedFile->width ?? 0 }}" data-height="{{ $uploadedFile->height ?? 0 }}"
                    @selected(old('preview_image', $draft->preview_image ?? '') == hash_encode($uploadedFile->id) ||
                    ($draft->preview_image ?? '') == $uploadedFile->path)>
                </option>
                @endforeach
            </select>
            <div class="form-text">
                @if ($category->isImagePreview())
                {{ translate('Preview image should be :dimensions px and Max. :size', [
                'dimensions' => @$productSettings->max_preview_img_width . 'x' .
                @$productSettings->max_preview_img_height,
                'size' => formatFileSize($category->preview_file_size ?? @$productSettings->max_file_size)
                ]) }}
                @else
                {{ translate('Thumbnail image required (max 120x120 px, must be square).') }}
                @endif
            </div>
        </div>
        @endif

        {{-- Video Preview --}}
        @if ($category->isVideoPreview())
        <div class="col-12 col-lg-6 product-submission-select">
            @if($isEditable && $product->preview_video)
            <div class="current-media-badge mb-3 d-flex align-items-center gap-2 p-2 bg-light rounded-2">
                <i class="bi bi-camera-video fs-4 text-primary"></i>
                <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>{{ translate('Video
                    currently assigned') }}</span>
            </div>
            @endif
            <label class="form-label fw-medium">{{ translate('Video Preview') }}
                @unless($isEditable)<span class="text-danger ms-1">*</span>@endunless
            </label>
            <select name="preview_video" class="form-select form-select-md selectpicker product-files-select"
                title="{{ $isEditable ? translate('Select to replace...') : translate('Select one') }}"
                data-live-search="true">
                @foreach ($uploadedFiles->filter->isVideo() as $uploadedFile)
                <option value="{{ hash_encode($uploadedFile->id) }}"
                    data-content="{{ $uploadedFile->generateDataContent() }}" @selected(old('preview_video', $draft->
                    preview_video ?? '') == hash_encode($uploadedFile->id) || ($draft->preview_video ?? '') ==
                    $uploadedFile->path)>
                </option>
                @endforeach
            </select>
            <div class="form-text">
                {{ translate('Maximum size :file_size', ['file_size' =>
                formatFileSize($category->max_preview_file_size)]) }}
            </div>
        </div>
        @elseif($category->isAudioPreview())
        <div class="col-12 col-lg-6 product-submission-select">
            @if($isEditable && $product->preview_audio)
            <div class="current-media-badge mb-3 d-flex align-items-center gap-2 p-2 bg-light rounded-2">
                <i class="bi bi-music-note-beamed fs-4 text-primary"></i>
                <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>{{ translate('Audio
                    currently assigned') }}</span>
            </div>
            @endif
            <label class="form-label fw-medium">{{ translate('Audio Preview') }}
                @unless($isEditable)<span class="text-danger ms-1">*</span>@endunless
            </label>
            <select name="preview_audio" class="form-select form-select-md selectpicker product-files-select"
                title="{{ $isEditable ? translate('Select to replace...') : translate('Select one') }}"
                data-live-search="true">
                @foreach ($uploadedFiles->filter->isAudio() as $uploadedFile)
                <option value="{{ hash_encode($uploadedFile->id) }}"
                    data-content="{{ $uploadedFile->generateDataContent() }}" @selected(old('preview_audio', $draft->
                    preview_audio ?? '') == hash_encode($uploadedFile->id) || ($draft->preview_audio ?? '') ==
                    $uploadedFile->path)>
                </option>
                @endforeach
            </select>
            <div class="form-text">
                {{ translate('Maximum size :file_size', ['file_size' =>
                formatFileSize($category->max_preview_file_size)]) }}
            </div>
        </div>
        @endif

        {{-- Main File --}}
        @if (@$settings->product->external_file_link_option)
        <div class="col-12 col-lg-6 product-submission-select">
            @if($isEditable && $product->main_file)
            <div class="current-media-badge mb-3 d-flex align-items-center gap-2 p-2 bg-light rounded">
                <i class="bi bi-file-earmark-zip fs-5 text-primary"></i>
                <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>{{ translate('Main
                    file currently assigned') }}</span>
            </div>
            @endif
            <label class="form-label fw-medium">{{ translate('Main File') }}
                @unless($isEditable)<span class="text-danger ms-1">*</span>@endunless
            </label>
            <div class="form-group">
                <select id="mainFileSource" name="main_file_source" class="form-select first-input">
                    <option value="0" @selected(old('main_file_source')=='0' )>{{ translate('Upload') }}</option>
                    <option value="1" @selected(old('main_file_source')=='1' )>{{ translate('External') }}</option>
                </select>
                <select id="mainFileSelect" name="main_file"
                    class="form-select form-select-md selectpicker product-files-select second-input main-file-source-1"
                    title="{{ $isEditable ? translate('Select to replace...') : translate('Select one') }}"
                    @if(old('main_file_source')=='1' ) disabled @endif>
                    @foreach ($uploadedFiles->filter->isArchive() as $uploadedFile)
                    <option value="{{ hash_encode($uploadedFile->id) }}"
                        data-content="{{ $uploadedFile->generateDataContent() }}" @selected(old('main_file', $draft->
                        main_file['path'] ?? '') == hash_encode($uploadedFile->id) || ($draft->main_file['path'] ?? '')
                        == $uploadedFile->path)>
                    </option>
                    @endforeach
                </select>
                <input type="url" name="main_file"
                    class="form-control form-control-md selectpicker second-input main-file-source-2 @if(old('main_file_source') != '1') d-none @endif"
                    value="{{ old('main_file', ($draft->main_file['type'] ?? '') == 'external' ? ($draft->main_file['path'] ?? '') : '') }}"
                    placeholder="https://www.example.com/file.zip" @if(old('main_file_source') !='1' ) disabled @endif>
            </div>
            @php
            $fileTypesArray = explode(',', $category->main_file_types);
            $fileTypesArray = array_map(function ($type) {
            return '.' . trim($type);
            }, $fileTypesArray);
            $types = implode(', ', $fileTypesArray);
            @endphp
            <div class="form-text main-file-source-1 text-muted @if(old('main_file_source') == '1') d-none @endif">
                {{ translate('Main file should be :types and :size', [
                'types' => strtoupper($types), 'size' => formatFileSize($category->main_file_size ?? @$productSettings->max_file_size)
                ]) }}
            </div>
            <div class="form-text main-file-source-2 @if(old('main_file_source') != '1') d-none @endif">
                {{ translate('Enter the direct external URL to download the file.') }}
            </div>
        </div>
        @else
        <div class="col-12 col-lg-6 product-submission-select">
            @if($isEditable && $product->main_file)
            <div class="current-media-badge mb-3 d-flex align-items-center gap-2 p-2 bg-light rounded-2">
                <i class="bi bi-file-earmark-zip fs-6 text-primary"></i>
                <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i>{{ translate('Main
                    file currently assigned') }}</span>
            </div>
            @endif
            <label class="form-label fw-medium">{{ translate('Main File') }}
                @unless($isEditable)<span class="text-danger ms-1">*</span>@endunless
            </label>
            <select name="main_file" class="form-select form-select-md selectpicker product-files-select second-input"
                title="{{ $isEditable ? translate('Select to replace...') : translate('Select one') }}"
                data-live-search="true">
                @foreach ($uploadedFiles->filter->isArchive() as $uploadedFile)
                <option value="{{ hash_encode($uploadedFile->id) }}"
                    data-content="{{ $uploadedFile->generateDataContent() }}" @selected(old('main_file', $draft->
                    main_file['path'] ?? '') == hash_encode($uploadedFile->id) || ($draft->main_file['path'] ?? '') ==
                    $uploadedFile->path)>
                </option>
                @endforeach
            </select>
            <div class="form-text">
                @php
                $fileTypesArray = explode(',', $category->main_file_types);
                $fileTypesArray = array_map(function ($type) {
                return '.' . trim($type);
                }, $fileTypesArray);
                $types = implode(', ', $fileTypesArray);
                @endphp
                {{ translate('Main file should be :types and :size', [
                'types' => strtoupper($types), 'size' => formatFileSize($category->main_file_size ?? @$productSettings->max_file_size)
                ]) }}
            </div>
        </div>
        @endif

        {{-- Gallery --}}
        @if ($category->isImagePreview() && !empty($category->gallery_images_count))
        <div class="col-12 col-lg-6 product-submission-select">
            @if($isEditable && $product->gallery && count($product->gallery) > 0)
            <div class="current-media-badge mb-3 d-flex align-items-center gap-2 p-2 bg-light rounded-2 flex-wrap">
                @foreach(array_slice($product->gallery_links ?? [], 0, 4) as $link)
                <img src="{{ $link }}" class="rounded object-fit-cover" width="40" height="30">
                @endforeach
                @if(count($product->gallery) > 4)
                <span class="text-muted small">+{{ count($product->gallery) - 4 }} {{ translate('more') }}</span>
                @endif
                <span class="text-muted small ms-auto"><i class="bi bi-check-circle text-success me-1"></i>{{
                    translate('Gallery assigned') }}</span>
            </div>
            @endif
            <label class="form-label fw-medium">{{ translate('Gallery') }}</label>
            <select name="gallery[]" class="form-select form-select-md selectpicker product-files-select"
                title="{{ $isEditable ? translate('Select to replace...') : translate('Select one') }}"
                data-live-search="true" multiple>
                @foreach ($uploadedFiles->filter->isImage() as $uploadedFile)
                <option value="{{ hash_encode($uploadedFile->id) }}"
                    data-content="{{ $uploadedFile->generateDataContent() }}" @selected(old('gallery') ?
                    in_array(hash_encode($uploadedFile->id), old('gallery')) : in_array($uploadedFile->path,
                    $draft->gallery ?? []))>
                </option>
                @endforeach
            </select>
            <div class="form-text">
                {{ translate('Maximum :count images allowed.', ['count' => $category->gallery_images_count ?? 10]) }}
            </div>
        </div>
        @endif
    </div>
</div>
@endif

@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/dropzone/dropzone.min.css') }}">
@endpush

@push('scripts_libs')
<script src="{{ asset('vendor/libs/dropzone/dropzone.min.js') }}"></script>
@endpush

@if($category)
@themeInclude('userpanel.partials.upload-options')
@endif
