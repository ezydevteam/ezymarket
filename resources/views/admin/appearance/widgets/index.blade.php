@extends('admin.layouts.full')
@section('section', translate('Appearance'))
@section('title', translate('Widgets'))
@section('container', 'container-max-xxl')
@section('content')
<div class="row g-4">
    {{-- Available Widgets Sidebar --}}
    <div class="col-lg-4 col-xl-3 order-2 order-lg-1">
        <div class="card mb-3 sticky-top">
            <div class="card-header p-3">
                <h6 class="mb-0">
                    <i class="bi bi-puzzle me-2 text-primary"></i>{{ translate('Available Widgets') }}
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="available-widgets-area widget-list p-2" id="availableWidgets">
                    @forelse($widgets as $widget)
                    <div class="widget-item-draggable p-2 mb-2 border rounded bg-light hover-secondary-light cursor-move transition-all"
                        data-widget-id="{{ $widget->id }}" data-widget-title="{{ $widget->title }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                                <i class="{{ $widget->icon ?? 'bi bi-puzzle' }} me-2 text-gray-700"></i>
                                <div class="text-truncate">
                                    <div class="fw-semibold small text-truncate">{{ $widget->title }}</div>
                                    @if($widget->description)
                                    <div class="text-muted fs-12">{{ truncateText($widget->description, 35) }}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Quick Add Dropdown --}}
                            <div class="dropdown ms-3 no-drag">
                                <button class="btn btn-sm p-1 text-primary" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false" title="{{ translate('Add to Area') }}">
                                    <i class="bi bi-plus-circle fs-6"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-light fit-content py-1">
                                    <li class="dropdown-header small text-uppercase fw-bold text-muted py-1">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        {{ translate('Add to') }}
                                    </li>
                                    @foreach($areas as $area)
                                    <li>
                                        <a class="dropdown-item py-1 fs-12 btn-quick-add" href="javascript:void(0)"
                                            data-area-id="{{ $area->value }}" data-widget-id="{{ $widget->id }}">
                                            <i class="{{ $area->icon() }} me-2 text-purple opacity-75"></i>
                                            {{ $area->label() }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>{{ translate('No widgets available') }}
                    </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Widget Areas --}}
    <div class="col-lg-8 col-xl-9 order-1 order-lg-2">
        <div class="row g-3" id="widgetAreasContainer">
            @foreach($areas as $area)
            @php $areaInstances = $instancesByArea[$area->value] ?? collect(); @endphp
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header px-3 py-2">
                        <h6 class="mb-0">
                            <i class="{{ $area->icon() }} me-2 text-purple"></i>{{ $area->label() }}
                            <div class="text-muted fs-12">{{ $area->description() }}</div>
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="widget-area-dropzone sortable-widgets bg-light rounded-2 p-2"
                            data-area-id="{{ $area->value }}">
                            @forelse($areaInstances as $instance)
                            @include('admin.appearance.widgets.partials.widget-item', ['instance' => $instance])
                            @empty
                            <div class="empty-zone text-center text-muted py-4 pe-none">
                                <i class="bi bi-plus-circle d-block mb-1"></i>
                                <small class="d-block">{{ translate('Add or drag widgets here') }}</small>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Widget Settings Modal --}}
@include('admin.appearance.widgets.partials.settings-offcanvas')
@endsection

@push('styles_libs')
<link href="{{ asset('vendor/libs/coloris/coloris.min.css') }}" rel="stylesheet" />
@endpush

@push('scripts_libs')
<script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/libs/coloris/coloris.min.js') }}"></script>
<script src="{{ asset_with_version('vendor/admin/js/widget.js') }}"></script>
@endpush

@push('scripts')
<script>
    $(function () {
        new WidgetManager({
            csrfToken: "{{ csrf_token() }}",
            routes: {
                store: "{{ route('admin.appearance.widgets.store') }}",
                sortable: "{{ route('admin.appearance.widgets.sortable') }}",
                instance: "{{ route('admin.appearance.widgets.instance', ':id') }}",
                update: "{{ route('admin.appearance.widgets.update', ':id') }}",
                toggle: "{{ route('admin.appearance.widgets.toggle', ':id') }}"
            },
            translations: {
                loading: "{{ translate('Loading...') }}",
                saving: "{{ translate('Saving...') }}",
                confirmDelete: "{{ translate('Are you sure you want to remove this widget?') }}",
                dragHere: "{{ translate('Drag widgets here') }}",
                failedToAdd: "{{ translate('Failed to add widget') }}",
                failedToSave: "{{ translate('Failed to save order') }}",
                failedToLoad: "{{ translate('Failed to load settings') }}",
                failedToUpdate: "{{ translate('Failed to save settings') }}",
                failedToToggle: "{{ translate('Failed to toggle widget') }}",
                failedToDelete: "{{ translate('Failed to delete widget') }}"
            }
        });
    });
</script>
@endpush
