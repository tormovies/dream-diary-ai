@props(['seo'])

@php
    // Убеждаемся, что есть базовые поля
    $title = $seo['title'] ?? config('app.name', 'Дневник сновидений');
    $description = $seo['description'] ?? config('seo.site_description', '');
    $ogTitle = $seo['og_title'] ?? $title;
    $ogDescription = $seo['og_description'] ?? $description;
    $ogImage = $seo['og_image'] ?? '';
    $ogImageWidth = $seo['og_image_width'] ?? null;
    $ogImageHeight = $seo['og_image_height'] ?? null;
    $ogType = $seo['og_type'] ?? 'website';
    $keywords = $seo['keywords'] ?? '';
    $canonical = $seo['canonical'] ?? url()->current();
    
    // Article мета-теги (если это статья)
    $articleAuthor = $seo['article_author'] ?? null;
    $articlePublishedTime = $seo['article_published_time'] ?? null;
    $articleModifiedTime = $seo['article_modified_time'] ?? null;
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
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:locale" content="ru_RU">
@if(!empty($ogImage))
<meta property="og:image" content="{{ $ogImage }}">
@if($ogImageWidth)
<meta property="og:image:width" content="{{ $ogImageWidth }}">
@endif
@if($ogImageHeight)
<meta property="og:image:height" content="{{ $ogImageHeight }}">
@endif
@endif
<meta property="og:site_name" content="{{ config('seo.site_name', 'Дневник сновидений') }}">

{{-- Article мета-теги (если это статья) --}}
@if($ogType === 'article')
@if($articleAuthor)
<meta property="article:author" content="{{ $articleAuthor }}">
@endif
@if($articlePublishedTime)
<meta property="article:published_time" content="{{ $articlePublishedTime }}">
@endif
@if($articleModifiedTime)
<meta property="article:modified_time" content="{{ $articleModifiedTime }}">
@endif
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $canonical }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if(!empty($ogImage))
<meta name="twitter:image" content="{{ $ogImage }}">
@endif





























