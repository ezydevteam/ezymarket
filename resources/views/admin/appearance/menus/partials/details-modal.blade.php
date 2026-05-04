<x-modal id="editMenuModal-{{ $menu->id }}"
         :title="translate('Edit Menu')"
         icon="bi bi-pencil-square"
         size="lg"
         :scrollable="true">

    <form id="editMenuForm-{{ $menu->id }}"
          action="{{ route('admin.appearance.menus.update', $menu->id) }}"
          method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="location" value="{{ $menu->location->value }}">

        <div class="row g-3">
            {{-- Name --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Name') }}
                    <span class="text-danger">*</span>
                </label>
                <input type="text"
                       name="name"
                       class="form-control form-control-md"
                       value="{{ $menu->name }}"
                       placeholder="{{ translate('Menu item name') }}"
                       required>
            </div>

            {{-- URL / Slug --}}
            <div class="col-md-6">
                <label class="form-label">{{ translate('URL / Slug') }}</label>
                <input type="text"
                       name="slug"
                       class="form-control form-control-md"
                       value="{{ $menu->slug }}"
                       placeholder="/page-slug or https://example.com">
            </div>

            {{-- Menu Type --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Menu Type') }}
                    <span class="text-danger">*</span>
                </label>
                <select name="menu_type"
                    class="form-select form-select-md selectpicker"
                    id="menuTypeSelect-{{ $menu->id }}"
                    data-conditional-toggle="#hideName-{{ $menu->id }}"
                    data-conditional-value="{{ \App\Enums\Menu\MenuType::HEADING->value }}"
                    data-conditional-logic="equal"
                    required>
                    @foreach($menuTypes as $key => $label)
                        <option value="{{ $key }}" {{ $menu->menu_type->value === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                {{-- Hide Name Switch (only for heading type) --}}
                <div class="form-check form-switch d-none mt-2" id="hideName-{{ $menu->id }}">
                    <input class="form-check-input"
                           type="checkbox"
                           name="hide_name"
                           id="hide_name_{{ $menu->id }}"
                           value="1"
                           {{ $menu->hide_name ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_name_{{ $menu->id }}">
                        {{ translate('Hide name in dropdown') }}
                    </label>
                </div>
            </div>

            {{-- Menu Style --}}
            <div class="col-md-6">
                <label class="form-label">
                    {{ translate('Menu Style') }}
                </label>
                <select name="menu_style" class="form-select form-select-md selectpicker" id="menuStyleSelect-{{ $menu->id }}">
                    <option value="">{{ translate('-- Default style --') }}</option>
                    @foreach($menuStyles as $key => $label)
                        <option value="{{ $key }}" {{ $menu->menu_style?->value === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">
                    {{ translate('This option may not work unless the menu type is set to Mega-menu.') }}
                </div>
            </div>
        </div>

        <hr class="my-4" />

        {{-- Badge Section --}}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ translate('Badge Text') }}</label>
                <input type="text"
                       name="badge"
                       class="form-control form-control-md"
                       value="{{ $menu->badge }}"
                       placeholder="{{ translate('e.g., New, Hot, Sale') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Badge Color') }}</label>
                <select name="badge_color" class="form-select form-select-md selectpicker" id="badgeColorSelect-{{ $menu->id }}" data-live-search="true">
                    <option value="">{{ translate('Select color') }}</option>
                    @foreach($badgeColors as $badgeClass => $badgeLabel)
                        <option value="{{ $badgeClass }}" {{ $menu->badge_color === $badgeClass ? 'selected' : '' }}>
                            {{ $badgeLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="my-4" />

        {{-- Icon Section --}}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ translate('Choose Icon') }}</label>
                <select name="icon" class="form-select form-select-md selectpicker" id="iconSelect-{{ $menu->id }}" data-live-search="true">
                    <option value="">{{ translate('-- No Icon --') }}</option>
                    @foreach($icons as $iconClass => $iconLabel)
                        <option value="{{ $iconClass }}"
                            data-content="<i class='bi {{ $iconClass }} me-2'></i> {{ $iconLabel }}"
                            {{ $menu->icon === $iconClass ? 'selected' : '' }}>
                            {{ $iconLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ translate('Icon Color') }}</label>
                <select name="icon_color" class="form-select form-select-md selectpicker" id="iconColorSelect-{{ $menu->id }}" data-live-search="true">
                    <option value="">{{ translate('-- Default Color --') }}</option>
                    @foreach($iconColors as $colorClass => $colorLabel)
                        <option value="{{ $colorClass }}" {{ $menu->icon_color === $colorClass ? 'selected' : '' }}>
                            {{ $colorLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="my-4" />

        {{-- Advanced Options --}}
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ translate('Custom CSS Class') }}</label>
                <input type="text"
                       name="custom_class"
                       class="form-control form-control-md"
                       value="{{ $menu->custom_class }}"
                       placeholder="my-custom-class">
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mt-3 pt-2">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_active"
                           id="is_active_{{ $menu->id }}"
                           value="1"
                           {{ $menu->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active_{{ $menu->id }}">
                        {{ translate('Active') }}
                    </label>
                </div>
                <div class="form-text">{{ translate('Inactive menus are hidden from the frontend') }}</div>
            </div>
            <div class="col-12">
                <label class="form-label">{{ translate('Custom HTML') }}</label>
                <textarea name="custom_html"
                          class="form-control"
                          rows="3"
                          placeholder="{{ translate('Optional HTML content') }}">{{ $menu->custom_html }}</textarea>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <div class="d-flex align-items-center gap-3 w-100">
            <form id="editMenuDeleteForm-{{ $menu->id }}"
                class="w-50"
                action="{{ route('admin.appearance.menus.destroy', $menu->id) }}"
                method="POST"
                data-ajax-confirm="true">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="btn bg-text-red hover-opacity action-confirm w-100"
                        id="editMenuDeleteBtn-{{ $menu->id }}"
                        data-confirm="{{ translate('Are you sure want to delete this menu? If it has child menus, they will also be deleted.') }}">
                    <i class="bi bi-trash me-2"></i>{{ translate('Delete') }}
                </button>
            </form>
            <button type="submit"
                    form="editMenuForm-{{ $menu->id }}"
                    id="editMenuBtn-{{ $menu->id }}"
                    class="btn btn-primary w-50">
                <i class="bi bi-check-circle me-2"></i>{{ translate('Update') }}
            </button>
        </div>
    </x-slot>
</x-modal>

{{-- Recursively include for children --}}
@foreach ($menu->children as $child)
    @include('admin.appearance.menus.partials.details-modal', ['menu' => $child])
@endforeach
