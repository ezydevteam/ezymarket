<livewire:newsletter-popup />
<x-extensions />
@stack('top_scripts')
<script src="{{ asset('vendor/libs/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/libs/codebay/utils/loaderUtils.js') }}"></script>
<script src="{{ asset('vendor/libs/codebay/utils/dateUtils.js') }}"></script>
<script src="{{ asset('vendor/libs/codebay/toastr/js/toastr.min.js') }}"></script>
<script src="{{ asset('vendor/libs/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('vendor/libs/ably/ably.min.js') }}"></script>
<script src="{{ asset('vendor/libs/wavesurfer/wavesurfer.min.js') }}"></script>
<script src="{{ asset('vendor/libs/plyr/plyr.min.js') }}"></script>
<script src="{{ asset('vendor/libs/ezydev/js/utility.js') }}"></script>
<livewire:scripts />
@stack('scripts_libs')
<script src="{{ theme_assets_with_version('assets/js/app.js') }}"></script>
<script src="{{ theme_assets_with_version('assets/js/broadcast.js') }}"></script>
@stack('scripts')
@toastrRender
{!! $themeSettings->extra_codes->footer_code !!}
