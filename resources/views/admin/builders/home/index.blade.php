@extends('admin.layouts.full')
@section('section', translate('Site Builders'))
@section('title', translate('Home Builder'))
@section('container', 'container-max-xxl')
@section('content')
    <div id="homeBuilder" class="row g-3">
        {{-- Sidebar: Content Blocks --}}
        <div class="col-lg-3 order-2 order-lg-1">
            <div class="card sticky-top" style="top: 60px;">
                <div class="card-header py-2">
                    <h5 class="mb-0">
                        <i class="bi bi-puzzle me-2 text-primary"></i>
                        {{ translate('Content Blocks') }}
                    </h5>
                </div>
                <div class="card-body p-0 sidebar-scrollable">
                    <div class="accordion accordion-flush" id="available-blocks">
                        @foreach($homeBlocks as $groupName => $blocks)
                            <div class="accordion-item">
                                <h6 class="accordion-header" id="heading{{ Str::slug($groupName) }}">
                                    <button class="accordion-button shadow-none fw-medium text-dark bg-white {{ $loop->first ? '' : 'collapsed' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ Str::slug($groupName) }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="collapse{{ Str::slug($groupName) }}">
                                        {{ translate($groupName) }}
                                    </button>
                                </h6>
                                <div id="collapse{{ Str::slug($groupName) }}"
                                    class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                    aria-labelledby="heading{{ Str::slug($groupName) }}"
                                    data-bs-parent="#available-blocks">
                                    <div class="accordion-body p-2">
                                        <div class="row g-2">
                                            @foreach ($blocks as $block)
                                                <div class="col-6">
                                                    <div class="builder-item p-3 bg-white border rounded shadow-sm cursor-grab d-flex flex-column align-items-center justify-content-center text-center h-100 position-relative"
                                                         data-id="{{ $block['id'] }}"
                                                         data-title="{{ $block['title'] }}"
                                                         data-icon="{{ $block['icon'] }}"
                                                         data-status="1">
                                                        <i class="{{ $block['icon'] }} fs-4 text-muted mb-2"></i>
                                                        <div class="w-100 text-wrap fw-medium text-dark small lh-sm" style="font-size: 12px;">{{ $block['title'] }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Main: Layout Canvas --}}
        <div class="col-lg-9 order-1 order-lg-2">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between border-0 rounded-3 py-2">
                    <h5 class="mb-0">
                        <i class="bi bi-layout-split me-2 text-primary"></i>
                        {{ translate('Home Layout') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary" id="save-builder-btn">
                            <i class="bi bi-save me-2"></i>{{ translate('Save') }}
                        </button>
                    </div>
                </div>
            </div>

            <div id="builder-canvas" class="d-block mb-4 position-relative" style="min-height: 250px;">
                <x-loader id="builderCanvasLoader" centered />
            </div>
        </div>
    </div>

    {{-- Edit Offcanvas --}}
    <x-offcanvas
        id="editHomeSectionOffcanvas"
        :title="translate('Edit Details')"
        icon="bi-pencil-square"
        placement="end">

        <x-loader id="offcanvasLoader" centered />
        <div id="offcanvasContent" class="d-none"></div>

        <x-slot:footer>
            <button type="submit" form="editHomeBlockForm" class="btn btn-primary w-100" id="editHomeBlockBtn">
                <i class="bi bi-check2-circle me-2"></i>{{ translate('Save Changes') }}
            </button>
        </x-slot:footer>
    </x-offcanvas>

    {{-- Row Options Offcanvas --}}
    <x-offcanvas
        id="sectionSettingsOffcanvas"
        :title="translate('Section Settings')"
        icon="bi-sliders2-vertical"
        placement="end">

        <input type="hidden" id="currentSectionId" value="">
        <x-loader id="sectionSettingsLoader" centered />
        <div id="sectionSettingsContent" class="d-none"></div>

         <x-slot:footer>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" id="saveSectionSettingsBtn">
                    <i class="bi bi-check2 me-1"></i>{{ translate('Apply Styles') }}
                </button>
            </div>
         </x-slot:footer>
    </x-offcanvas>
@endsection

@push('styles_libs')
    <link href="{{ asset('vendor/libs/jquery/jquery-ui.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/libs/coloris/coloris.min.css') }}" rel="stylesheet" />
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/coloris/coloris.min.js') }}"></script>
    <script src="{{ asset('vendor/admin/js/builder.js') }}"></script>
@endpush

@include('admin.partials.ckeditor')

@push('scripts')
    <script>
        "use strict";
        window.HomeBuilder = {
           layoutData: @json($homeLayout ?? []),
           routes: {
              sectionSettings: "{{ route('admin.builders.home.section-settings') }}",
              uploadImage: "{{ route('admin.builders.home.upload-image') }}",
              updateLayout: "{{ route('admin.builders.home.update-layout') }}",
              editBlock: "{{ url('admin/builders/home/edit-block') }}/BLOCK_ID"
           },
           csrfToken: "{{ csrf_token() }}",
           allowDuplicates: true,
           translations: config.translates
        };

        $(function() {
            new BuilderManager({
                type: 'home',
                ...window.HomeBuilder
            });
        });
    </script>
@endpush
