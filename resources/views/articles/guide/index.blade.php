<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
            <title>Инструкции - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        
        {{-- Структурированные данные (JSON-LD) --}}
        @if(isset($structuredData) && !empty($structuredData))
            @foreach($structuredData as $data)
                <x-structured-data :data="$data" />
            @endforeach
        @endif
        
        @vite(['resources/css/app.css', 'resources/css/articles.css', 'resources/js/app.js'])
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="profile-grid w-full">
                <!-- Левая панель (только для авторизованных) -->
                @auth
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Инструкции
                        </p>
                        <a href="{{ route('reports.create') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>Добавить сон
                        </a>
                    </div>
                    
                    <!-- Карточка пользователя -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <div class="flex justify-center">
                                <x-avatar :user="auth()->user()" size="lg" />
                            </div>
                            <div class="mt-4">
                                <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ auth()->user()->nickname }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    @if($userStats)
                                        {{ $userStats['reports'] }} {{ $userStats['reports'] == 1 ? 'запись' : ($userStats['reports'] < 5 ? 'записи' : 'записей') }}
                                    @else
                                        Пользователь
                                    @endif
                                </div>
                            </div>
                            
                            @if($userStats)
                            <div class="flex justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $userStats['friends'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">друзей</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $userStats['dreams'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $userStats['avg_per_month'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов/мес</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Быстрое меню -->
                    <x-auth-sidebar-menu />
                </aside>
                @else
                <!-- Левая панель для неавторизованных -->
                <aside class="space-y-6">
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
                    
                    <!-- Статистика проекта -->
                    <x-project-statistics :stats="$globalStats" variant="list" />
                    
                    <!-- Быстрые действия -->
                    <x-guest-quick-actions />
                </aside>
                @endauth
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $seo['h1'] ?? 'Инструкции' }}</h2>
                        @if(isset($seo['h1_description']) && !empty($seo['h1_description']))
                            <div class="mt-4 bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500 dark:border-purple-400 p-4 rounded-r-lg">
                                <p class="text-gray-700 dark:text-gray-300 italic">{{ $seo['h1_description'] }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Список инструкций -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            @forelse($articles as $article)
                                <a href="{{ route('guide.show', $article->slug) }}" class="guide-item-link block mb-4 p-5 bg-gradient-to-r from-purple-50 to-blue-50 dark:from-gray-700 dark:to-gray-700 hover:from-purple-100 hover:to-blue-100 dark:hover:from-gray-600 dark:hover:to-gray-600 border-l-4 border-purple-500 dark:border-purple-400 rounded-r-lg transition-all duration-200 hover:shadow-lg group">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-purple-700 dark:text-purple-300 mb-3 group-hover:text-purple-900 dark:group-hover:text-purple-100 transition-colors flex items-center">
                                                <i class="fas fa-book-open mr-3 text-purple-500 dark:text-purple-400"></i>
                                                {{ $article->title }}
                                            </h3>
                                            @if($article->questions_preview)
                                                @php
                                                    // Разбиваем текст по строкам и фильтруем пустые
                                                    $questions = array_filter(
                                                        array_map('trim', explode("\n", $article->questions_preview)),
                                                        function($line) {
                                                            return !empty($line);
                                                        }
                                                    );
                                                @endphp
                                                @if(count($questions) > 0)
                                                    <ul class="list-disc list-inside space-y-1.5 text-gray-700 dark:text-gray-300 text-sm ml-7">
                                                        @foreach($questions as $question)
                                                            <li>{{ $question }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="ml-4 flex-shrink-0">
                                            <i class="fas fa-arrow-right text-purple-500 dark:text-purple-400 group-hover:text-purple-700 dark:group-hover:text-purple-300 group-hover:translate-x-1 transition-all duration-200"></i>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                                    Инструкции пока не добавлены.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
