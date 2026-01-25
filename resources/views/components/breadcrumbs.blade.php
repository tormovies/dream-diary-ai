@props(['items' => []])

@if(count($items) > 1)
    @php
        $baseUrl = rtrim(config('seo.base_url', config('app.url')), '/');
        $breadcrumbList = [];
        foreach ($items as $index => $item) {
            $breadcrumbList[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url']
            ];
        }
    @endphp

    {{-- Визуальные breadcrumbs --}}
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex flex-wrap items-center gap-2 text-sm">
            @foreach($items as $index => $item)
                <li class="flex items-center">
                    @if($loop->last)
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ $item['name'] }}
                        </span>
                    @else
                        <a href="{{ $item['url'] }}" 
                           class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 hover:underline transition-colors">
                            {{ $item['name'] }}
                        </a>
                        <span class="mx-2 text-gray-400 dark:text-gray-500">/</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- JSON-LD разметка для поисковиков --}}
    @php
        $jsonData = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbList
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @endphp
    <script type="application/ld+json">
    {!! $jsonData !!}
    </script>
@endif
