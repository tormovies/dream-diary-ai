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
            <title>Лента активности - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            .main-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
                width: 100%;
            }
            @media (min-width: 1024px) {
                .main-grid {
                    grid-template-columns: 280px 1fr 320px;
                    gap: 2rem;
                }
            }
            @media (min-width: 1400px) {
                .main-grid {
                    grid-template-columns: 320px 1fr 360px;
                    gap: 2.5rem;
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
            @auth
            <!-- Для авторизованных: трехколоночный layout -->
            <div class="main-grid w-full">
                <!-- Левая панель -->
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            @if($todayReportsCount > 0)
                                Сегодня {{ $todayReportsCount }} {{ $todayReportsCount == 1 ? 'человек поделился' : ($todayReportsCount < 5 ? 'человека поделились' : 'человек поделились') }} своими сновидениями.
                            @else
                                Сегодня пока никто не поделился сновидениями.
                            @endif
                            Не забывайте записывать свои сны!
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
                    <!-- Заголовок ленты -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-2 text-purple-600 dark:text-purple-400">Лента активности</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Самые интересные сны от пользователей</p>
                        
                        <!-- Фильтр -->
                        <div class="flex border-b border-gray-200 dark:border-gray-700">
                            <a href="{{ route('activity.index', ['filter' => 'all']) }}" 
                               class="px-6 py-3 font-medium text-sm border-b-2 {{ $filter === 'all' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                Все сны
                            </a>
                            <a href="{{ route('activity.index', ['filter' => 'friends']) }}" 
                               class="px-6 py-3 font-medium text-sm border-b-2 {{ $filter === 'friends' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                Друзья
                            </a>
                        </div>
                    </div>
                    
                    <!-- Лента активности -->
                    <div class="space-y-6">
                        @if($activities->count() > 0)
                            @foreach($activities as $activity)
                                @if($activity['type'] === 'report')
                                    @php $report = $activity['item']; @endphp
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex items-center gap-4">
                                                <x-avatar :user="$report->user" size="md" />
                                                <div>
                                                    <a href="{{ route('users.profile', $report->user) }}" class="font-semibold text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400">
                                                        {{ $report->user->nickname }}
                                                    </a>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $report->created_at->diffForHumans() }} • 
                                                        @php
                                                            $readingTime = ceil(strlen(collect($report->dreams)->pluck('description')->implode(' ')) / 1000);
                                                        @endphp
                                                        {{ $readingTime }} {{ $readingTime == 1 ? 'мин' : 'мин' }} чтения
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
                                @elseif($activity['type'] === 'comment')
                                    @php $comment = $activity['item']; @endphp
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex items-center gap-4">
                                                <x-avatar :user="$comment->user" size="md" />
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-white">
                                                        {{ $comment->user->nickname }}
                                                    </div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                        прокомментировал отчет <a href="{{ route('users.profile', $comment->report->user) }}" class="text-purple-600 dark:text-purple-400 hover:underline">{{ $comment->report->user->nickname }}</a>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                        {{ $comment->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                                            {{ \Illuminate\Support\Str::limit($comment->content, 200) }}
                                        </p>
                                        
                                        <a href="{{ route('reports.show', $comment->report) }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium">
                                            Перейти к отчету →
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center card-shadow border border-gray-200 dark:border-gray-700">
                                <p class="text-gray-600 dark:text-gray-400">В ленте активности пока нет записей.</p>
                                @if($filter === 'friends' && auth()->check())
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">Попробуйте переключиться на "Все"</p>
                                @endif
                                @auth
                                <a href="{{ route('reports.create') }}" class="inline-block mt-4 gradient-primary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all">
                                    <i class="fas fa-plus mr-2"></i>Добавить первый сон
                                </a>
                                @endauth
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
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-chart-bar"></i> Статистика проекта
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Пользователей</span>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['users'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Отчетов</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['reports'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Снов</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($stats['dreams'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Комментариев</span>
                                <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($stats['comments'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Тегов</span>
                                <span class="text-lg font-bold text-pink-600 dark:text-pink-400">{{ number_format($stats['tags'], 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Быстрые действия -->
                    <x-guest-quick-actions />
                </aside>
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок ленты -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-2 text-purple-600 dark:text-purple-400">Лента активности</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Самые интересные сны от пользователей</p>
                    </div>
                    
                    <!-- Лента активности -->
                    <div class="space-y-6">
                        @if($activities->count() > 0)
                            @foreach($activities->take(10) as $activity)
                                @if($activity['type'] === 'report')
                                    @php $report = $activity['item']; @endphp
                                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex items-center gap-4">
                                                <x-avatar :user="$report->user" size="md" />
                                                <div>
                                                    <a href="{{ route('users.profile', $report->user) }}" class="font-semibold text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400">
                                                        {{ $report->user->nickname }}
                                                    </a>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
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
                                            <a href="{{ route('login') }}" class="px-4 py-2 gradient-primary text-white rounded-lg text-sm font-medium hover:shadow-lg transition-all">
                                                <i class="fas fa-sign-in-alt mr-2"></i>Войти для просмотра
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center card-shadow border border-gray-200 dark:border-gray-700">
                                <p class="text-gray-600 dark:text-gray-400 mb-4">В ленте активности пока нет записей.</p>
                                <a href="{{ route('register') }}" class="inline-block mt-4 gradient-primary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all">
                                    <i class="fas fa-user-plus mr-2"></i>Присоединиться
                                </a>
                            </div>
                        @endif
                    </div>
                </main>
                
                <!-- Правая панель -->
                <aside class="space-y-6">
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
                                <a href="{{ route('login') }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-purple-100 dark:hover:bg-purple-900 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                                    {{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </aside>
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
