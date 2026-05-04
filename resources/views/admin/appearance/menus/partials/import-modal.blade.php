<x-modal
    id="importMenuModal"
    size="md"
    :title="translate('Import Menus')"
    icon="bi bi-box-arrow-in-down"
    :scrollable="true"
>
    <form id="importMenuForm" action="{{ route('admin.appearance.menus.import') }}" method="POST">
        @csrf
        <input type="hidden" name="to_location" value="{{ $location }}">
        <input type="hidden" name="from_location" id="importFromLocationHidden" value="">

        {{-- Source Location Select --}}
        <div class="location-select">
            <label class="form-label fw-medium">{{ translate('Import from') }}</label>
            <select class="form-select" id="importFromLocation" data-url="{{ route('admin.appearance.menus.menu-list') }}">
                <option value="">{{ translate('Select a location...') }}</option>
                @foreach($locations as $key => $label)
                    @if($key !== $location)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endif
                @endforeach
            </select>
            <div class="form-text">
                {{ translate('Import menus from another location to :location', ['location' => $locations[$location] ?? $location]) }}
            </div>
        </div>

        {{-- Menu List Container --}}
        <div id="importMenuListContainer" class="d-none mt-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="form-label fw-medium mb-0">{{ translate('Select menus to import') }}</label>
                <div class="form-check form-check-inline mb-0">
                    <input type="checkbox" class="form-check-input" id="importSelectAll">
                    <label class="form-check-label small" for="importSelectAll">{{ translate('Select All') }}</label>
                </div>
            </div>
            <div id="importMenuList" class="border rounded">
                {{-- Menu list will be loaded here via AJAX --}}
            </div>
        </div>
        {{-- Loading State --}}
        <div id="importMenuLoading" class="text-center py-4 d-none">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-2">{{ translate('Loading menus...') }}</span>
        </div>
    </form>
    <x-slot name="footer">
        <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>{{ translate('Cancel') }}
        </button>
        <button form="importMenuForm" class="btn btn-primary" id="importMenuBtn" disabled>
            <i class="bi bi-box-arrow-in-down me-2"></i>{{ translate('Import Selected') }}
        </button>
    </x-slot>
</x-modal>
