@extends('admin.layouts.form')
@section('section', translate('Custom Styles'))
@section('title', translate('Admin Panel Style'))
@section('description', translate('Customize the appearance of the admin panel with colors, typography, and custom
CSS.'))
@section('content')
<form id="ezydev-form" action="{{ route('admin.system.custom-style.index') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-paint-bucket me-2"></i>
            {{ translate('Colors') }}
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if(!empty($colors))
                @foreach ($colors as $key => $value)
                <div class="col-lg-6 col-xl-4">
                    @php
                    $labelMap = [
                    'body_bg_color' => 'Body Background Color',
                    'nav_sidebar_bg_color' => 'Nav & Sidebar Background Color',
                    'nav_sidebar_text_color' => 'Nav & Sidebar Text Color',
                    ];
                    $labelInfoMap = [
                    'primary_color' => 'Primary buttons, links, hovers and highlights',
                    'secondary_color' => 'Secondary buttons, body text',
                    'border_color' => 'Borders and dividers',
                    'body_bg_color' => 'Main body background',
                    'nav_sidebar_bg_color' => 'Header and sidebar background',
                    'nav_sidebar_text_color' => 'Header and sidebar text',
                    ];
                    $label = $labelMap[$key] ?? ucfirst(str_replace('_', ' ', $key));
                    @endphp
                    <label class="form-label">{{ translate($label) }}</label>
                    <div class="colorpicker">
                        <input type="text" name="system_admin[colors][{{ $key }}]" class="form-control coloris"
                            value="{{ $value }}" required>
                    </div>
                    @if (isset($labelInfoMap[$key]))
                    <small class="form-text text-muted">
                        {{ translate($labelInfoMap[$key]) }}
                    </small>
                    @endif
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-body-text me-2"></i>{{ translate('Typography') }}
        </div>
        <div class="card-body">
            @php
            $selectedType = $fonts['selected_type'] ?? 'system';
            $selectedGoogleFont = $fonts['google_font'] ?? '';
            $selectedCustomFont = isset($fonts['custom_font']['file']) ? $fonts['custom_font']['file'] : '';

            // Build selected value for unified dropdown
            $selectedFont = 'system';
            if ($selectedType === 'google' && $selectedGoogleFont) {
            $selectedFont = 'google:' . $selectedGoogleFont;
            } elseif ($selectedType === 'custom' && $selectedCustomFont) {
            $selectedFont = 'custom:' . $selectedCustomFont;
            }
            @endphp

            <div class="row g-3">
                <!-- Font Selection Dropdown -->
                <div class="col-md-6">
                    <label class="form-label">{{ translate('Select Font') }}</label>
                    <div class="input-group">
                        <select name="system_admin[selected_font]" class="form-select" id="fontSelect">
                            <!-- System Default -->
                            <option value="system" {{ $selectedFont==='system' ? 'selected' : '' }}>
                                {{ translate('System Default') }}
                            </option>

                            <!-- Google Fonts -->
                            <optgroup label="{{ translate('Google Fonts') }}">
                                @foreach($googleFonts as $font)
                                <option value="google:{{ $font }}" {{ $selectedFont==='google:' . $font ? 'selected'
                                    : '' }}>
                                    {{ $font }}
                                </option>
                                @endforeach
                            </optgroup>

                            <!-- Custom Fonts -->
                            @if(!empty($customFonts))
                            <optgroup label="{{ translate('Custom Fonts') }}">
                                @foreach($customFonts as $font)
                                <option value="custom:{{ $font['file'] }}" {{ $selectedFont==='custom:' . $font['file']
                                    ? 'selected' : '' }}>
                                    {{ $font['display'] }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        <button type="button" class="btn btn-danger d-none" id="deleteCustomFontBtn"
                            data-bs-toggle="tooltip" title="{{ translate('Delete this font') }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">
                        {{ translate('Choose from system fonts, Google Fonts, or your uploaded custom fonts') }}
                    </small>
                    <input type="hidden" name="delete_custom_font_file" id="deleteCustomFontFile" value="">
                </div>

                <!-- Upload New Custom Font -->
                <div class="col-md-6">
                    <label class="form-label">{{ translate('Upload Custom Font') }}</label>
                    <input type="file" name="custom_font_file" class="form-control" accept=".ttf,.otf,.woff,.woff2">
                    <small class="form-text text-muted">
                        {{ translate('Upload TTF, OTF, WOFF, WOFF2 (Max 5MB) - Will appear in the dropdown after
                        upload') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-filetype-css me-2"></i>{{ translate('Custom CSS') }}
        </div>
        <div class="card-body p-0">
            <textarea name="custom_css" id="css-editor" class="form-control">{{ $customCssFile }}</textarea>
        </div>
    </div>
</form>
@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/coloris/coloris.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/codemirror/codemirror.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/libs/codemirror/monokai.min.css') }}">
@endpush
@push('scripts_libs')
<script src="{{ asset('vendor/libs/coloris/coloris.min.js') }}"></script>
<script src="{{ asset('vendor/libs/codemirror/codemirror.min.js') }}"></script>
<script src="{{ asset('vendor/libs/codemirror/css.min.js') }}"></script>
<script src="{{ asset('vendor/libs/codemirror/sublime.min.js') }}"></script>
@endpush
@push('scripts')
<script>
    // Font Selection Handler
    const fontSelect = document.getElementById('fontSelect');
    const deleteCustomFontBtn = document.getElementById('deleteCustomFontBtn');
    const deleteCustomFontFile = document.getElementById('deleteCustomFontFile');

    // Show/hide delete button based on selection
    function updateDeleteButton() {
        const selectedValue = fontSelect.value;
        if (selectedValue.startsWith('custom:')) {
            deleteCustomFontBtn.classList.remove('d-none');
        } else {
            deleteCustomFontBtn.classList.add('d-none');
        }
    }

    if (fontSelect) {
        // Initial check
        updateDeleteButton();

        fontSelect.addEventListener('change', function () {
            const selectedValue = this.value;

            // Update delete button visibility
            updateDeleteButton();

            // Parse the value (format: "type:value" or just "system")
            if (selectedValue.startsWith('google:')) {
                const fontName = selectedValue.replace('google:', '');
                applyGoogleFont(fontName);
            } else {
                // For system or custom fonts, reset preview
                document.body.style.fontFamily = '';
                const existingLink = document.getElementById('admin-google-font-preview');
                if (existingLink) {
                    existingLink.remove();
                }
            }
        });
    }

    // Delete custom font handler
    if (deleteCustomFontBtn) {
        deleteCustomFontBtn.addEventListener('click', function () {
            const selectedValue = fontSelect.value;
            if (selectedValue.startsWith('custom:')) {
                const fontFile = selectedValue.replace('custom:', '');
                const fontName = fontSelect.options[fontSelect.selectedIndex].text;

                if (confirm('{{ translate("Are you sure you want to delete") }} ' + fontName + '?')) {
                    deleteCustomFontFile.value = fontFile;
                    document.getElementById('ezydev-form').submit();
                }
            }
        });
    }

    function applyGoogleFont(fontName) {
        // Remove previous Google Font link if exists
        const existingLink = document.getElementById('admin-google-font-preview');
        if (existingLink) {
            existingLink.remove();
        }

        // If no font selected, remove body font style
        if (!fontName) {
            document.body.style.fontFamily = '';
            return;
        }

        // Create Google Font URL
        const fontUrl = `https://fonts.googleapis.com/css2?family=${fontName.replace(/ /g, '+')}:wght@400;500;600;700&display=swap`;

        // Add Google Font link to head
        const link = document.createElement('link');
        link.id = 'admin-google-font-preview';
        link.rel = 'stylesheet';
        link.href = fontUrl;
        document.head.appendChild(link);

        // Apply font to body after font loads
        link.onload = function () {
            document.body.style.fontFamily = `'${fontName}', sans-serif`;
        };
    }
</script>
@endpush
@endsection
