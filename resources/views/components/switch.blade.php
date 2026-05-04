@props([
'name' => 'is_active',
'id' => null,
'label' => 'Status',
'onLabel' => 'Active',
'offLabel' => 'Inactive',
'checked' => false,
'value' => '1',
'size' => 'xl',
'showLabel' => true,
])

@php
$switchId = $id ?? $name . '-switch-' . uniqid();
$sizeClass = 'ezydev-switch-wrapper-' . $size;
@endphp

@if($showLabel)
<label class="form-label d-block">{{ translate($label) }}</label>
@endif
<div class="ezydev-switch {{ $sizeClass }}">
    <input type="checkbox" class="ezydev-switch-input" name="{{ $name }}" id="{{ $switchId }}" value="{{ $value }}" {{
        $checked ? 'checked' : '' }} {{ $attributes }}>
    <label class="ezydev-switch-label" for="{{ $switchId }}">
        <span class="ezydev-switch-slider">
            <span class="ezydev-switch-button">
                <span class="ezydev-switch-on">{{ translate($onLabel) }}</span>
                <span class="ezydev-switch-off">{{ translate($offLabel) }}</span>
            </span>
        </span>
    </label>
</div>
