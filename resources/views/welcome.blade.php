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
            <title>{{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

            <!-- Top.Mail.Ru counter -->
            <script type="text/javascript">
            var _tmr = window._tmr || (window._tmr = []);
            _tmr.push({id: "3733093", type: "pageView", start: (new Date()).getTime()});
            (function (d, w, id) {
              if (d.getElementById(id)) return;
              var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
              ts.src = "https://top-fwz1.mail.ru/js/code.js";
              var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
              if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
            })(document, window, "tmr-code");
            </script>
            <noscript><div><img src="https://top-fwz1.mail.ru/counter?id=3733093;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div></noscript>
            <!-- /Top.Mail.Ru counter -->
            <x-header-styles />
            
            <x-yandex-metrika />
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
                    
                    <!-- Быстрое меню -->
                    <x-auth-sidebar-menu />
                </aside>
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок ленты -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-2 text-purple-600 dark:text-purple-400">Лента сновидений</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Самые интересные сны от пользователей</p>
                        
                        <form method="GET" action="{{ route('dashboard') }}" class="flex gap-2 mb-4">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Поиск по снам, тегам, пользователям..." 
                                   class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-l-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="submit" class="gradient-primary text-white px-6 py-3 rounded-r-lg hover:shadow-lg transition-all">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        
                    </div>
                    
                    <!-- Компактная лента сновидений -->
                    <div class="compact-feed">
                        @if($reports->count() > 0)
                            <table class="feed-table">
                                <thead class="feed-table-header">
                                    <tr>
                                        <th>Лента сновидений</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        @php
                                            $diaryName = $report->user->getDiaryName();
                                            $dreamsWithTitles = $report->dreams->filter(function($dream) {
                                                return !empty($dream->title);
                                            });
                                            $allTitles = $dreamsWithTitles->pluck('title')->implode(', ');
                                            if(empty($allTitles)) {
                                                $allTitles = 'Без названия';
                                            }
                                            // Обрезаем если больше 160 символов
                                            $titlesLength = mb_strlen($allTitles);
                                            if($titlesLength > 160) {
                                                $allTitles = mb_substr($allTitles, 0, 160) . '...';
                                            }
                                            $commentsCount = $report->comments->where('parent_id', null)->count();
                                            $whatHappened = 'Добавлен отчет';
                                            $whatHappenedShort = 'Отчёт';
                                            $badgeClass = 'report';
                                            $whatHappenedLink = route('reports.show', $report);
                                            if($commentsCount > 0) {
                                                $whatHappened = 'Новый комментарий';
                                                $whatHappenedShort = 'Коммент';
                                                $badgeClass = 'comment';
                                                $whatHappenedLink = route('reports.show', $report) . '#comments';
                                            }
                                        @endphp
                                        <tr class="feed-table-row">
                                            <td>
                                                <!-- Первая строка -->
                                                <div class="flex items-center justify-between gap-4 mb-2">
                                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                                        @if($report->user->public_link)
                                                            <a href="{{ route('diary.public', $report->user->public_link) }}" class="text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 hover:underline font-bold flex-shrink-0">
                                                                {{ $diaryName }}
                                                            </a>
                                                        @else
                                                            <strong class="text-gray-900 dark:text-white flex-shrink-0">{{ $diaryName }}</strong>
                                                        @endif
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <a href="{{ $whatHappenedLink }}" class="flex-shrink-0">
                                                            <span class="action-text-full text-purple-600 dark:text-purple-400 hover:underline">{{ $whatHappened }}</span>
                                                            <span class="action-badge {{ $badgeClass }}">{{ $whatHappenedShort }}</span>
                                                        </a>
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <div class="diary-author flex-shrink-0">
                                                            <x-avatar :user="$report->user" size="sm" />
                                                            <a href="{{ route('users.profile', $report->user) }}" class="hover:text-purple-600 dark:hover:text-purple-400 hover:underline">
                                                                {{ $report->user->nickname }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Вторая строка -->
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                                        <a href="{{ route('reports.show', $report) }}" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:underline truncate">
                                                            {{ $allTitles }}
                                                        </a>
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <span class="text-gray-600 dark:text-gray-400 text-sm flex-shrink-0">
                                                            {{ $report->dreams->count() }} {{ $report->dreams->count() == 1 ? 'сон' : ($report->dreams->count() < 5 ? 'сна' : 'снов') }}
                                                        </span>
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <span class="text-gray-600 dark:text-gray-400 text-sm flex-shrink-0">
                                                            {{ $report->report_date->format('d.m.Y') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <div class="p-4 text-center border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('activity.index') }}" class="text-purple-600 dark:text-purple-400 hover:underline font-medium text-sm">
                                    <i class="fas fa-plus mr-2"></i>Загрузить больше снов
                                </a>
                            </div>
                        @else
                            <div class="p-12 text-center">
                                <p class="text-gray-600 dark:text-gray-400 mb-4">В ленте сновидений пока нет записей.</p>
                                @auth
                                <a href="{{ route('reports.create') }}" class="inline-block gradient-primary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all text-sm">
                                    <i class="fas fa-plus mr-2"></i>Добавить первый сон
                                </a>
                                @endauth
                            </div>
                        @endif
                    </div>
                </main>
                
                <!-- Правая панель -->
                <aside class="space-y-6">
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
                    
                    @if($userStats && $friendsOnline->count() > 0)
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
                    
                    <!-- Наша статистика (общая статистика проекта) -->
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
                    
                    @if(false)
                    <!-- Сонник дня (скрыт) -->
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
                    @endif
                    
                    @if($popularTags->count() > 0)
                    <!-- Тренды недели -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-fire"></i> Популярные теги
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($popularTags as $tag)
                                <a href="{{ route('dashboard', ['tags' => [$tag->id]]) }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-purple-100 dark:hover:bg-purple-900 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
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
                    
                    <!-- Быстрые действия -->
                    <x-guest-quick-actions />
                </aside>
                
                <!-- Центральная панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок ленты -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold mb-2 text-purple-600 dark:text-purple-400">Лента сновидений</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Самые интересные сны от пользователей</p>
                        
                        <form method="GET" action="{{ route('activity.index') }}" class="flex gap-2 mb-4">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Поиск по снам, тегам, пользователям..." 
                                   class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-l-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <button type="submit" class="gradient-primary text-white px-6 py-3 rounded-r-lg hover:shadow-lg transition-all">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Компактная лента сновидений -->
                    <div class="compact-feed">
                        @if($reports->count() > 0)
                            <table class="feed-table">
                                <thead class="feed-table-header">
                                    <tr>
                                        <th>Лента сновидений</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports->take(10) as $report)
                                        @php
                                            $diaryName = $report->user->getDiaryName();
                                            $dreamsWithTitles = $report->dreams->filter(function($dream) {
                                                return !empty($dream->title);
                                            });
                                            $allTitles = $dreamsWithTitles->pluck('title')->implode(', ');
                                            if(empty($allTitles)) {
                                                $allTitles = 'Без названия';
                                            }
                                            // Обрезаем если больше 160 символов
                                            $titlesLength = mb_strlen($allTitles);
                                            if($titlesLength > 160) {
                                                $allTitles = mb_substr($allTitles, 0, 160) . '...';
                                            }
                                            $commentsCount = $report->comments->where('parent_id', null)->count();
                                            $whatHappened = 'Добавлен отчет';
                                            $whatHappenedShort = 'Отчёт';
                                            $badgeClass = 'report';
                                            $whatHappenedLink = route('reports.show', $report);
                                            if($commentsCount > 0) {
                                                $whatHappened = 'Новый комментарий';
                                                $whatHappenedShort = 'Коммент';
                                                $badgeClass = 'comment';
                                                $whatHappenedLink = route('reports.show', $report) . '#comments';
                                            }
                                        @endphp
                                        <tr class="feed-table-row">
                                            <td>
                                                <!-- Первая строка -->
                                                <div class="flex items-center justify-between gap-4 mb-2">
                                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                                        @if($report->user->public_link)
                                                            <a href="{{ route('diary.public', $report->user->public_link) }}" class="text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 hover:underline font-bold flex-shrink-0">
                                                                {{ $diaryName }}
                                                            </a>
                                                        @else
                                                            <strong class="text-gray-900 dark:text-white flex-shrink-0">{{ $diaryName }}</strong>
                                                        @endif
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <a href="{{ $whatHappenedLink }}" class="flex-shrink-0">
                                                            <span class="action-text-full text-purple-600 dark:text-purple-400 hover:underline">{{ $whatHappened }}</span>
                                                            <span class="action-badge {{ $badgeClass }}">{{ $whatHappenedShort }}</span>
                                                        </a>
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <div class="diary-author flex-shrink-0">
                                                            <x-avatar :user="$report->user" size="sm" />
                                                            <a href="{{ route('users.profile', $report->user) }}" class="hover:text-purple-600 dark:hover:text-purple-400 hover:underline">
                                                                {{ $report->user->nickname }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Вторая строка -->
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                                        <a href="{{ route('reports.show', $report) }}" class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:underline truncate">
                                                            {{ $allTitles }}
                                                        </a>
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <span class="text-gray-600 dark:text-gray-400 text-sm flex-shrink-0">
                                                            {{ $report->dreams->count() }} {{ $report->dreams->count() == 1 ? 'сон' : ($report->dreams->count() < 5 ? 'сна' : 'снов') }}
                                                        </span>
                                                        <span class="text-gray-600 dark:text-gray-400">|</span>
                                                        <span class="text-gray-600 dark:text-gray-400 text-sm flex-shrink-0">
                                                            {{ $report->report_date->format('d.m.Y') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <div class="p-4 text-center border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('activity.index') }}" class="text-purple-600 dark:text-purple-400 hover:underline font-medium text-sm">
                                    Смотреть все →
                                </a>
                            </div>
                        @else
                            <div class="p-12 text-center">
                                <p class="text-gray-600 dark:text-gray-400 mb-4">В ленте сновидений пока нет записей.</p>
                                <a href="{{ route('register') }}" class="inline-block gradient-primary text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all text-sm">
                                    <i class="fas fa-user-plus mr-2"></i>Присоединиться
                                </a>
                            </div>
                        @endif
                    </div>
                </main>
                
                <!-- Правая панель -->
                <aside class="space-y-6">
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
                    
                    @if(false)
                    <!-- Сонник дня (скрыт) -->
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
                    @endif
                    
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

        @auth
        <!-- Кнопка добавления сна (floating) -->
        <a href="{{ route('reports.create') }}" 
           class="fixed bottom-8 right-8 w-16 h-16 gradient-primary text-white rounded-full flex items-center justify-center text-2xl shadow-lg hover:scale-110 transition-transform z-50">
            <i class="fas fa-plus"></i>
        </a>
        @endauth
    </body>
</html>
