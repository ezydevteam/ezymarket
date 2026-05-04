@extends('admin.layouts.full')
@section('section', translate('Appearance'))
@section('title', translate(':theme_name Theme Settings', ['theme_name' => $theme->name]))
@section('content')
<div class="row g-4">
    <div class="col-lg-3">
        <div class="card rounded-4 sticky-top">
            <ul class="theme-list-group list-group list-group-flush">

                {{-- Root Items --}}
                @foreach ($menus['root'] as $groupKey)
                <a class="theme-list-group-item list-group-item list-group-item-action {{ $groupKey == $activeGroup ? 'active' : '' }}"
                    href="{{ route('admin.appearance.themes.settings.group', [$theme->id, $groupKey]) }}">
                    <span class="text-capitalize">{{ translate(str_replace('_', ' ', $groupKey)) }}</span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
                @endforeach

                {{-- Grouped Items --}}
                @foreach ($menus as $menuLabel => $subGroups)
                @if ($menuLabel === 'root') @continue @endif
                @php
                $isParentActive = in_array($activeGroup, $subGroups);
                $menuId = Str::slug($menuLabel);
                @endphp
                <a class="menu-group theme-list-group-item list-group-item list-group-item-action {{ $isParentActive ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" href="#menu_{{ $menuId }}" role="button"
                    aria-expanded="{{ $isParentActive ? 'true' : 'false' }}">
                    <span class="text-capitalize">{{ translate($menuLabel) }}</span>
                    <i class="bi bi-chevron-down transition-3 text-muted small"></i>
                </a>
                <div class="collapse {{ $isParentActive ? 'show' : '' }}" id="menu_{{ $menuId }}">
                    <div class="list-group list-group-flush border-start px-2 ms-3 my-1">
                        @foreach ($subGroups as $groupKey)
                        <a class="list-group-item list-group-item-action py-2 border-0 rounded-2 {{ $groupKey == $activeGroup ? 'active' : '' }}"
                            href="{{ route('admin.appearance.themes.settings.group', [$theme->id, $groupKey]) }}">
                            <span class="text-capitalize fs-14">
                                {{ translate(str_replace('_', ' ', str_replace('_page','', $groupKey))) }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="card rounded-4 h-100">
            <div
                class="card-header d-flex justify-content-between align-items-center rounded-4 rounded-bottom-0 px-3 py-2">
                <h6 class="mb-0">
                    <i class="bi bi-gear me-2"></i>{{ translate(':theme_name Theme Settings', ['theme_name' =>
                    $theme->name]) }}
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.appearance.themes.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}
                    </a>
                    <button type="submit" form="themeSettingsForm" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>{{ translate('Save') }}
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <form id="themeSettingsForm"
                    action="{{ route('admin.appearance.themes.settings.update', [$theme->id, $activeGroup]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row row-cols-1 g-3">
                        @foreach ($themeSettingsCollection as $themeSetting)
                        @if ($themeSetting->field === 'heading')
                        {{-- Section Heading / Group Title --}}
                        <div class="col-12 mt-2">
                            <div class="d-flex align-items-center gap-2">
                                @if(!empty($themeSetting->icon))
                                <i class="bi {{ $themeSetting->icon }} text-primary fs-5"></i>
                                @endif
                                <h6 class="fw-bold mb-0 text-dark text-capitalize">{{ translate($themeSetting->label) }}
                                </h6>
                            </div>
                            @if(!empty($themeSetting->description))
                            <p class="text-muted small mb-0">{{ translate($themeSetting->description) }}
                            </p>
                            @endif
                        </div>
                        @elseif ($themeSetting->field === 'divider')
                        {{-- Horizontal Rule Divider --}}
                        <div class="col-12">
                            <hr class="my-1 border-secondary-subtle">
                        </div>
                        @elseif ($themeSetting->field === 'input')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label">{{ translate($themeSetting->label) }}</label>
                            @if (isset($themeSetting->disabled) && $themeSetting->disabled)
                            <input type="{{ $themeSetting->type }}" class="form-control"
                                value="{{ $themeSetting->value }}" disabled>
                            @else
                            <input type="{{ $themeSetting->type }}" name="{{ $themeSetting->key }}" class="form-control"
                                value="{{ $themeSetting->value }}" {{ $themeSetting->required ? 'required' : '' }}>
                            @endif
                        </div>
                        @elseif ($themeSetting->field === 'number')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label">{{ translate($themeSetting->label) }}</label>
                            <input type="number" name="{{ $themeSetting->key }}" class="form-control"
                                value="{{ $themeSetting->value }}" min="{{ $themeSetting->min }}"
                                max="{{ $themeSetting->max }}" {{ $themeSetting->required ? 'required' : '' }}>
                        </div>
                        @elseif ($themeSetting->field === 'textarea')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label">{{ translate($themeSetting->label) }}</label>
                            <textarea name="{{ $themeSetting->key }}" class="form-control"
                                rows="{{ $themeSetting->rows }}" {{
                                $themeSetting->required ? 'required' : '' }}>{{ $themeSetting->value }}</textarea>
                        </div>
                        @elseif ($themeSetting->field === 'ckeditor')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label">{{ translate($themeSetting->label) }}</label>
                            <textarea name="{{ $themeSetting->key }}"
                                class="form-control ckeditor">{{ $themeSetting->value }}</textarea>
                        </div>
                        @elseif ($themeSetting->field === 'select')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label">{{ translate($themeSetting->label) }}</label>
                            <select name="{{ $themeSetting->key }}" class="form-select" {{ $themeSetting->required ?
                                'required' : '' }}>
                                @foreach ($themeSetting->options as $key => $value)
                                <option value="{{ $key }}" {{ $themeSetting->value == $key ? 'selected' : '' }}>
                                    {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        @elseif ($themeSetting->field === 'bootstrap-select')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label">{{ translate($themeSetting->label) }}</label>
                            <select name="{{ $themeSetting->key }}" class="form-select selectpicker"
                                title="{{ translate($themeSetting->label) }}"
                                data-live-search="{{ $themeSetting->search }}" {{ $themeSetting->required ? 'required' :
                                '' }}>
                                @foreach ($themeSetting->options as $key => $value)
                                <option value="{{ $key }}" {{ $themeSetting->value == $key ? 'selected' : '' }}>
                                    {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        @elseif ($themeSetting->field === 'checkbox')
                        <div class="{{ $themeSetting->col }}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="{{ $themeSetting->key }}"
                                    id="{{ $themeSetting->key }}" {{ $themeSetting->required ? 'required' : '' }}
                                {{ $themeSetting->value ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    {{ translate($themeSetting->label) }}
                                </label>
                            </div>
                        </div>
                        @elseif ($themeSetting->field === 'radios')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label d-block">{{ translate($themeSetting->label) }}</label>
                            @foreach ($themeSetting->options as $key => $value)
                            <div class="form-check d-inline-block me-2">
                                <input class="form-check-input" type="radio" name="{{ $themeSetting->key }}"
                                    id="{{ $themeSetting->key . $key }}" value="{{ $key }}" {{ $themeSetting->value ==
                                $key ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $themeSetting->key . $key }}">
                                    {{ $value }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @elseif ($themeSetting->field === 'icon-radios')
                        {{-- Radio button cards with Bootstrap Icons --}}
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label fw-medium d-block">{{ translate($themeSetting->label) }}</label>
                            @if(!empty($themeSetting->description))
                            <p class="text-muted small mb-2">{{ translate($themeSetting->description) }}</p>
                            @endif
                            <div class="row g-2">
                                @foreach ($themeSetting->options as $opt)
                                @php $optKey = $opt->value ?? $opt->key ?? null; @endphp
                                <div class="{{ $themeSetting->option_col ?? 'col-3' }}">
                                    <input type="radio" class="btn-check" name="{{ $themeSetting->key }}"
                                        id="{{ $themeSetting->key }}_{{ $optKey }}" value="{{ $optKey }}" {{
                                        ($themeSetting->value ?? '') == $optKey ? 'checked' : '' }}>
                                    <label
                                        class="btn btn-radio w-100 p-2 text-center d-flex flex-column align-items-center gap-1"
                                        for="{{ $themeSetting->key }}_{{ $optKey }}">
                                        @if(!empty($opt->icon))
                                        <i class="bi {{ $opt->icon }} fs-5 d-block"></i>
                                        @endif
                                        @if (!empty($opt->label))
                                        <span class="small lh-sm">{{ translate($opt->label) }}</span>
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @elseif ($themeSetting->field === 'toggle')
                        <div class="{{ $themeSetting->col }}">
                            <div class="form-check form-switch">
                                <input type="hidden" name="{{ $themeSetting->key }}" value="0">
                                <input class="form-check-input" type="checkbox" name="{{ $themeSetting->key }}"
                                    id="{{ $themeSetting->key }}" value="1" @checked($themeSetting->value ?? true)>
                                <label class="form-check-label fw-medium" for="{{ $themeSetting->key }}">{{
                                    translate($themeSetting->label) }}</label>
                            </div>
                        </div>
                        @elseif ($themeSetting->field === 'color')
                        <div class="{{ $themeSetting->col }}">
                            <label class="form-label">{{ translate($themeSetting->label) }}</label>
                            <div class="colorpicker">
                                <input type="text" name="{{ $themeSetting->key }}" class="form-control coloris"
                                    value="{{ $themeSetting->value }}" {{ $themeSetting->required ? 'required' : '' }}>
                            </div>
                        </div>
                        @elseif ($themeSetting->field === 'image')
                        <div class="{{ $themeSetting->col }}">
                            @if ($themeSetting->box_type == 'regular')
                            <div class="image-box p-4 border bg-light rounded-2">
                                <h5>{{ translate($themeSetting->label) }}</h5>
                                <div class="my-3">
                                    <img id="image-preview-{{ $loop->index }}"
                                        class="border p-2 rounded-2 {{ $themeSetting->box_bg }}"
                                        src="{{ asset($themeSetting->value) }}" alt="{{ $themeSetting->key }}"
                                        height="60px">
                                </div>
                                <input type="file" name="{{ $themeSetting->key }}" class="form-control image-input"
                                    data-id="{{ $loop->index }}" accept="{{ $themeSetting->accept }}">
                                @if ($themeSetting->description)
                                <div class="form-text mt-2">
                                    {{ translate($themeSetting->description) }}</div>
                                @endif
                            </div>
                            @elseif ($themeSetting->box_type == 'square-small')
                            <div class="my-3">
                                <div class="vironeer-image-preview {{ $themeSetting->box_bg }}">
                                    <img id="attach-image-preview-{{ $loop->index }}"
                                        src="{{ asset($themeSetting->value) }}" alt="{{ $themeSetting->key }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <input id="attach-image-targeted-input-{{ $loop->index }}" type="file"
                                    name="{{ $themeSetting->key }}" accept="{{ $themeSetting->accept }}"
                                    class="form-control" hidden>
                                <button data-id="{{ $loop->index }}" type="button"
                                    class="attach-image-button btn btn-secondary btn-lg w-100 mb-2"><i
                                        class="fas fa-camera me-2"></i>{{ translate('Choose ' . $themeSetting->label)
                                    }}</button>
                                @if ($themeSetting->description)
                                <small class="text-muted">{{ translate($themeSetting->description) }}</small>
                                @endif
                            </div>
                            @elseif ($themeSetting->box_type == 'square-large')
                            <div class="my-3">
                                <div class="vironeer-image-preview-box {{ $themeSetting->box_bg }}">
                                    <img id="attach-image-preview-{{ $loop->index }}"
                                        src="{{ asset($themeSetting->value) }}" alt="{{ $themeSetting->key }}"
                                        width="100%" height="200px">
                                </div>
                            </div>
                            <div class="mb-3">
                                <input id="attach-image-targeted-input-{{ $loop->index }}" type="file"
                                    name="{{ $themeSetting->key }}" accept="{{ $themeSetting->accept }}"
                                    class="form-control" hidden>
                                <button data-id="{{ $loop->index }}" type="button"
                                    class="attach-image-button btn btn-secondary btn-lg w-100 mb-2"><i
                                        class="fas fa-camera me-2"></i>{{ translate('Choose ' . $themeSetting->label)
                                    }}</button>
                                @if ($themeSetting->description)
                                <small class="text-muted">{{ translate($themeSetting->description) }}</small>
                                @endif
                            </div>
                            @elseif ($themeSetting->box_type == 'full')
                            <div class="vironeer-file-preview-box mb-3 {{ $themeSetting->box_bg }} p-4 text-center">
                                <div class="file-preview-box mb-3">
                                    <img id="attach-image-preview-{{ $loop->index }}"
                                        src="{{ asset($themeSetting->value) }}" alt="{{ $themeSetting->key }}"
                                        height="70px">
                                </div>
                                <button type="button" class="attach-image-button btn btn-secondary mb-2"
                                    data-id="{{ $loop->index }}"><i class="fas fa-camera me-2"></i>{{ translate('Choose
                                    ' . $themeSetting->label) }}</button>
                                <input id="attach-image-targeted-input-{{ $loop->index }}" type="file"
                                    name="{{ $themeSetting->key }}" accept="{{ $themeSetting->accept }}" hidden>
                                @if ($themeSetting->description)
                                <div class="form-text">{{ translate($themeSetting->description) }}
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @elseif ($themeSetting->field === 'alert')
                        <div class="col-12 mt-3">
                            <div class="alert alert-{{ $themeSetting->type ?? 'info' }} border-0 rounded-4 mb-0">
                                <div class="d-flex align-items-center gap-3">
                                    @if(!empty($themeSetting->icon))
                                    <i class="bi {{ $themeSetting->icon }} fs-4"></i>
                                    @endif
                                    <div>
                                        @if(!empty($themeSetting->label))
                                        <h6 class="alert-heading fw-bold mb-1">{{ translate($themeSetting->label) }}</h6>
                                        @endif
                                        <p class="mb-0 small">{{ translate($themeSetting->description) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/coloris/coloris.min.css') }}">
@endpush
@push('scripts_libs')
<script src="{{ asset('vendor/libs/coloris/coloris.min.js') }}"></script>
@endpush
@include('admin.partials.ckeditor')
@endsection
