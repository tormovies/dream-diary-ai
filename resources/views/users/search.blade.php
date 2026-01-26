@extends('layouts.base')

@section('content')
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
                                        <div class="flex flex-wrap items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                                <x-avatar :user="$request->user" size="md" />
                                                <div class="min-w-0 flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-white truncate" title="{{ $request->user->nickname }}">{{ $request->user->nickname }}</p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $request->user->name }}">{{ $request->user->name }}</p>
                                                </div>
                                            </div>
                                            <div class="flex flex-shrink-0 gap-2">
                                                <form action="{{ route('friends.accept', $request) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                                                        <i class="fas fa-check mr-1"></i>Принять
                                                    </button>
                                                </form>
                                                <form action="{{ route('friends.reject', $request) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
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
                                        
                                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all overflow-hidden min-w-0">
                                            <div class="flex items-center gap-4 mb-4 min-w-0">
                                                <x-avatar :user="$friend" size="lg" />
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="font-semibold text-lg text-gray-900 dark:text-white truncate" title="{{ $friend->nickname }}">{{ $friend->nickname }}</h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $friend->name }}">{{ $friend->name }}</p>
                                                </div>
                                            </div>

                                            @if($friend->bio)
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-2">{{ $friend->bio }}</p>
                                            @endif

                                            <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                <a href="{{ route('users.profile', $friend) }}" 
                                                   class="flex-shrink-0 inline-flex items-center justify-center px-3 py-1.5 gradient-primary text-white rounded text-xs font-medium hover:shadow-md transition-all min-h-[32px]">
                                                    Профиль
                                                </a>
                                                <form action="{{ route('friends.destroy', $friendship) }}" method="POST" 
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя из друзей?');"
                                                      class="flex-shrink-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-red-500 hover:bg-red-700 text-white rounded text-xs font-medium transition-colors min-h-[32px] whitespace-nowrap">
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
                        
                        <form method="GET" action="{{ route('users.search') }}" class="flex flex-wrap gap-2 mb-6">
                            <input type="text" 
                                   name="q" 
                                   value="{{ request('q') }}"
                                   placeholder="Поиск по никнейму или имени..." 
                                   class="flex-1 min-w-[200px] px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg md:rounded-r-none bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="submit" class="flex-shrink-0 gradient-primary text-white px-6 py-3 rounded-lg md:rounded-l-none hover:shadow-lg transition-all">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Результаты поиска -->
                    <div class="space-y-6">
                        @if($users->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($users as $user)
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all overflow-hidden min-w-0 {{ $user->is_banned ? 'border-red-300 dark:border-red-700' : '' }} relative">
                                        @if($user->is_banned && auth()->check() && auth()->user()->isAdmin())
                                            <div class="absolute top-4 right-4">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold bg-red-500 text-white rounded-md shadow-sm">
                                                    <i class="fas fa-ban"></i>
                                                    Заблокирован
                                                </span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-4 mb-4 min-w-0">
                                            <x-avatar :user="$user" size="lg" />
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-lg text-gray-900 dark:text-white truncate" title="{{ $user->nickname }}">{{ $user->nickname }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $user->name }}">{{ $user->name }}</p>
                                            </div>
                                        </div>

                                        @if($user->bio)
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-2">{{ $user->bio }}</p>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <a href="{{ route('users.profile', $user) }}" 
                                               class="flex-shrink-0 text-xs px-3 py-1.5 gradient-primary text-white rounded font-medium hover:shadow-md transition-all">
                                                Профиль
                                            </a>
                                            <span class="flex-shrink-0 text-xs px-3 py-1 rounded 
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
                    <!-- Статистика проекта -->
                    <x-project-statistics :stats="$globalStats" />
                    
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
                    
                    <!-- Последние толкования -->
                    @if(isset($latestInterpretations) && $latestInterpretations->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-link"></i> Последние толкования
                        </h3>
                        <ul class="space-y-3">
                            @foreach($latestInterpretations as $interpretation)
                                @php
                                    if ($interpretation->report_id && !$interpretation->relationLoaded('report')) {
                                        $interpretation->load('report');
                                    }
                                    if ($interpretation->report_id && $interpretation->report) {
                                        $interpretationSeo = \App\Helpers\SeoHelper::forReportAnalysis($interpretation->report, $interpretation);
                                        $linkUrl = route('reports.analysis', $interpretation->report->id);
                                    } else {
                                        $interpretationSeo = \App\Helpers\SeoHelper::forDreamAnalyzerResult($interpretation);
                                        $linkUrl = route('dream-analyzer.show', ['hash' => $interpretation->hash]);
                                    }
                                    $linkTitle = $interpretationSeo['title'] ?? 'Толкование сна';
                                    if (mb_strlen($linkTitle) > 70) {
                                        $linkTitle = mb_substr($linkTitle, 0, 67) . '...';
                                    }
                                @endphp
                                <li>
                                    <a href="{{ $linkUrl }}" 
                                       class="block p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all group">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 line-clamp-2">
                                            {{ $linkTitle }}
                                        </div>
                                        @if(!empty($interpretationSeo['description']))
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                                {{ mb_substr($interpretationSeo['description'], 0, 80) }}{{ mb_strlen($interpretationSeo['description']) > 80 ? '...' : '' }}
                                            </div>
                                        @endif
                                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                            {{ $interpretation->created_at->format('d.m.Y') }}
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
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
                    <x-project-statistics :stats="$stats" />
                </aside>
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0 col-span-2">
                    <!-- Заголовок и форма поиска -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-4 text-purple-600 dark:text-purple-400">Поиск пользователей</h2>
                        
                        <form method="GET" action="{{ route('users.search') }}" class="flex flex-wrap gap-2 mb-6">
                            <input type="text" 
                                   name="q" 
                                   value="{{ request('q') }}"
                                   placeholder="Поиск по никнейму или имени..." 
                                   class="flex-1 min-w-[200px] px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg md:rounded-r-none bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="submit" class="flex-shrink-0 gradient-primary text-white px-6 py-3 rounded-lg md:rounded-l-none hover:shadow-lg transition-all">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Результаты поиска -->
                    <div class="space-y-6">
                        @if($users->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($users as $user)
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all overflow-hidden min-w-0 {{ $user->is_banned ? 'border-red-300 dark:border-red-700' : '' }} relative">
                                        @if($user->is_banned && auth()->check() && auth()->user()->isAdmin())
                                            <div class="absolute top-4 right-4">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold bg-red-500 text-white rounded-md shadow-sm">
                                                    <i class="fas fa-ban"></i>
                                                    Заблокирован
                                                </span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-4 mb-4 min-w-0">
                                            <x-avatar :user="$user" size="lg" />
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-lg text-gray-900 dark:text-white truncate" title="{{ $user->nickname }}">{{ $user->nickname }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $user->name }}">{{ $user->name }}</p>
                                            </div>
                                        </div>

                                        @if($user->bio)
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-2">{{ $user->bio }}</p>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <a href="{{ route('users.profile', $user) }}" 
                                               class="flex-shrink-0 text-xs px-3 py-1.5 gradient-primary text-white rounded font-medium hover:shadow-md transition-all">
                                                Профиль
                                            </a>
                                            <span class="flex-shrink-0 text-xs px-3 py-1 rounded 
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
@endsection
