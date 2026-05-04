@extends('admin.layouts.full')
@section('section', translate('Site Builders'))
@section('title', translate('Footer Builder'))
@section('container', 'container-max-xxl')
@section('content')
    <div id="footerBuilder" class="row g-3">
        {{-- Sidebar: Content Blocks --}}
        <div class="col-lg-3 order-2 order-lg-1">
            <div class="card sticky-top" style="top: 60px;">
                <div class="card-header py-2">
                    <h5 class="mb-0">
                        <i class="bi bi-puzzle me-2 text-primary"></i>
                        {{ translate('Content Blocks') }}
                    </h5>
                </div>
                <div class="card-body sidebar-scrollable p-2">
                    <div class="row g-2" id="footerContentBlocks">
                        @foreach($footerBlocks as $block)
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

        {{-- Main: Layout Canvas --}}
        <div class="col-lg-9 order-1 order-lg-2">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between border-0 rounded-3 py-2">
                    <h5 class="mb-0">
                        <i class="bi bi-layout-split me-2 text-primary"></i>
                        {{ translate('Footer Layout') }}
                    </h5>
                    <button type="button" class="btn btn-sm btn-primary" id="save-builder-btn">
                        <i class="bi bi-save me-2"></i>{{ translate('Save') }}
                    </button>
                </div>
            </div>

            {{-- Builder Canvas --}}
            <div id="footerBuilderCanvas"></div>
        </div>
    </div>

    {{-- Edit Block Offcanvas --}}
    <x-offcanvas
        id="editBlockOffcanvas"
        :title="translate('Edit Element')"
        icon="bi-pencil-square"
        placement="end"
        :focus="false">

        <x-loader id="offcanvasLoader" centered />
        <div id="offcanvasContent" class="d-none"></div>

        <x-slot:footer>
            <button type="submit" form="editBlockForm" class="btn btn-primary w-100" id="editBlockBtn">
                <i class="bi bi-check2-circle me-2"></i>{{ translate('Save Changes') }}
            </button>
        </x-slot:footer>
    </x-offcanvas>

    {{-- Section Settings Offcanvas --}}
    <x-offcanvas
        id="sectionSettingsOffcanvas"
        :title="translate('Section Settings')"
        icon="bi-sliders2-vertical"
        placement="end"
        :focus="false">

        <input type="hidden" id="currentSectionId" value="">
        <x-loader id="sectionSettingsLoader" centered />
        <div id="sectionSettingsContent" class="d-none"></div>

        <x-slot:footer>
            <button type="button" class="btn btn-primary w-100" id="saveSectionSettingsBtn">
                <i class="bi bi-check2-circle me-2"></i>{{ translate('Apply Settings') }}
            </button>
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

@push('scripts')
<script>
    "use strict";
    window.footerBuilder = {
        canvasId: '#footerBuilderCanvas',
        availableBlocksId: '#footerContentBlocks',
        layoutData: {!! json_encode($footerLayout ?? []) !!},
        csrfToken: '{{ csrf_token() }}',
        allowDuplicates: true,
        enableSectionSettings: true,
        fixedSections: true,
        sections: [
            {
                id: 'footer_widget_section',
                title: '{{ translate("Widget Section") }}',
                icon: 'bi-grid-3x3-gap',
                canDisable: true,
                defaultColumns: [
                    { width: 3 },
                    { width: 3 },
                    { width: 3 },
                    { width: 3 }
                ]
            },
            {
                id: 'footer_menu_section',
                title: '{{ translate("Menu Section") }}',
                icon: 'bi-list-ul',
                canDisable: true,
                defaultColumns: [
                    { width: 12 }
                ]
            },
            {
                id: 'footer_bottom_section',
                title: '{{ translate("Bottom Section") }}',
                icon: 'bi-c-circle',
                canDisable: true,
                defaultColumns: [
                    { width: 4 },
                    { width: 8 }
                ]
            }
        ],
        routes: {
            updateLayout: '{{ route("admin.builders.footer.update-layout") }}',
            uploadImage: '{{ route("admin.builders.footer.upload-image") }}',
            editBlock: '{{ route("admin.builders.footer.edit-block", "BLOCK_ID") }}',
            sectionSettings: '{{ route("admin.builders.footer.section-settings") }}'
        },
        translations: config.translates
    };

    $(function() {
        new BuilderManager({
            type: 'footer',
            ...window.footerBuilder
        });
    });
</script>
@endpush
