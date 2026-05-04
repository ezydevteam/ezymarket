@bootstrap
<link rel="stylesheet" href="{{ asset('vendor/libs/fontawesome/fontawesome.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/simplebar/simplebar.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/datatable/css/datatables.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/datatable/css/buttons.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/codebay/counter-cards.min.css') }}" />
@stack('styles_libs')
<link rel="stylesheet" href="{{ asset('vendor/libs/toggle-master/bootstrap-toggle.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/libs/codebay/toastr/css/toastr.min.css') }}" />
@adminColors
<link rel="stylesheet" href="{{ asset('vendor/admin/css/fonts.css') }}" />
<link rel="stylesheet" href="{{ asset_with_version('vendor/admin/css/app.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/admin/css/sidebar-collapsed.css') }}" />
@if (getDirection() == 'rtl')
<link rel="stylesheet" href="{{ asset_with_version('vendor/admin/css/app.rtl.css') }}" />
@endif
@stack('styles')
@adminCustomStyle
