@props(['seo'])

@php
    // Убеждаемся, что есть базовые поля
    $title = $seo['title'] ?? config('app.name', 'Дневник сновидений');
    $description = $seo['description'] ?? config('seo.site_description', '');
    $ogTitle = $seo['og_title'] ?? $title;
    $ogDescription = $seo['og_description'] ?? $description;
    $ogImage = $seo['og_image'] ?? '';
    $keywords = $seo['keywords'] ?? '';
    $canonical = $seo['canonical'] ?? url()->current();
@endphp

{{-- Basic Meta Tags --}}
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
@if(!empty($keywords))
<meta name="keywords" content="{{ $keywords }}">
@endif

{{-- Favicon --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="alternate icon" href="{{ asset('favicon.ico') }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
@if(!empty($ogImage))
<meta property="og:image" content="{{ $ogImage }}">
@endif
<meta property="og:site_name" content="{{ config('seo.site_name', 'Дневник сновидений') }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $canonical }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if(!empty($ogImage))
<meta name="twitter:image" content="{{ $ogImage }}">
@endif























