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
    <div class="container @yield('container')">
      <main class="ezydev-main-content px-2">
        <div class="py-4 g-3">
          <div class="row g-3 align-items-center">
            @hasSection('title')
            <div class="col-12 col-lg">
              <h1 class="h3 mb-1">@yield('title')</h1>
              @hasSection('description')
              <p class="text-gray mb-0">@yield('description')</p>
              @endif
            </div>
            @endif
            @hasSection('back')
            <div class="col-auto">
              <a href="@yield('back')" class="btn btn-light"><i class="fas fa-arrow-left fa-rtl me-1"></i>{{
                translate("Back") }}</a>
            </div>
            @endif
            @hasSection('create')
            <div class="col-auto">
              <a href="@yield('create')" class="btn btn-dark"><i class="fa fa-plus me-1"></i>{{
                translate($__env->yieldContent('create_label', 'Create')) }}
              </a>
            </div>
            @endif
            <div class="col-auto">
              <button form="ezydev-form" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>
                {{ translate($__env->yieldContent('save_label', 'Save')) }}
              </button>
            </div>
          </div>
        </div>
        <div class="codebay-form-page">@yield('content')</div>
      </main>
    </div>
  </div>
  @include('admin.includes.scripts')
</body>

</html>
