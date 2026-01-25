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
            <title>Сообщество - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @auth
            <!-- Для авторизованных: трехколоночный layout -->
            <div class="main-grid w-full">
                <!-- Левая панель -->
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Сообщество: друзья и поиск пользователей
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
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Мои друзья -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Мои друзья</h2>
                        </div>
                        
                        @if(session('success'))
                            <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Входящие запросы -->
                        @if($incomingRequests->count() > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400">Входящие запросы в друзья</h3>
                                <div class="space-y-4">
                                    @foreach($incomingRequests as $request)
                                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <div class="flex items-center gap-4">
                                                <x-avatar :user="$request->user" size="md" />
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $request->user->nickname }}</p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->user->name }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <form action="{{ route('friends.accept', $request) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                                        <i class="fas fa-check mr-1"></i>Принять
                                                    </button>
                                                </form>
                                                <form action="{{ route('friends.reject', $request) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                                        <i class="fas fa-times mr-1"></i>Отклонить
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Список друзей -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400">Мои друзья ({{ $friends->count() }})</h3>

                            @if($friends->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($friends as $friend)
                                        @php
                                            $friendship = \App\Models\Friendship::where(function ($query) use ($friend) {
                                                $query->where('user_id', auth()->id())
                                                      ->where('friend_id', $friend->id);
                                            })->orWhere(function ($query) use ($friend) {
                                                $query->where('user_id', $friend->id)
                                                      ->where('friend_id', auth()->id());
                                            })->where('status', 'accepted')->first();
                                        @endphp
                                        
                                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all">
                                            <div class="flex items-center gap-4 mb-4">
                                                <x-avatar :user="$friend" size="lg" />
                                                <div class="flex-1">
                                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-white">{{ $friend->nickname }}</h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $friend->name }}</p>
                                                </div>
                                            </div>

                                            @if($friend->bio)
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-2">{{ $friend->bio }}</p>
                                            @endif

                                            <div class="flex items-stretch gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                <a href="{{ route('users.profile', $friend) }}" 
                                                   class="flex-1 inline-flex items-center justify-center px-2 py-1.5 gradient-primary text-white rounded text-xs font-medium hover:shadow-md transition-all min-h-[32px]">
                                                    Профиль
                                                </a>
                                                <form action="{{ route('friends.destroy', $friendship) }}" method="POST" 
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя из друзей?');"
                                                      class="flex-1">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full h-full inline-flex items-center justify-center px-2 py-1.5 bg-red-500 hover:bg-red-700 text-white rounded text-xs font-medium transition-colors min-h-[32px]">
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">У вас пока нет друзей.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Поиск пользователей -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-4 text-purple-600 dark:text-purple-400">Найти друзей</h2>
                        
                        <form method="GET" action="{{ route('users.search') }}" class="flex gap-2 mb-6">
                            <input type="text" 
                                   name="q" 
                                   value="{{ request('q') }}"
                                   placeholder="Поиск по никнейму или имени..." 
                                   class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-l-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="submit" class="gradient-primary text-white px-6 py-3 rounded-r-lg hover:shadow-lg transition-all">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Результаты поиска -->
                    <div class="space-y-6">
                        @if($users->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($users as $user)
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all {{ $user->is_banned ? 'border-red-300 dark:border-red-700' : '' }} relative">
                                        @if($user->is_banned && auth()->check() && auth()->user()->isAdmin())
                                            <div class="absolute top-4 right-4">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold bg-red-500 text-white rounded-md shadow-sm">
                                                    <i class="fas fa-ban"></i>
                                                    Заблокирован
                                                </span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-4 mb-4">
                                            <x-avatar :user="$user" size="lg" />
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-lg text-gray-900 dark:text-white">{{ $user->nickname }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->name }}</p>
                                            </div>
                                        </div>

                                        @if($user->bio)
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-2">{{ $user->bio }}</p>
                                        @endif

                                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <a href="{{ route('users.profile', $user) }}" 
                                               class="text-xs px-3 py-1 gradient-primary text-white rounded font-medium hover:shadow-md transition-all">
                                                Профиль
                                            </a>
                                            <span class="text-xs px-3 py-1 rounded 
                                                @if($user->diary_privacy === 'public') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                                @elseif($user->diary_privacy === 'friends') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                                @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                                @endif">
                                                @if($user->diary_privacy === 'public') Публичный
                                                @elseif($user->diary_privacy === 'friends') Друзьям
                                                @else Приватный
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6">
                                {{ $users->links() }}
                            </div>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center card-shadow border border-gray-200 dark:border-gray-700">
                                <p class="text-gray-600 dark:text-gray-400 mb-4">
                                    @if(request('q'))
                                        Пользователи не найдены
                                    @else
                                        Введите запрос для поиска пользователей
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </main>
                
                <!-- Правая панель -->
                <aside class="space-y-6">
                    <!-- Наша статистика -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-chart-line"></i> Наша статистика
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:shadow-md transition-all">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($globalStats['dreams'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Всего снов</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:shadow-md transition-all">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($globalStats['reports'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Отчетов</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:shadow-md transition-all">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($globalStats['users'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Пользователей</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:shadow-md transition-all">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format(round($globalStats['avg_dreams_per_report'] ?? 0, 1), 1, ',', ' ') }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Снов/отчет</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($friendsOnline->count() > 0)
                    <!-- Друзья онлайн -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-user-friends"></i> Друзья онлайн
                        </h3>
                        <div class="space-y-3">
                            @foreach($friendsOnline as $friend)
                                <a href="{{ route('users.profile', $friend) }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <x-avatar :user="$friend" size="sm" />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $friend->nickname }}</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                            @if($friend->reports->count() > 0)
                                                Добавил новый сон
                                            @else
                                                В сети
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Сонник дня -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-book-open"></i> Сонник дня
                        </h3>
                        <div class="space-y-4">
                            @foreach($dreamDictionary as $item)
                                <div class="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                                    <div class="font-semibold text-gray-900 dark:text-white mb-1">{{ $item['symbol'] }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $item['meaning'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    @if($popularTags->count() > 0)
                    <!-- Популярные теги -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-fire"></i> Популярные теги
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($popularTags as $tag)
                                <a href="{{ route('reports.search', ['tags' => [$tag->id]]) }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-purple-100 dark:hover:bg-purple-900 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                                    {{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </aside>
            </div>
            @else
            <!-- Для неавторизованных: трехколоночный layout -->
            <div class="main-grid w-full">
                <!-- Левая панель -->
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
                    <x-project-statistics :stats="$stats" variant="list" />
                </aside>
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0 col-span-2">
                    <!-- Заголовок и форма поиска -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-4 text-purple-600 dark:text-purple-400">Поиск пользователей</h2>
                        
                        <form method="GET" action="{{ route('users.search') }}" class="flex gap-2 mb-6">
                            <input type="text" 
                                   name="q" 
                                   value="{{ request('q') }}"
                                   placeholder="Поиск по никнейму или имени..." 
                                   class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-l-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="submit" class="gradient-primary text-white px-6 py-3 rounded-r-lg hover:shadow-lg transition-all">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Результаты поиска -->
                    <div class="space-y-6">
                        @if($users->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($users as $user)
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all {{ $user->is_banned ? 'border-red-300 dark:border-red-700' : '' }} relative">
                                        @if($user->is_banned && auth()->check() && auth()->user()->isAdmin())
                                            <div class="absolute top-4 right-4">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold bg-red-500 text-white rounded-md shadow-sm">
                                                    <i class="fas fa-ban"></i>
                                                    Заблокирован
                                                </span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-4 mb-4">
                                            <x-avatar :user="$user" size="lg" />
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-lg text-gray-900 dark:text-white">{{ $user->nickname }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->name }}</p>
                                            </div>
                                        </div>

                                        @if($user->bio)
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-2">{{ $user->bio }}</p>
                                        @endif

                                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <a href="{{ route('users.profile', $user) }}" 
                                               class="text-xs px-3 py-1 gradient-primary text-white rounded font-medium hover:shadow-md transition-all">
                                                Профиль
                                            </a>
                                            <span class="text-xs px-3 py-1 rounded 
                                                @if($user->diary_privacy === 'public') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                                @elseif($user->diary_privacy === 'friends') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                                @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                                @endif">
                                                @if($user->diary_privacy === 'public') Публичный
                                                @elseif($user->diary_privacy === 'friends') Друзьям
                                                @else Приватный
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6">
                                {{ $users->links() }}
                            </div>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center card-shadow border border-gray-200 dark:border-gray-700">
                                <p class="text-gray-600 dark:text-gray-400 mb-4">
                                    @if(request('q'))
                                        Пользователи не найдены
                                    @else
                                        Введите запрос для поиска пользователей
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </main>
            </div>
            @endauth
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
