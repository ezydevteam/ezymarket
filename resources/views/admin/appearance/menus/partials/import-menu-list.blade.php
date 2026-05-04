@if ($menus->count() > 0)
    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
        @foreach ($menus as $menu)
            <label class="list-group-item list-group-item-action">
                <input type="checkbox" class="form-check-input import-menu-item m-0" name="menu_ids[]" value="{{ $menu->id }}">
                <span>{{ $menu->name }}</span>
                @if($menu->children->count() > 0)
                    <span class="badge bg-text-secondary px-2 py-1 ms-1">{{ $menu->children->count() }} {{ translate('Submenu (s)') }}</span>
                @endif
            </label>
        @endforeach
    </div>
@else
    <x-empty message="{{ translate('No menus in this location') }}" description="" size="sm" icon="bi-list-nested" />
@endif
