@php
    $inputId = $inputId ?? 'input_' . uniqid();
    $name = $name ?? 'image';
    $label = $label ?? translate('Image');
    $value = $value ?? null;
    $required = $required ?? false;
    $previewClass = $previewClass ?? 'ratio ratio-16x9';
    $infoText = $infoText ?? translate('Supported formats: PNG, JPG, JPEG');
    $showRemove = $showRemove ?? true;
@endphp

<div class="image-upload-wrapper">
    <label for="{{ $inputId }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <div class="position-relative">
        <div class="image-preview-container {{ $previewClass }} bg-light border rounded overflow-hidden mb-2">
            @if($value)
                <img id="image-preview-{{ $inputId }}"
                    src="{{ asset($value) }}"
                    alt="{{ $label }}"
                    class="w-100 h-100 object-fit-cover">
            @else
                <img id="image-preview-{{ $inputId }}"
                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect width='100' height='100' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='14' fill='%236c757d'%3ENo Image%3C/text%3E%3C/svg%3E"
                    alt="{{ $label }}"
                    class="w-100 h-100 object-fit-cover">
            @endif
        </div>
        @if($showRemove && $value)
            <button type="button"
                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                id="{{ $inputId }}_remove"
                title="{{ translate('Remove image') }}">
                <i class="bi bi-trash"></i>
            </button>
        @endif
    </div>

    <div class="input-group">
        <input type="file"
            class="form-control image-input"
            id="{{ $inputId }}"
            name="{{ $name }}"
            data-id="{{ $inputId }}"
            accept="image/*"
            {{ $required ? 'required' : '' }}>
    </div>

    @if($value)
        <input type="hidden" name="{{ $name }}_current" value="{{ $value }}">
    @endif

    @if($infoText)
        <div class="form-text mt-2">
            {{ $infoText }}
        </div>
    @endif
</div>

@if($showRemove)
@push('scripts')
<script>
    (function() {
        const removeBtn = document.getElementById('{{ $inputId }}_remove');

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const fileInput = document.getElementById('{{ $inputId }}');
                const preview = document.getElementById('image-preview-{{ $inputId }}');
                const currentInput = document.querySelector('input[name="{{ $name }}_current"]');

                if (fileInput) {
                    fileInput.value = '';
                }

                if (currentInput) {
                    currentInput.value = '';
                } else {
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = '{{ $name }}_current';
                    deleteInput.value = '';
                    fileInput.parentNode.appendChild(deleteInput);
                }

                if (preview) {
                    preview.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect width='100' height='100' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='14' fill='%236c757d'%3ENo Image%3C/text%3E%3C/svg%3E";
                }

                removeBtn.remove();
            });
        }
    })();
</script>
@endpush
@endif
