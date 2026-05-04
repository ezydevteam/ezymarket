<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">
<head>
    @section('theme_head')
        @themeInclude('includes.head')
    @show

    @section('theme_styles')
        @themeInclude('includes.styles')
    @show

    @stack('head_content')

    @section('head_ad')
        <x-advertisement alias="head_code" />
    @show
</head>
<body class="@yield('body_class')">
    @section('theme_header')
        @themeInclude('includes.header')
    @show

    @yield('body_content')
    @themeInclude('auth.includes.registration-success-modal')

    @section('theme_footer')
        @themeInclude('includes.footer')
    @show

    @section('theme_config')
        @themeInclude('includes.config')
    @show

    @section('theme_scripts')
        @themeInclude('includes.scripts')
    @show

    @stack('footer_content')
</body>
</html>
