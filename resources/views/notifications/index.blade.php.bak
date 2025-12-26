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
            <title>Уведомления - {{ config('app.name', 'Дневник сновидений') }}</title>
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
        </style>
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="profile-grid w-full">
                <!-- Левая панель -->
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Ваши уведомления и статистика
                        </p>
                        <a href="{{ route('reports.create') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm mt-2">
                            <i class="fas fa-plus-circle mr-2"></i>Создать отчёт
                        </a>
                    </div>
                    
                    <!-- Карточка пользователя -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="text-center relative">
                            <a href="{{ route('profile.edit') }}" class="absolute top-0 right-0 text-gray-400 dark:text-gray-500 hover:text-purple-600 dark:hover:text-purple-400 transition-colors" title="Редактировать профиль">
                                <i class="fas fa-edit text-lg"></i>
                            </a>
                            <div class="flex justify-center">
                                <x-avatar :user="auth()->user()" size="lg" />
                            </div>
                            <div class="mt-4">
                                <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ auth()->user()->nickname }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $stats['reports_count'] }} {{ $stats['reports_count'] == 1 ? 'запись' : ($stats['reports_count'] < 5 ? 'записи' : 'записей') }}
                                </div>
                            </div>
                            
                            <div class="flex justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['friends_count'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">друзей</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['dreams_count'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['avg_dreams_per_month'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов/мес</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Быстрое меню -->
                    <div class="sidebar-menu hidden lg:block bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-bars"></i> Меню
                        </h3>
                        <nav class="space-y-2">
                            <a href="{{ route('dream-analyzer.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('dream-analyzer.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-magic w-5"></i> Толкование снов
                            </a>
                            <a href="{{ route('activity.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('activity.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-home w-5"></i> Лента активности
                            </a>
                            <a href="{{ route('reports.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('reports.create') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-plus-circle w-5"></i> Создать отчёт
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
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Сводная статистика -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-chart-bar"></i> Сводная статистика
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <!-- Первая строка -->
                            <a href="{{ route('dashboard') }}" class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-purple-300 dark:border-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 hover:border-purple-400 dark:hover:border-purple-500 transition-colors cursor-pointer">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ $stats['reports_count'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1">
                                    <i class="fas fa-book"></i> Отчеты
                                </div>
                            </a>
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ $stats['dreams_count'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1">
                                    <i class="fas fa-moon"></i> Сны
                                </div>
                            </div>
                            <a href="{{ route('statistics.index') }}" class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-purple-300 dark:border-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 hover:border-purple-400 dark:hover:border-purple-500 transition-colors cursor-pointer">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ $stats['avg_dreams_per_month'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1">
                                    <i class="fas fa-chart-line"></i> Снов/мес
                                </div>
                            </a>
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ $stats['comments_count'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1">
                                    <i class="fas fa-comments"></i> Комментарии
                                </div>
                            </div>
                            <!-- Вторая строка -->
                            <a href="{{ route('users.search') }}" class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-purple-300 dark:border-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 hover:border-purple-400 dark:hover:border-purple-500 transition-colors cursor-pointer">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ $stats['friends_count'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1">
                                    <i class="fas fa-user-friends"></i> Друзья
                                </div>
                            </a>
                            @if($stats['friendship_requests_count'] > 0)
                                <a href="{{ route('users.search') }}" class="text-center p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 border-2 border-yellow-400 dark:border-yellow-600 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 transition-colors cursor-pointer">
                                    <div class="text-2xl font-bold mb-1 text-yellow-600 dark:text-yellow-400">{{ $stats['friendship_requests_count'] }}</div>
                                    <div class="text-sm flex items-center justify-center gap-1 text-yellow-700 dark:text-yellow-300 font-medium">
                                        <i class="fas fa-user-plus"></i> Запросы
                                        <span class="ml-1 text-xs bg-yellow-400 dark:bg-yellow-600 text-yellow-900 dark:text-yellow-100 px-1.5 py-0.5 rounded-full">!</span>
                                    </div>
                                    <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-2 font-medium">
                                        Обработать →
                                    </div>
                                </a>
                            @else
                                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ $stats['friendship_requests_count'] }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1">
                                        <i class="fas fa-user-plus"></i> Запросы
                                    </div>
                                </div>
                            @endif
                            <div class="text-center p-4 rounded-lg {{ $stats['unread_notifications_count'] > 0 ? 'bg-blue-50 dark:bg-blue-900/30 border-2 border-blue-400 dark:border-blue-600' : 'bg-gray-50 dark:bg-gray-700' }}">
                                <div class="text-2xl font-bold mb-1 {{ $stats['unread_notifications_count'] > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-purple-600 dark:text-purple-400' }}">{{ $stats['unread_notifications_count'] }}</div>
                                <div class="text-sm flex items-center justify-center gap-1 {{ $stats['unread_notifications_count'] > 0 ? 'text-blue-700 dark:text-blue-300 font-medium' : 'text-gray-600 dark:text-gray-400' }}">
                                    <i class="fas fa-bell"></i> Уведомления
                                    @if($stats['unread_notifications_count'] > 0)
                                        <span class="ml-1 text-xs bg-blue-400 dark:bg-blue-600 text-blue-900 dark:text-blue-100 px-1.5 py-0.5 rounded-full">!</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">{{ $stats['tags_count'] }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1">
                                    <i class="fas fa-tags"></i> Теги
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Непрочитанные уведомления</h2>
                            @if($notifications->count() > 0)
                                <form action="{{ route('notifications.read-all') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-check-double mr-2"></i>Отметить все как прочитанные
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Сообщения об успехе -->
                    @if(session('success'))
                        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Список уведомлений -->
                    @if($notifications->count() > 0)
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-purple-500' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            @if($notification->type === 'friendship_request')
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="fas fa-user-plus text-purple-600 dark:text-purple-400"></i>
                                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $notification->data['from_user_nickname'] ?? 'Пользователь' }}</span>
                                                    <span class="text-gray-600 dark:text-gray-400">отправил вам запрос в друзья</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="flex gap-2 mt-3">
                                                    <a href="{{ route('users.search') }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium inline-flex items-center">
                                                        Перейти к запросам <i class="fas fa-arrow-right ml-2"></i>
                                                    </a>
                                                </div>
                                            @elseif($notification->type === 'friendship_accepted')
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $notification->data['from_user_nickname'] ?? 'Пользователь' }}</span>
                                                    <span class="text-gray-600 dark:text-gray-400">принял ваш запрос в друзья</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="flex gap-2 mt-3">
                                                    <a href="{{ route('users.search') }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium inline-flex items-center">
                                                        Перейти к друзьям <i class="fas fa-arrow-right ml-2"></i>
                                                    </a>
                                                </div>
                                            @elseif($notification->type === 'comment')
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="fas fa-comment text-blue-600 dark:text-blue-400"></i>
                                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $notification->data['from_user_nickname'] ?? 'Пользователь' }}</span>
                                                    <span class="text-gray-600 dark:text-gray-400">прокомментировал ваш отчет от {{ $notification->data['report_date'] ?? '' }}</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                @if(isset($notification->data['report_id']))
                                                    <div class="flex gap-2 mt-3">
                                                        <a href="{{ route('reports.show', $notification->data['report_id']) }}#comments" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium inline-flex items-center">
                                                            Перейти к комментариям <i class="fas fa-arrow-right ml-2"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            @elseif($notification->type === 'comment_reply')
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="fas fa-reply text-green-600 dark:text-green-400"></i>
                                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $notification->data['from_user_nickname'] ?? 'Пользователь' }}</span>
                                                    <span class="text-gray-600 dark:text-gray-400">ответил на ваш комментарий к отчету от {{ $notification->data['report_date'] ?? '' }}</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                @if(isset($notification->data['comment_preview']))
                                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic pl-6">
                                                        "{{ $notification->data['comment_preview'] }}"
                                                    </div>
                                                @endif
                                                @if(isset($notification->data['report_id']))
                                                    <div class="flex gap-2 mt-3">
                                                        <a href="{{ route('reports.show', $notification->data['report_id']) }}#comments" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium inline-flex items-center">
                                                            Перейти к комментариям <i class="fas fa-arrow-right ml-2"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        @if(!$notification->read_at)
                                            <form action="{{ route('notifications.read', $notification) }}" method="POST" class="ml-4">
                                                @csrf
                                                <button type="submit" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 text-sm p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Отметить как прочитанное">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Пагинация -->
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <div class="text-center text-gray-600 dark:text-gray-400 py-8">
                                <i class="fas fa-bell-slash text-4xl mb-4 text-gray-400 dark:text-gray-600"></i>
                                <p class="text-lg">У вас нет непрочитанных уведомлений.</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">Все уведомления прочитаны!</p>
                            </div>
                        </div>
                    @endif
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



