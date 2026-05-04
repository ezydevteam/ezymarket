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
                <div class="row mt-4">
                    <div class="col">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
    @include('admin.includes.scripts')
</body>
</html>
