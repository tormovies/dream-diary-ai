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
            <title>Отчет от {{ $report->report_date->format('d.m.Y') }} - {{ config('app.name', 'Дневник сновидений') }}</title>
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
            .profile-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
                width: 100%;
            }
            @media (min-width: 1024px) {
                .profile-grid {
                    grid-template-columns: 280px 1fr;
                    gap: 2rem;
                }
            }
            @media (min-width: 1400px) {
                .profile-grid {
                    grid-template-columns: 320px 1fr;
                    gap: 2.5rem;
                }
            }
            /* Если нет левой панели, центральная занимает всю ширину */
            @media (min-width: 1024px) {
                .profile-grid:not(:has(> aside)) {
                    grid-template-columns: 1fr;
                }
            }
            .sidebar-menu {
                display: none;
            }
            @media (min-width: 1024px) {
                .sidebar-menu {
                    display: block;
                }
            }
        </style>
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
                            Просмотр отчета
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
                    <div class="sidebar-menu bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-bars"></i> Меню
                        </h3>
                        <nav class="space-y-2">
                            <a href="{{ route('activity.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('activity.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-home w-5"></i> Лента активности
                            </a>
                            <a href="{{ route('reports.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('reports.create') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-plus-circle w-5"></i> Добавить сон
                            </a>
                            <a href="{{ route('statistics.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('statistics.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-chart-pie w-5"></i> Статистика
                            </a>
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-calendar-alt w-5"></i> Мои отчеты
                            </a>
                            <a href="{{ route('users.search') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('users.search') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-user-friends w-5"></i> Мои друзья
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('profile.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-cog w-5"></i> Настройки
                            </a>
                        </nav>
                    </div>
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
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-chart-bar"></i> Статистика проекта
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Пользователей</span>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ number_format($globalStats['users'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Отчетов</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($globalStats['reports'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Снов</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($globalStats['dreams'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Комментариев</span>
                                <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($globalStats['comments'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Тегов</span>
                                <span class="text-lg font-bold text-pink-600 dark:text-pink-400">{{ number_format($globalStats['tags'], 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Быстрые действия -->
                    <div class="sidebar-menu bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-bolt"></i> Быстрые действия
                        </h3>
                        <nav class="space-y-2">
                            <a href="{{ route('dream-analyzer.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('dream-analyzer.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-magic w-5"></i> Толкование снов
                            </a>
                            <a href="{{ route('register') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all">
                                <i class="fas fa-user-plus w-5"></i> Регистрация
                            </a>
                            <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all">
                                <i class="fas fa-sign-in-alt w-5"></i> Войти
                            </a>
                            <a href="{{ route('activity.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all">
                                <i class="fas fa-home w-5"></i> Лента активности
                            </a>
                            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all">
                                <i class="fas fa-home w-5"></i> Главная
                            </a>
                        </nav>
                    </div>
                </aside>
                @endauth
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок и кнопки действий -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="mb-4">
                            <div class="mb-4">
                                <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Отчет от {{ $report->report_date->format('d.m.Y') }}</h2>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="text-xs px-2 py-1 rounded 
                                        @if($report->status === 'published') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300
                                        @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                        @endif">
                                        @if($report->status === 'published') Опубликован
                                        @else Черновик
                                        @endif
                                    </span>
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
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2 sm:items-stretch">
                                @php
                                    // Получаем предыдущий URL, если он с нашего сайта
                                    $previousUrl = url()->previous();
                                    $currentHost = parse_url(url('/'), PHP_URL_HOST);
                                    $previousHost = parse_url($previousUrl, PHP_URL_HOST);
                                    
                                    // Если предыдущий URL с нашего сайта, используем его, иначе соответствующая стартовая страница
                                    $backUrl = ($previousHost === $currentHost && $previousUrl !== url()->current()) 
                                        ? $previousUrl 
                                        : (auth()->check() ? route('dashboard') : route('home'));
                                @endphp
                                
                                <!-- Кнопка "Назад" (всегда первая) -->
                                <a href="{{ $backUrl }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-arrow-left mr-2"></i>Назад
                                </a>
                                
                                <!-- Кнопка "Страница дневника" (видна всем) -->
                                <a href="{{ route('diary.public', $report->user->public_link) }}" 
                                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-book mr-2"></i>Страница дневника
                                </a>
                                
                                <!-- Кнопка "Редактировать" (только для владельца) -->
                                @auth
                                    @if(auth()->id() === $report->user_id)
                                        <a href="{{ route('reports.edit', $report) }}" 
                                           class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                            <i class="fas fa-edit mr-2"></i>Редактировать
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p>Автор: {{ $report->user->nickname }}</p>
                        </div>
                    </div>

                    @if(session('info'))
                        <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-lg">
                            {{ session('info') }}
                        </div>
                    @endif

                    @if($report->tags->count() > 0)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap gap-2">
                                @foreach($report->tags as $tag)
                                    <span class="text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-3 py-1 rounded">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @foreach($report->dreams as $index => $dream)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1 min-w-0">
                                        @if(!empty($dream->title))
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $dream->title }}
                                            </h3>
                                        @endif
                                    </div>
                                    <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300 px-2 py-1 rounded whitespace-nowrap ml-2 flex-shrink-0">
                                        {{ $dream->dream_type }}
                                    </span>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $dream->description }}</p>
                            </div>
                        </div>
                    @endforeach

                    <!-- Комментарии -->
                    <div id="comments" class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Комментарии ({{ $report->comments->where('parent_id', null)->count() }})</h3>

                            @if(session('success'))
                                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-4">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <!-- Форма добавления комментария (только для авторизованных) -->
                            @auth
                                @php
                                    // Владелец всегда может комментировать свой отчёт, админ тоже может
                                    $isOwner = $report->user_id === auth()->id();
                                    $isAdmin = auth()->user()->isAdmin();
                                    $canView = auth()->user()->can('view', $report);
                                    $canComment = $isOwner || $isAdmin || $canView;
                                    
                                    // Временная отладка (удалить после проверки)
                                    if (!$canComment) {
                                        \Log::info('Comment access denied', [
                                            'user_id' => auth()->id(),
                                            'user_role' => auth()->user()->role,
                                            'report_user_id' => $report->user_id,
                                            'isOwner' => $isOwner,
                                            'isAdmin' => $isAdmin,
                                            'canView' => $canView,
                                        ]);
                                    }
                                @endphp
                                @if($canComment)
                                    <form action="{{ route('comments.store', $report) }}" method="POST" class="mb-6">
                                        @csrf
                                        <div class="mb-3">
                                            <textarea name="content" 
                                                      rows="3" 
                                                      class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                      placeholder="Напишите комментарий..."
                                                      required></textarea>
                                            @error('content')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
                                            Отправить комментарий
                                        </button>
                                    </form>
                                @else
                                    <div class="mb-6 p-4 bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 rounded-lg">
                                        <p class="text-sm">У вас нет доступа для комментирования этого отчета.</p>
                                    </div>
                                @endif
                            @else
                                <div class="mb-6 p-4 bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-300 rounded-lg">
                                    <p class="text-sm mb-2">Для комментирования необходимо <a href="{{ route('login') }}" class="underline font-semibold">войти</a> или <a href="{{ route('register') }}" class="underline font-semibold">зарегистрироваться</a>.</p>
                                </div>
                            @endauth

                            <!-- Список комментариев -->
                            <div class="space-y-4">
                                @php
                                    $rootComments = $report->comments->where('parent_id', null)->sortBy('created_at');
                                @endphp

                                @foreach($rootComments as $comment)
                                    @include('comments.partials.comment', ['comment' => $comment, 'level' => 0])
                                @endforeach

                                @if($rootComments->isEmpty())
                                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Пока нет комментариев. Будьте первым!</p>
                                @endif
                            </div>
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
