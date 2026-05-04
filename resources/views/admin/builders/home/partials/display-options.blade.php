<hr class="my-3">
<h6 class="text-uppercase small text-muted fw-bold mb-3">{{ translate('Display Settings') }}</h6>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ translate('Cache Expiry (Minutes)') }}</label>
        <input type="number" name="cache_expiry_time" class="form-control" min="0"
            value="{{ $options['cache_expiry_time'] ?? 60 }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ translate('Visibility') }}</label>
        <select name="visibility" class="form-select selectpicker">
            <option value="all" @selected(($options['visibility'] ?? 'all' )=='all' )>{{ translate('All Devices') }}
            </option>
            <option value="desktop" @selected(($options['visibility'] ?? '' )=='desktop' )>{{ translate('Desktop Only')
                }}</option>
            <option value="mobile" @selected(($options['visibility'] ?? '' )=='mobile' )>{{ translate('Mobile Only') }}
            </option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ translate('Custom CSS Class') }}</label>
    <input type="text" name="custom_class" class="form-control" value="{{ $options['custom_class'] ?? '' }}"
        placeholder="e.g. my-custom-class">
</div>

<div class="form-check form-switch mb-3">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" id="block_status" name="is_active" value="1" @checked($isActive)>
    <label class="form-check-label" for="block_status">{{ translate('Active :block', ['block' => $homeBlock->title ??
        '']) }}</label>
</div>
