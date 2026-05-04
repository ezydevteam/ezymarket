@extends('admin.layouts.full')
@section('section', translate('Appearance'))
@section('title', translate('Menus'))
@section('content')
    <div class="row g-3">
        {{-- Menu Builder Sidebar --}}
        <div class="col-lg-4 col-xl-3 order-2 order-lg-1">
            @include('admin.appearance.menus.partials.menu-builder')
        </div>
        {{-- Menu List --}}
        <div class="col-lg-8 col-xl-9 order-1 order-lg-2">
            {{-- Menu Items Container --}}
            <div id="menuItemsContainer">
                @include('admin.appearance.menus.partials.view')
            </div>
        </div>
    </div>

    {{-- Import Menu Modal --}}
    @include('admin.appearance.menus.partials.import-modal')
@endsection

@push('top_scripts')
    <script>
        const sortingRoute = "{{ route('admin.appearance.menus.nestable') }}";
        const nestableMaxDepth = {{ \App\Models\Appearance\Menu::MAX_DEPTH }};
    </script>
@endpush

@push('styles_libs')
    <link rel="stylesheet" href="{{ asset('vendor/libs/jquery/nestable/jquery.nestable.min.css') }}">
@endpush

@push('scripts_libs')
    <script src="{{ asset('vendor/libs/jquery/nestable/jquery.nestable.min.js') }}"></script>
    <script src="{{ asset_with_version('vendor/libs/ezydev/js/menu-manager.js') }}"></script>
@endpush

@push('scripts')
<script>
    $(function() {
        MenuManager.init({
            bulkAddRoute: "{{ route('admin.appearance.menus.bulk-add') }}",
            currentLocation: "{{ $location }}",
            csrfToken: "{{ csrf_token() }}",
            translations: {
                selectAtLeastOne: "{{ translate('Please select at least one item') }}",
                adding: "{{ translate('Adding...') }}",
                somethingWrong: "{{ translate('Something went wrong') }}",
                enterLinkText: "{{ translate('Please enter link text') }}",
                updating: "{{ translate('Updating...') }}",
                deleting: "{{ translate('Deleting...') }}",
                importing: "{{ translate('Importing...') }}",
                failedToLoadMenus: "{{ translate('Failed to load menus') }}",
                bulkDeleteConfirm: "{{ translate('Are you sure you want to delete the selected menus? If they have child menus, those will also be deleted.') }}"
            }
        });
    });
</script>
@endpush
