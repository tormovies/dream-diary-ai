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
            <title>Профиль: {{ $user->nickname }} - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="profile-grid w-full">
                <!-- Левая панель -->
                <aside class="space-y-6">
                    @auth
                        @if(auth()->id() === $user->id)
                            <!-- Приветственная карточка для своего профиля -->
                            <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                                <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                                <p class="text-purple-100 mb-4 text-sm">
                                    Это ваш профиль
                                </p>
                                <a href="{{ route('profile.edit') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                                    <i class="fas fa-cog mr-2"></i>Редактировать
                                </a>
                            </div>
                        @else
                            <!-- Информационная карточка для чужого профиля -->
                            <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                                <h3 class="text-xl font-bold mb-2">Профиль пользователя</h3>
                                <p class="text-purple-100 mb-4 text-sm">
                                    Просмотр профиля {{ $user->nickname }}
                                </p>
                            </div>
                        @endif
                    @else
                        <!-- Информационная карточка для неавторизованных -->
                        <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                            <h3 class="text-xl font-bold mb-2">Профиль пользователя</h3>
                            <p class="text-purple-100 mb-4 text-sm">
                                Просмотр профиля {{ $user->nickname }}
                            </p>
                        </div>
                    @endauth
                    
                    <!-- Карточка пользователя -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <div class="flex justify-center">
                                <x-avatar :user="$user" size="lg" />
                            </div>
                            <div class="mt-4">
                                <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ $user->nickname }}</div>
                                @if($user->name)
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $user->name }}</div>
                                @endif
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $reportsCount }} {{ $reportsCount == 1 ? 'запись' : ($reportsCount < 5 ? 'записи' : 'записей') }}
                                </div>
                            </div>
                            
                            @php
                                // Подсчет статистики для отображения
                                $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
                                
                                // Подсчет друзей
                                $friendships = \App\Models\Friendship::where(function ($query) use ($user) {
                                    $query->where('user_id', $user->id)
                                        ->where('status', 'accepted');
                                })->orWhere(function ($query) use ($user) {
                                    $query->where('friend_id', $user->id)
                                        ->where('status', 'accepted');
                                })->get();

                                $friendIds = $friendships->map(function ($friendship) use ($user) {
                                    return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
                                })->toArray();
                                
                                $friendsCount = count($friendIds);
                                
                                // Среднее количество снов в месяц
                                $firstReport = $user->reports()->orderBy('created_at')->first();
                                if ($firstReport) {
                                    $monthsDiff = $firstReport->created_at->diffInMonths(now());
                                    $avgDreamsPerMonth = $monthsDiff > 0 ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
                                } else {
                                    $avgDreamsPerMonth = 0;
                                }
                            @endphp
                            
                            <div class="flex justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $friendsCount }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">друзей</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $userDreamsCount }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $avgDreamsPerMonth }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов/мес</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @auth
                        <!-- Быстрое меню -->
                        <x-auth-sidebar-menu />
                    @endauth
                </aside>
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Профиль: {{ $user->nickname }}</h2>
                        <p class="text-lg text-gray-700 dark:text-gray-300 mt-2">
                            <i class="fas fa-book mr-2"></i>{{ $user->getDiaryName() }}
                        </p>
                    </div>

                    <!-- Информация о пользователе -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="space-y-4">
                            @if($user->bio)
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">О себе</h3>
                                    <p class="text-gray-700 dark:text-gray-300">{{ $user->bio }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Отчетов:</span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white ml-2">{{ $reportsCount }}</span>
                                </div>
                                @if($lastReport)
                                    <div>
                                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Последний отчет:</span>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white ml-2">{{ $lastReport->report_date->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Статус дневника:</span>
                                    <span class="ml-2">
                                        @if($user->diary_privacy === 'public')
                                            <span class="text-green-600 dark:text-green-400 font-semibold">Публичный</span>
                                        @elseif($user->diary_privacy === 'friends')
                                            <span class="text-yellow-600 dark:text-yellow-400 font-semibold">Только друзьям</span>
                                        @else
                                            <span class="text-gray-600 dark:text-gray-400 font-semibold">Приватный</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if($user->public_link)
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Публичная ссылка на дневник:</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" 
                                               value="{{ route('diary.public', $user->public_link) }}" 
                                               readonly 
                                               class="block w-full border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-md shadow-sm text-sm text-gray-900 dark:text-white" 
                                               id="public-link-{{ $user->id }}" />
                                        <button type="button" 
                                                onclick="copyLink({{ $user->id }})" 
                                                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-copy mr-1"></i>Копировать
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Действия -->
                    @auth
                        @if(auth()->id() !== $user->id)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Действия</h3>
                                <div class="flex flex-wrap gap-4">
                                    @php
                                        $friendship = \App\Models\Friendship::where(function ($query) use ($user) {
                                            $query->where('user_id', auth()->id())
                                                  ->where('friend_id', $user->id);
                                        })->orWhere(function ($query) use ($user) {
                                            $query->where('user_id', $user->id)
                                                  ->where('friend_id', auth()->id());
                                        })->first();
                                    @endphp

                                    @if(!$friendship)
                                        <form action="{{ route('friends.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="friend_id" value="{{ $user->id }}">
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                                <i class="fas fa-user-plus mr-2"></i>Добавить в друзья
                                            </button>
                                        </form>
                                    @elseif($friendship->status === 'pending')
                                        @if($friendship->user_id === auth()->id())
                                            <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-4 py-2 rounded-lg font-semibold">
                                                <i class="fas fa-clock mr-2"></i>Запрос отправлен
                                            </span>
                                        @else
                                            <div class="flex gap-2">
                                                <form action="{{ route('friends.accept', $friendship) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                                        <i class="fas fa-check mr-2"></i>Принять запрос
                                                    </button>
                                                </form>
                                                <form action="{{ route('friends.reject', $friendship) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                                        <i class="fas fa-times mr-2"></i>Отклонить
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    @elseif($friendship->status === 'accepted')
                                        <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-4 py-2 rounded-lg font-semibold">
                                            <i class="fas fa-user-friends mr-2"></i>Друзья
                                        </span>
                                    @endif

                                    @if($canViewDiary)
                                        <a href="{{ route('diary.show', $user) }}" 
                                           class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors inline-flex items-center">
                                            <i class="fas fa-book mr-2"></i>Посмотреть дневник
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endauth
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

            function copyLink(userId) {
                const linkInput = document.getElementById('public-link-' + userId);
                if (linkInput) {
                    linkInput.select();
                    document.execCommand('copy');
                    alert('Ссылка скопирована в буфер обмена');
                }
            }
        </script>
    </body>
</html>
