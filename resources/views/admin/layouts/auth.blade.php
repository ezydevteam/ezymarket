<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">
  <head>
    @include('admin.includes.head')
    @bootstrap
    <link
      rel="stylesheet"
      href="{{ asset('vendor/libs/fontawesome/fontawesome.min.css') }}"
    />
    @adminColors
    <link
      rel="stylesheet"
      href="{{ asset('vendor/libs/codebay/toastr/css/toastr.min.css') }}"
    />
    <link
      rel="stylesheet"
      href="{{ asset_with_version('vendor/admin/css/app.css') }}"
    />
    @if (getDirection() == 'rtl')
    <link
      rel="stylesheet"
      href="{{ asset_with_version('vendor/admin/css/app.rtl.css') }}"
    />
    @endif
    @adminCustomStyle
  </head>

  <body>
    <div class="codebay-auth-container">
      <div class="codebay-auth-form">
        <a href="{{ route('admin.index') }}" class="codebay-auth-logo">
          <img
            src="{{ asset(themeSettings()->general->logo_dark ?? 'themes/main/images/logo-dark.png') }}"
            alt="{{ @$settings->general->site_name }}"
          />
        </a>
        <div class="card">@yield('content')</div>
      </div>
    </div>
    <script src="{{ asset('vendor/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/libs/codebay/toastr/js/toastr.min.js') }}"></script>
    <script src="{{ asset_with_version('vendor/admin/js/app.js') }}"></script>

    @toastrRender

    @if ($errors->any())
    <script>
      @foreach ($errors->all() as $error)
          toastr.error('{{ $error }}')
      @endforeach
    </script>
    @elseif(session('status'))
    <script>
      toastr.success('{{ session('status') }}')
    </script>
    @endif
  </body>
</html>
















