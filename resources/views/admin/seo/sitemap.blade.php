<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Управление Sitemap') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.seo.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Назад к SEO
                </a>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Назад в админку
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded mb-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                Статистика и настройки Sitemap
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Управляйте тем, какие типы контента включаются в sitemap
                            </p>
                        </div>
                        <a href="{{ url('/sitemap.xml') }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            <i class="fas fa-external-link-alt mr-1"></i>Открыть sitemap
                        </a>
                    </div>

                    <form method="POST" action="{{ route('admin.seo.sitemap.settings') }}">
                        @csrf

                        <!-- Настройка количества URL на странице -->
                        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                            <label for="urls_per_page" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                Количество URL на одной странице sitemap
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="number" 
                                       id="urls_per_page" 
                                       name="urls_per_page" 
                                       value="{{ $urlsPerPage }}" 
                                       min="100" 
                                       max="50000" 
                                       step="100"
                                       class="w-32 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    (минимум: 100, максимум: 50 000)
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1.5">
                                При превышении этого лимита контент автоматически разбивается на несколько файлов sitemap
                            </p>
                        </div>

                        <!-- Настройка количества ссылок при перелинковке -->
                        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                            <label for="linking_links_count" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                Количество ссылок при перелинковке
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="number" 
                                       id="linking_links_count" 
                                       name="linking_links_count" 
                                       value="{{ $linkingLinksCount }}" 
                                       min="1" 
                                       max="20" 
                                       step="1"
                                       class="w-24 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    (минимум: 1, максимум: 20)
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1.5">
                                Количество похожих/последних толкований, отображаемых в блоках перелинковки
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach([
                                'static' => ['name' => 'Статические страницы', 'description' => 'Главная, толкование снов, инструкции, статьи, активность'],
                                'guides' => ['name' => 'Инструкции', 'description' => 'Опубликованные инструкции'],
                                'articles' => ['name' => 'Статьи', 'description' => 'Опубликованные статьи'],
                                'interpretations' => ['name' => 'Толкования сновидений', 'description' => 'Готовые толкования (после 16.01.2026)'],
                                'reports' => ['name' => 'Публичные отчеты', 'description' => 'Опубликованные отчеты'],
                                'report_analyses' => ['name' => 'Анализы отчетов', 'description' => 'Анализы публичных отчетов'],
                            ] as $key => $info)
                                @php
                                    $stat = $stats[$key];
                                    $enabledKey = $key . '_enabled';
                                @endphp
                                <div class="border border-gray-200 dark:border-gray-700 rounded p-3">
                                    <div class="flex items-start gap-2 mb-2">
                                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-0.5">
                                            <input type="checkbox" 
                                                   name="{{ $enabledKey }}" 
                                                   value="1"
                                                   {{ $stat['enabled'] ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                                        </label>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $info['name'] }}
                                            </h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $info['description'] }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded p-2">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">
                                                @if(in_array($key, ['interpretations', 'report_analyses']))
                                                    Готовых*
                                                @else
                                                    Всего
                                                @endif
                                            </div>
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                                {{ number_format($stat['total'], 0, ',', ' ') }}
                                            </div>
                                        </div>
                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded p-2">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Сегодня</div>
                                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                {{ number_format($stat['today'], 0, ',', ' ') }}
                                            </div>
                                        </div>
                                    </div>
                                    @if(in_array($key, ['interpretations', 'report_analyses']))
                                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">
                                            *только с валидными SEO попадут в sitemap
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-1.5 px-4 rounded text-sm">
                                Сохранить настройки
                            </button>
                        </div>
                    </form>
                    
                    <!-- Форма очистки кеша (отдельно от формы настроек) -->
                    <div class="mt-4 flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <form method="POST" action="{{ route('admin.seo.sitemap.clear-cache') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Вы уверены, что хотите очистить кеш sitemap? Это заставит систему перегенерировать все sitemap файлы при следующем запросе.')"
                                        class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-1.5 px-4 rounded text-sm">
                                    <i class="fas fa-sync-alt mr-2"></i>Очистить кеш sitemap
                                </button>
                            </form>
                            @if($lastCacheUpdate && $lastCacheUpdate instanceof \Carbon\Carbon)
                                <div class="text-xs {{ $cacheExists ? 'text-gray-600 dark:text-gray-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    <i class="fas fa-clock mr-1"></i>
                                    @if($cacheExists)
                                        Последнее обновление: {{ $lastCacheUpdate->format('d.m.Y H:i') }}
                                    @else
                                        Кеш очищен: {{ $lastCacheUpdate->format('d.m.Y H:i') }}<br>
                                        <span class="text-xs">(будет пересоздан автоматически при запросе любого sitemap файла: /sitemap.xml, /sitemap-static.xml и т.д. - обычно это делают поисковые роботы)</span>
                                    @endif
                                </div>
                            @else
                                <div class="text-xs text-gray-500 dark:text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Кеш еще не был сгенерирован
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Информация -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-3">
                <div class="text-xs text-blue-800 dark:text-blue-300 space-y-0.5">
                    <div>• Изменения применяются сразу после сохранения</div>
                    <div>• Отключенные типы контента не будут включены в sitemap index</div>
                    <div>• Статистика обновляется в реальном времени</div>
                    <div>• Sitemap кешируется на 1 час для производительности</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
