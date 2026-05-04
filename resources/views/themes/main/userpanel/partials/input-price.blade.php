@isset($label)
    <label class="form-label fw-medium">{{ $label }} {!! isset($star_required) ? '<span class="text-danger ms-1">*</span>' : '' !!}</label>
@endisset
<div class="input-group">
    <span class="input-group-text px-3 bg-white" data-bs-toggle="tooltip"
        data-bs-title="{{ currentCurrency()->code }}">{{ currentCurrency()->symbol }}</span>
    <input {{ isset($id) ? 'id=' . $id : '' }} type="number" {{ isset($name) ? 'name=' . $name : '' }}
        class="form-control form-control-md {{ $input_classes ?? '' }}" step="any"
        placeholder="{{ isset($placeholder) ? $placeholder : '0.00' }}"
        value="{{ isset($value) ? $value : (isset($name) ? old($name) : '') }}"
        {{ isset($min) ? 'min=' . $min : '' }} {{ isset($max) ? 'max=' . $max : '' }} @disabled($disabled ?? false)
        @required($required ?? false)>
</div>

