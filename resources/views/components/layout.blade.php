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
        
        <!-- Resource Hints для оптимизации загрузки -->
        <link rel="preconnect" href="https://top-fwz1.mail.ru" crossorigin>
        <link rel="dns-prefetch" href="https://top-fwz1.mail.ru">
        
        <!-- Preload критических ресурсов -->
        <x-preload-assets />
        
        @if(isset($seo))
            <x-seo-head :seo="$seo" />
        @else
            <title>{{ $title ?? config('app.name', 'Дневник сновидений') }}</title>
        @endif

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
        
        <x-fontawesome />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <x-header-styles />
        
        @stack('styles')
        
        @unless(request()->is('admin*'))
        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
            (function(m,e,t,r,i,k,a){
                m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();
                for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
                k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
            })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=89409547', 'ym');
            ym(89409547, 'init', {ssr:true, clickmap:true, accurateTrackBounce:true, trackLinks:true});
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/89409547" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
        @endunless
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="{{ $gridClass ?? 'main-grid' }} w-full">
                @if(isset($sidebar))
                <!-- Левая панель -->
                <aside class="space-y-6">
                    {{ $sidebar }}
                </aside>
                @endif
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0">
                    {{ $slot }}
                </main>
                
                @if(isset($rightSidebar))
                <!-- Правая панель -->
                <aside class="sidebar-menu space-y-6">
                    {{ $rightSidebar }}
                </aside>
                @endif
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
