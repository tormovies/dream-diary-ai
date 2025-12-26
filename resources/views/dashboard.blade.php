<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: 'light' }"
      x-bind:class="{ 'dark': theme === 'dark' }"
      x-init="
        const savedTheme = localStorage.getItem('theme') || 'light';
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
        
        @if(isset($seo))
            <x-seo-head :seo="$seo" />
        @else
            <title>Мои отчёты - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            .gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .card-shadow {
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            }
            .dark .card-shadow {
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            }
        </style>
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <main class="space-y-6 w-full" 
                  x-data="{ viewMode: localStorage.getItem('reportsViewMode') || 'grid' }"
                  x-init="
                    // Функция проверки и переключения на плитку для мобильных
                    const checkMobileView = () => {
                        if (window.innerWidth < 768 && viewMode === 'table') {
                            viewMode = 'grid';
                        }
                    };
                    
                    // Проверяем при загрузке
                    checkMobileView();
                    
                    // Следим за изменениями viewMode и сохраняем только если не мобильные
                    $watch('viewMode', value => {
                        if (window.innerWidth >= 768 || value === 'grid') {
                            localStorage.setItem('reportsViewMode', value);
                        }
                    });
                    
                    // Следим за изменением размера окна
                    window.addEventListener('resize', checkMobileView);
                  ">
                    <!-- Заголовок и кнопка создания -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center flex-wrap gap-4">
                            <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Мои отчёты</h2>
                            
                            <div class="flex items-center gap-3">
                                <!-- Переключатель вида (скрыт на мобильных) -->
                                <div class="hidden md:flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                                    <button @click="viewMode = 'grid'" 
                                            :class="viewMode === 'grid' ? 'bg-white dark:bg-gray-600 shadow-sm' : ''"
                                            class="px-3 py-2 rounded-md transition-all"
                                            title="Плитка">
                                        <i class="fas fa-th-large" :class="viewMode === 'grid' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500 dark:text-gray-400'"></i>
                                    </button>
                                    <button @click="viewMode = 'table'" 
                                            :class="viewMode === 'table' ? 'bg-white dark:bg-gray-600 shadow-sm' : ''"
                                            class="px-3 py-2 rounded-md transition-all"
                                            title="Таблица">
                                        <i class="fas fa-list" :class="viewMode === 'table' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-500 dark:text-gray-400'"></i>
                                    </button>
                                </div>
                                
                                <a href="{{ route('reports.create') }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-all whitespace-nowrap">
                                    <i class="fas fa-plus mr-2"></i>Создать отчет
                                </a>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Форма поиска и фильтров -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700" 
                         x-data="{ open: false }">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                             @click="open = !open">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    <i class="fas fa-search mr-2"></i>Поиск и фильтры
                                </h3>
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform" 
                                     :class="{ 'rotate-180': open }"
                                     fill="none" 
                                     stroke="currentColor" 
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="p-6">
                            <form method="GET" action="{{ route('dashboard') }}" class="space-y-4">
                                <!-- Поиск по тексту -->
                                <div>
                                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поиск по тексту</label>
                                    <input type="text" 
                                           id="search" 
                                           name="search" 
                                           value="{{ request('search') }}"
                                           placeholder="Поиск по названию или описанию снов..."
                                           class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <!-- Фильтр по тегам -->
                                    <div>
                                        <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Теги</label>
                                        <select id="tags" 
                                                name="tags[]" 
                                                multiple
                                                class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                size="5">
                                            @foreach($allTags as $tag)
                                                <option value="{{ $tag->id }}" 
                                                        {{ in_array($tag->id, (array)request('tags', [])) ? 'selected' : '' }}>
                                                    {{ $tag->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Удерживайте Ctrl для выбора нескольких</p>
                                    </div>

                                    <!-- Фильтр по типу сна -->
                                    <div>
                                        <label for="dream_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип сна</label>
                                        <select id="dream_type" 
                                                name="dream_type" 
                                                class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">Все типы</option>
                                            @foreach($dreamTypes as $type)
                                                <option value="{{ $type }}" {{ request('dream_type') === $type ? 'selected' : '' }}>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Фильтр по дате (от) -->
                                    <div>
                                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата от</label>
                                        <input type="date" 
                                               id="date_from" 
                                               name="date_from" 
                                               value="{{ request('date_from') }}"
                                               class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>

                                    <!-- Фильтр по дате (до) -->
                                    <div>
                                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата до</label>
                                        <input type="date" 
                                               id="date_to" 
                                               name="date_to" 
                                               value="{{ request('date_to') }}"
                                               class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Фильтр по статусу -->
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Статус</label>
                                        <select id="status" 
                                                name="status" 
                                                class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">Все</option>
                                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Опубликованные</option>
                                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Черновики</option>
                                        </select>
                                    </div>

                                    <!-- Сортировка -->
                                    <div>
                                        <label for="sort_by" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Сортировать по</label>
                                        <select id="sort_by" 
                                                name="sort_by" 
                                                class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="report_date" {{ request('sort_by', 'report_date') === 'report_date' ? 'selected' : '' }}>Дате отчета</option>
                                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Дате создания</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Порядок</label>
                                        <select id="sort_order" 
                                                name="sort_order" 
                                                class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>По убыванию</option>
                                            <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">На странице</label>
                                        <select id="per_page" 
                                                name="per_page" 
                                                class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
                                        Применить фильтры
                                    </button>
                                    <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                        Сбросить
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($reports->count() > 0)
                        <!-- Вид плиткой (всегда на мобильных, переключаемый на десктопе) -->
                        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($reports as $report)
                                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm card-shadow border border-gray-200 dark:border-gray-700 relative">
                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ $report->report_date->format('d.m.Y') }}
                                                </h3>
                                                <span class="text-xs px-2 py-1 rounded mt-1 inline-block
                                                    @if($report->status === 'published') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300
                                                    @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                                    @endif">
                                                    @if($report->status === 'published') Опубликован
                                                    @else Черновик
                                                    @endif
                                                </span>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded 
                                                @if($report->access_level === 'all') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300
                                                @elseif($report->access_level === 'friends') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300
                                                @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                                @endif">
                                                @if($report->access_level === 'all') Всем
                                                @elseif($report->access_level === 'friends') Друзьям
                                                @else Никому
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            Снов: {{ $report->dreams->count() }}
                                        </p>
                                        
                                        <!-- Названия снов -->
                                        @if($report->dreams->count() > 0)
                                            <div class="mb-4 space-y-2">
                                                @php
                                                    $dreamsWithTitles = $report->dreams->filter(function($dream) {
                                                        return !empty($dream->title);
                                                    })->take(4);
                                                @endphp
                                                @foreach($dreamsWithTitles as $index => $dream)
                                                    <div class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded border-l-2 border-blue-400 gap-2">
                                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400 min-w-[28px] flex-shrink-0 text-center">#{{ $index + 1 }}</span>
                                                        <span class="text-sm text-gray-900 dark:text-white flex-1">{{ $dream->title }}</span>
                                                    </div>
                                                @endforeach
                                                @if($report->dreams->count() > $dreamsWithTitles->count())
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 italic pl-2">
                                                        ... и еще {{ $report->dreams->count() - $dreamsWithTitles->count() }} {{ ($report->dreams->count() - $dreamsWithTitles->count()) == 1 ? 'сон' : 'снов' }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        @if($report->tags->count() > 0)
                                            <div class="flex flex-wrap gap-1 mb-4">
                                                @foreach($report->tags as $tag)
                                                    <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-2 py-1 rounded">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="space-y-2">
                                            <!-- Кнопки публикации/снятия с публикации -->
                                            <div class="flex gap-2">
                                                @if($report->status === 'draft')
                                                    <form action="{{ route('reports.publish', $report) }}" 
                                                          method="POST" 
                                                          class="inline flex-1">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="w-full bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition-colors">
                                                            <i class="fas fa-eye mr-1"></i>Опубликовать
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('reports.unpublish', $report) }}" 
                                                          method="POST" 
                                                          class="inline flex-1">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="w-full bg-gray-500 hover:bg-gray-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition-colors">
                                                            <i class="fas fa-eye-slash mr-1"></i>Снять с публикации
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            
                                            <!-- Остальные действия -->
                                            <div class="flex gap-2 items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                                <div class="flex gap-2">
                                                    <a href="{{ route('reports.show', $report) }}" 
                                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
                                                        Просмотр
                                                    </a>
                                                    <a href="{{ route('reports.edit', $report) }}" 
                                                       class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-sm">
                                                        Редактировать
                                                    </a>
                                                </div>
                                                
                                                <!-- Кнопка удаления -->
                                                <form action="{{ route('reports.destroy', $report) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот отчет?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Вид таблицей (только на десктопе) -->
                        <div x-show="viewMode === 'table'" class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                            <style>
                                @media (max-width: 767px) {
                                    [x-show="viewMode === 'table'"] {
                                        display: none !important;
                                    }
                                }
                            </style>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                <i class="fas fa-calendar mr-1"></i>Дата
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                <i class="fas fa-moon mr-1"></i>Сны
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                <i class="fas fa-info-circle mr-1"></i>Статус
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                                                <i class="fas fa-lock mr-1"></i>Доступ
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                                                <i class="fas fa-comment mr-1"></i>Комментарии
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Действия
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($reports as $report)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <!-- Дата -->
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-calendar-day text-purple-500 mr-2"></i>
                                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $report->report_date->format('d.m.Y') }}
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <!-- Сны -->
                                                <td class="px-4 py-3">
                                                    @if($report->dreams->count() > 0)
                                                        @php
                                                            // Собираем все названия снов
                                                            $dreamTitles = $report->dreams
                                                                ->filter(fn($dream) => !empty($dream->title))
                                                                ->pluck('title')
                                                                ->take(3)
                                                                ->join(', ');
                                                            
                                                            if(empty($dreamTitles)) {
                                                                $dreamTitles = 'Без названия';
                                                            }
                                                            
                                                            $commentsCount = $report->comments->count();
                                                        @endphp
                                                        <div class="flex items-center gap-2">
                                                            <a href="{{ route('reports.show', $report) }}" 
                                                               class="text-sm text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 hover:underline truncate flex-1">
                                                                {{ $dreamTitles }}
                                                                @if($report->dreams->filter(fn($d) => !empty($d->title))->count() > 3)
                                                                    <span class="text-gray-400 dark:text-gray-500">...</span>
                                                                @endif
                                                            </a>
                                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 whitespace-nowrap">
                                                                    <i class="fas fa-moon mr-1"></i>{{ $report->dreams->count() }}
                                                                </span>
                                                                @if($commentsCount > 0)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300 whitespace-nowrap">
                                                                        <i class="fas fa-comment mr-1"></i>{{ $commentsCount }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                                
                                                <!-- Статус (кнопка публикации) -->
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @if($report->status === 'draft')
                                                        <form action="{{ route('reports.publish', $report) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                                                                <i class="fas fa-eye mr-1"></i>Опубликовать
                                                            </button>
                                                        </form>
                                                    @else
                                                        <div class="flex items-center gap-2">
                                                            <span class="inline-flex items-center text-green-600 dark:text-green-400">
                                                                <i class="fas fa-check-circle text-lg"></i>
                                                            </span>
                                                            <form action="{{ route('reports.unpublish', $report) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" 
                                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                                    Снять с публикации
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </td>
                                                
                                                <!-- Доступ -->
                                                <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                        @if($report->access_level === 'all') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300
                                                        @elseif($report->access_level === 'friends') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300
                                                        @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                                        @endif">
                                                        <i class="fas @if($report->access_level === 'all') fa-globe @elseif($report->access_level === 'friends') fa-user-friends @else fa-lock @endif mr-1"></i>
                                                        @if($report->access_level === 'all') Всем
                                                        @elseif($report->access_level === 'friends') Друзьям
                                                        @else Никому
                                                        @endif
                                                    </span>
                                                </td>
                                                
                                                <!-- Комментарии -->
                                                <td class="px-4 py-3 whitespace-nowrap hidden sm:table-cell">
                                                    @php
                                                        $commentsCount = $report->comments->count();
                                                    @endphp
                                                    @if($commentsCount > 0)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300">
                                                            <i class="fas fa-comment mr-1"></i>{{ $commentsCount }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </td>
                                                
                                                <!-- Действия -->
                                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('reports.show', $report) }}" 
                                                           class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                                           title="Просмотр">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('reports.edit', $report) }}" 
                                                           class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300"
                                                           title="Редактировать">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('reports.destroy', $report) }}" 
                                                              method="POST" 
                                                              class="inline"
                                                              onsubmit="return confirm('Вы уверены, что хотите удалить этот отчет?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                                                                    title="Удалить">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6">
                            {{ $reports->links() }}
                        </div>
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm card-shadow border border-gray-200 dark:border-gray-700">
                            <div class="p-6 text-gray-900 dark:text-white text-center">
                                <p class="mb-4">У вас пока нет отчетов.</p>
                                <a href="{{ route('reports.create') }}" 
                                   class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-all inline-block">
                                    Создать первый отчет
                                </a>
                            </div>
                        </div>
                    @endif
                </main>
        </div>

        <script>
            function toggleTheme() {
                const html = document.documentElement;
                const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                if (newTheme === 'dark') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
                
                localStorage.setItem('theme', newTheme);
            }
        </script>
    </body>
</html>
