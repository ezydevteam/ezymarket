<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">
  <head>
    @include('admin.includes.head')
    @include('admin.includes.styles')
  </head>

  <body>
    @include('admin.includes.sidebar')
    <div class="ezydev-main-wrapper">
      @include('admin.includes.navbar')
      <div class="container container-max-lg py-5">
        <main class="ezydev-main-content py-5">
          <div class="card codebay-error-card my-5 p-5">
            <div class="py-4">
              <h1 class="codebay-error-code">@yield('code')</h1>
              <h2 class="codebay-error-title">@yield('title')</h2>
              <div class="col-lg-9 m-auto">
                <p class="codebay-error-message">@yield('message')</p>
              </div>
              <div>
                <a
                  href="{{ route('admin.dashboard') }}"
                  class="btn btn-primary btn-md"
                  ><i class="fa-solid fa-table-columns me-2"></i
                  >{{ translate("Go to dashboard") }}</a
                >
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
    @include('admin.includes.scripts')
  </body>
</html>


















