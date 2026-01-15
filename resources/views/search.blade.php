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
        
        <!-- Resource Hints для оптимизации загрузки -->
        <link rel="preconnect" href="https://top-fwz1.mail.ru" crossorigin>
        <link rel="dns-prefetch" href="https://top-fwz1.mail.ru">
        
        <!-- Preload критических ресурсов -->
        <x-preload-assets />
        
        @if(isset($seo))
            <x-seo-head :seo="$seo" />
        @else
            <title>Поиск - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-header-styles />
        
        <x-yandex-metrika />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="main-grid w-full">
                <!-- Левая панель -->
                <aside class="space-y-6">
                @auth
                    <!-- Для авторизованных -->
                    <!-- Быстрое меню -->
                    <x-auth-sidebar-menu />
                @else
                    <!-- Для неавторизованных -->
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Присоединяйтесь к сообществу людей, которые записывают и анализируют свои сновидения.
                        </p>
                        <a href="{{ route('register') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                            <i class="fas fa-user-plus mr-2"></i>Регистрация
                        </a>
                    </div>
                    
                    <x-guest-quick-actions />
                @endauth
                </aside>
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0 overflow-hidden">
                    <!-- Заголовок и форма поиска -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-4 text-purple-600 dark:text-purple-400">Поиск сновидений</h2>
                        
                        <!-- Форма поиска и фильтров -->
                        <div x-data="{ open: {{ request()->hasAny(['search', 'tags', 'dream_type', 'date_from', 'date_to', 'sort_by', 'sort_order', 'per_page']) ? 'false' : 'true' }} }">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors rounded-t-lg"
                                 @click="open = !open">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        🔍 Фильтры поиска
                                    </h3>
                                    <svg class="w-5 h-5 text-gray-500 transition-transform" 
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
                                <form method="GET" action="{{ route('reports.search') }}" class="profile-form">
                                    <!-- Поиск по тексту -->
                                    <div class="form-group">
                                        <label for="search" class="form-label">
                                            <i class="fas fa-search"></i>
                                            Поиск по тексту
                                        </label>
                                        <input type="text" 
                                               id="search" 
                                               name="search" 
                                               value="{{ request('search') }}"
                                               placeholder="Поиск по названию, описанию снов или пользователям..."
                                               class="form-input">
                                    </div>

                                    <div class="form-row form-row-auto">
                                        <!-- Фильтр по тегам - СКРЫТ -->
                                        <!--
                                        <div class="form-group">
                                            <label for="tags" class="form-label">
                                                <i class="fas fa-tags"></i>
                                                Теги
                                            </label>
                                            <select id="tags" 
                                                    name="tags[]" 
                                                    multiple
                                                    class="form-select"
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
                                        -->

                                        <!-- Фильтр по типу сна -->
                                        <div class="form-group">
                                            <label for="dream_type" class="form-label">
                                                <i class="fas fa-moon"></i>
                                                Тип сна
                                            </label>
                                            <select id="dream_type" 
                                                    name="dream_type" 
                                                    class="form-select">
                                                <option value="">Все типы</option>
                                                @foreach($dreamTypes as $type)
                                                    <option value="{{ $type }}" {{ request('dream_type') === $type ? 'selected' : '' }}>
                                                        {{ $type }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Фильтр по дате (от) -->
                                        <div class="form-group">
                                            <label for="date_from" class="form-label">
                                                <i class="fas fa-calendar"></i>
                                                Дата от
                                            </label>
                                            <input type="date" 
                                                   id="date_from" 
                                                   name="date_from" 
                                                   value="{{ request('date_from') }}"
                                                   class="form-input">
                                        </div>

                                        <!-- Фильтр по дате (до) -->
                                        <div class="form-group">
                                            <label for="date_to" class="form-label">
                                                <i class="fas fa-calendar"></i>
                                                Дата до
                                            </label>
                                            <input type="date" 
                                                   id="date_to" 
                                                   name="date_to" 
                                                   value="{{ request('date_to') }}"
                                                   class="form-input">
                                        </div>
                                    </div>

                                    <div class="form-row form-row-auto-sm">
                                        <!-- Сортировка -->
                                        <div class="form-group">
                                            <label for="sort_by" class="form-label">
                                                <i class="fas fa-sort"></i>
                                                Сортировать по
                                            </label>
                                            <select id="sort_by" 
                                                    name="sort_by" 
                                                    class="form-select">
                                                <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>Дате создания</option>
                                                <option value="report_date" {{ request('sort_by') === 'report_date' ? 'selected' : '' }}>Дате отчета</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="sort_order" class="form-label">
                                                <i class="fas fa-arrow-down-wide-short"></i>
                                                Порядок
                                            </label>
                                            <select id="sort_order" 
                                                    name="sort_order" 
                                                    class="form-select">
                                                <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>По убыванию</option>
                                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="per_page" class="form-label">
                                                <i class="fas fa-list-ol"></i>
                                                На странице
                                            </label>
                                            <select id="per_page" 
                                                    name="per_page" 
                                                    class="form-select">
                                                <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                                                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-actions form-actions-start">
                                        <button type="submit" class="btn-form-primary">
                                            <i class="fas fa-search mr-2"></i>
                                            Применить фильтры
                                        </button>
                                        <a href="{{ route('reports.search') }}" class="btn-form-secondary">
                                            <i class="fas fa-redo mr-2"></i>
                                            Сбросить
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Результаты поиска -->
                    <div class="space-y-6">
                        @if($reports->count() > 0)
                            @foreach($reports as $report)
                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-4">
                                            <x-avatar :user="$report->user" size="md" />
                                            <div>
                                                <a href="{{ route('users.profile', $report->user) }}" class="font-semibold text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400">
                                                    {{ $report->user->nickname }}
                                                </a>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $report->report_date->format('d.m.Y') }} • 
                                                    {{ $report->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($report->dreams->count() > 0)
                                        @php 
                                            $firstDream = $report->dreams->first();
                                            $dreamsWithTitles = $report->dreams->filter(function($dream) {
                                                return !empty($dream->title);
                                            });
                                            $allTitles = $dreamsWithTitles->pluck('title')->implode(', ');
                                        @endphp
                                        @if(!empty($allTitles))
                                            <h3 class="text-xl font-semibold mb-3 text-purple-600 dark:text-purple-400">
                                                {{ $allTitles }}
                                                @if($report->dreams->count() > 1)
                                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">
                                                        ({{ $report->dreams->count() }} {{ $report->dreams->count() == 2 ? 'сна' : ($report->dreams->count() < 5 ? 'сна' : 'снов') }})
                                                    </span>
                                                @endif
                                            </h3>
                                        @endif
                                        <p class="text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                                            {{ \Illuminate\Support\Str::limit($firstDream->description, 300) }}
                                        </p>
                                    @endif
                                    
                                    @if($report->tags->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            @foreach($report->tags as $tag)
                                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="flex gap-6">
                                            <span class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                <i class="far fa-comment"></i>
                                                <span>{{ $report->comments->where('parent_id', null)->count() }}</span>
                                            </span>
                                            <span class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                <i class="far fa-eye"></i>
                                                <span>{{ $report->dreams->count() }}</span>
                                            </span>
                                        </div>
                                        <a href="{{ route('reports.show', $report) }}" class="px-4 py-2 gradient-primary text-white rounded-lg text-sm font-medium hover:shadow-lg transition-all">
                                            <i class="fas fa-brain mr-2"></i>Просмотр
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="mt-6">
                                {{ $reports->links() }}
                            </div>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center card-shadow border border-gray-200 dark:border-gray-700">
                                <p class="text-gray-600 dark:text-gray-400 mb-4">По вашему запросу ничего не найдено.</p>
                                <a href="{{ route('reports.search') }}" class="inline-block mt-4 gradient-primary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all">
                                    <i class="fas fa-redo mr-2"></i>Сбросить фильтры
                                </a>
                            </div>
                        @endif
                    </div>
                </main>
                
                <!-- Правая панель (доступна всем) -->
                <aside class="space-y-6">
                    <!-- Информация о поиске -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> О поиске
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            Используйте фильтры для поиска сновидений по различным критериям. Вы можете искать по тексту, типу сна и дате.
                        </p>
                        @guest
                        <div class="mt-4 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                            <p class="text-xs text-purple-700 dark:text-purple-300 mb-2">
                                <i class="fas fa-lightbulb mr-1"></i> Зарегистрируйтесь, чтобы:
                            </p>
                            <ul class="text-xs text-purple-600 dark:text-purple-400 space-y-1 ml-4">
                                <li>• Создавать свои отчёты</li>
                                <li>• Комментировать записи</li>
                                <li>• Получать статистику</li>
                            </ul>
                        </div>
                        @endguest
                    </div>
                </aside>
            </div>
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
                
                if (window.Alpine && window.Alpine.store) {
                    window.Alpine.store('theme', newTheme);
                }
            }
        </script>
    </body>
</html>




