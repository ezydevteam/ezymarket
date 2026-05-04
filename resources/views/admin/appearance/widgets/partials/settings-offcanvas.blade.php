<x-offcanvas id="widgetSettingsCanvas"
    :title="translate('Widget Settings')"
    icon="bi-gear"
    placement="end">
    {{-- Settings form loaded via AJAX --}}
    <x-loader id="widgetSettingsLoader" centered />

    <x-slot name="footer">
        <button type="button" class="btn btn-primary w-100" id="widgetSettingsBtn">
            <i class="bi bi-check2-circle me-2"></i>{{ translate('Save Changes') }}
        </button>
    </x-slot>
</x-offcanvas>
