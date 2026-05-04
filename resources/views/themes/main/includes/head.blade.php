@php
$currentUser = authUser();
$title = metaTitle($__env);
$description = $__env->yieldContent('description') ? $__env->yieldContent('description') : @$settings->seo->description
?? '';
$keywords = $__env->yieldContent('keywords') ? $__env->yieldContent('keywords') : @$settings->seo->keywords ?? '';
$ogImage = $__env->yieldContent('og_image') ? $__env->yieldContent('og_image'):
asset($themeSettings->general->social_image);
@endphp
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ $currentUser?->id }}">
<meta name="user-username" content="{{ $currentUser?->username }}">
<meta name="ably-key" content="{{ env('ABLY_KEY') }}">
<meta name="theme-color" content="{{ $themeSettings->colors->primary_color }}">

<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta property="og:site_name" content="{{ getSiteName() }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image:height" content="600">
<meta property="og:image:width" content="316">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image:src" content="{{ $ogImage }}">
<meta name="google-site-verification" content="5FeevMox2ODFtWmtjG4TTQgcbSMIWzLiQbGzKFrRnog" />
<meta name="msvalidate.01" content="DB38C27C5C3F44003BB3AF8CDC457793" />
@if (View::hasSection('noindex'))
<meta name="robots" content="noindex, nofollow">
@endif
<title>{{ $title }}</title>
<link rel="canonical" href="{{ url()->current() }}" />
<link rel="icon" href="{{ asset($themeSettings->general->favicon) }}">
@yield('breadcrumbs_schema')
{!! schema($__env) !!}
@stack('schema')

<script>
    window.Laravel = @json($laravelJs);
    (function () {
        try {
            var theme = localStorage.getItem('theme');
            var dark = theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        } catch (e) { }
    })();
</script>
