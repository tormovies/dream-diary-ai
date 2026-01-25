<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: 'light' }"
      x-bind:class="{ 'dark': theme === 'dark' }"
      x-init="
        var savedTheme = localStorage.getItem('theme') || 'light';
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
            <title>Статьи - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        
        {{-- Структурированные данные (JSON-LD) --}}
        @if(isset($structuredData) && !empty($structuredData))
            @foreach($structuredData as $data)
                <x-structured-data :data="$data" />
            @endforeach
        @endif
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                            Статьи
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
                        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $seo['h1'] ?? 'Статьи' }}</h2>
                        @if(isset($seo['h1_description']) && !empty($seo['h1_description']))
                            <div class="mt-4 bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500 dark:border-purple-400 p-4 rounded-r-lg">
                                <p class="text-gray-700 dark:text-gray-300 italic">{{ $seo['h1_description'] }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Список статей -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            @forelse($articles as $article)
                                <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0 last:mb-0">
                                    <h3 class="text-xl font-semibold text-purple-600 dark:text-purple-400 mb-2">
                                        <a href="{{ route('articles.show', $article->slug) }}" class="hover:text-purple-800 dark:hover:text-purple-300 transition-colors">
                                            {{ $article->title }}
                                        </a>
                                    </h3>
                                </div>
                            @empty
                                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                                    Статьи пока не добавлены.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </main>
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
            }
        </script>
    </body>
</html>
