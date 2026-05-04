@if ($menus->count() > 0)
    <div class="card" id="menuItemsCard">
        {{-- Toolbar --}}
        <div class="card-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 px-3 py-2">
            <div class="d-flex align-items-center gap-2">
                {{-- Location Select --}}
                <div class="input-group input-group-sm" style="width: auto;">
                    <select class="form-select fw-medium" id="menuLocationSelect">
                        @foreach($locations as $key => $label)
                            <option value="{{ route('admin.appearance.menus.index', ['location' => $key]) }}" {{ $location === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn bg-text-dark fw-light hover-opacity d-none" id="menuLocationBtn">
                        {{ translate('Select') }}
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                 {{-- Bulk Delete --}}
                <button type="button" class="btn btn-sm btn-outline-danger d-none" id="menuBulkDeleteBtn" data-url="{{ route('admin.appearance.menus.bulk-delete') }}">
                    <i class="bi bi-trash me-1"></i>{{ translate('Delete Selected') }}
                </button>
                <div class="form-check mb-0">
                    <input type="checkbox" class="form-check-input" id="selectAllMenus">
                    <label class="form-check-label small text-muted" for="selectAllMenus">{{ translate('Select All') }}</label>
                </div>
                <div class="vr mx-3"></div>
                {{-- Search Toggle --}}
                <div class="d-flex align-items-center menu-search-wrapper">
                    <div class="menu-search-box d-none">
                        <div class="input-group input-group-sm" style="width: 240px;">
                            <input type="text" class="form-control" id="menuSearchInput" placeholder="{{ translate('Search :location menus...', ['location' => $location]) }}">
                            <button type="button" class="btn btn-outline-primary border-2" id="menuSearchClose" title="{{ translate('Close Search') }}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-link text-muted p-0" id="menuSearchToggle">
                        <i class="bi bi-search"></i> {{ translate('Search') }}
                    </button>
                </div>
                <div class="vr mx-2"></div>
                {{-- Import Button --}}
                <button type="button" class="btn btn-link text-muted p-0" data-bs-toggle="modal" data-bs-target="#importMenuModal">
                    <i class="bi bi-box-arrow-in-down"></i> {{ translate('Import') }}
                </button>
            </div>
        </div>
        <div class="dd nestable" data-location="{{ $location }}">
            <ol class="dd-list">
                @foreach ($menus as $menu)
                    @include('admin.appearance.menus.partials.menu-item', ['menu' => $menu, 'level' => 0])
                @endforeach
            </ol>
        </div>
        {{-- No Search Result --}}
        <x-empty id="menuSearchNoResult" class="d-none" />
    </div>
    {{-- Edit Modals --}}
    @foreach ($menus as $menu)
        @include('admin.appearance.menus.partials.details-modal', ['menu' => $menu])
    @endforeach
@else
    <div class="card" id="menuItemsCard">
        {{-- Toolbar with location select even when empty --}}
        <div class="card-header d-flex align-items-center justify-content-between p-2">
            <div class="input-group input-group-sm" style="width: auto;">
                <select class="form-select" id="menuLocationSelect">
                    @foreach($locations as $key => $label)
                        <option value="{{ route('admin.appearance.menus.index', ['location' => $key]) }}" {{ $location === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn bg-text-dark fw-light hover-opacity d-none" id="menuLocationBtn">
                    {{ translate('Select') }}
                </button>
            </div>
            {{-- Import Button --}}
            <button type="button" class="btn btn-link text-muted p-0" data-bs-toggle="modal" data-bs-target="#importMenuModal">
                <i class="bi bi-box-arrow-in-down"></i> {{ translate('Import') }}
            </button>
        </div>
        <x-empty size="lg" />
    </div>
@endif
