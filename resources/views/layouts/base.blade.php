<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: '{{ auth()->check() ? (auth()->user()->theme ?? 'light') : 'light' }}' }"
      x-bind:class="{ 'dark': theme === 'dark' }"
      x-init="
        const savedTheme = localStorage.getItem('theme') || '{{ auth()->check() ? (auth()->user()->theme ?? 'light') : 'light' }}';
        theme = savedTheme;
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
      ">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        {{-- Preload критических ресурсов --}}
        <x-preload-assets />
        
        {{-- SEO мета-теги --}}
        @if(isset($seo))
            <x-seo-head :seo="$seo" />
        @else
            <title>@yield('title', $title ?? config('app.name', 'Дневник сновидений'))</title>
            
            {{-- Favicon --}}
            <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
            <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
        @endif
        
        {{-- Структурированные данные (JSON-LD) - должны быть сразу после SEO тегов, до скриптов --}}
        @if(isset($structuredData) && !empty($structuredData))
            @foreach($structuredData as $data)
                <x-structured-data :data="$data" />
            @endforeach
        @endif
        
        {{-- Vite ресурсы (по умолчанию, можно добавить дополнительные через @push('vite')) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('vite')
        
        {{-- Дополнительные стили --}}
        <x-header-styles />
        @stack('styles')
        
        {{-- Яндекс.Метрика --}}
        <x-yandex-metrika :exclude-admin="true" />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        {{-- Header --}}
        <x-header />
        
        {{-- Контент страницы --}}
        @yield('content')
        
        {{-- Footer --}}
        <x-footer />
        
        {{-- Дополнительные скрипты --}}
        @stack('scripts')
    </body>
</html>
