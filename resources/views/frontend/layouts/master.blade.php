<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @hasSection ('page_meta')
        @yield('page_meta')
    @else
        @isset($page)
            <title>{{ $page->getTranslation('seo_title', app()->getLocale()) ?: $page->getTranslation('title', app()->getLocale()) }}</title>
            <meta name="description" content="{{ $page->getTranslation('meta_description', app()->getLocale()) }}">
            <meta name="keywords" content="{{ $page->getTranslation('keywords', app()->getLocale()) }}">
            <meta name="robots" content="{{ $page->index_status }},{{ $page->follow_status }}">
            <link rel="canonical" href="{{ $page->canonical_url ?: url()->current() }}"/>
            <meta property="og:title"
                  content="{{ $page->getTranslation('og_title', app()->getLocale()) ?: $page->getTranslation('seo_title', app()->getLocale()) }}"/>
            <meta property="og:description"
                  content="{{ $page->getTranslation('og_description', app()->getLocale()) ?: $page->getTranslation('meta_description', app()->getLocale()) }}"/>
            @if($page->og_image)
                <meta property="og:image" content="{{ asset($page->og_image) }}"/>
            @endif
        @else
            <title>{{ $settings['site_title']->value[app()->getLocale()] ?? config('app.name') }}</title>
            <meta name="description" content="{{ $settings['site_description']->value[app()->getLocale()] ?? '' }}">
        @endif
        <meta property="og:type" content="website"/>
        <meta property="og:url" content="{{ url()->current() }}"/>
    @endif

    @if(!empty($settings['google_site_verification']->value))
        <meta name="google-site-verification" content="{{ $settings['google_site_verification']->value }}"/>
    @endif
    @if(!empty($settings['bing_site_verification']->value))
        <meta name="msvalidate.01" content="{{ $settings['bing_site_verification']->value }}"/>
    @endif
    @if(!empty($settings['yandex_site_verification']->value))
        <meta name="yandex-verification" content="{{ $settings['yandex_site_verification']->value }}"/>
    @endif
    <link rel="icon"
          href="{{ isset($settings['site_favicon']) ? asset($settings['site_favicon']->value) : '/favicon.ico' }}">
    <link rel="stylesheet" href="{{ asset('site/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('site/css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('site/css/google.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('site/css/fontawesome-all.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('site/css/line-awesome.css') }}"/>
    <script src="https://cdn.jsdelivr.net/npm/icofont@1.0.0/main.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @stack('styles')
</head>
<body>
@include('frontend.layouts._header')
@if (!Route::is('frontend.home', 'frontend.home.text', 'frontend.services.show', 'blog.index', 'blog.show', 'blog.category', 'frontend.search'))
    <x-page-banner :title="$pageTitle ?? 'Next Medya'" :subtitle="$pageSubtitle ?? ''"/>
@endif
@yield('content')
@include('frontend.layouts._footer')
@auth('admin')
    @include('admin.bar')
@endauth
<script src="
https://cdn.jsdelivr.net/npm/icofont@1.0.0/main.min.js
"></script>
<script src="{{ asset('site/js/script.js') }}"></script>
<script src="{{ asset('site/js/footer.js') }}"></script>
<script src="{{ asset('site/js/google.js') }}"></script>
@stack('scripts')
</body>
</html>
