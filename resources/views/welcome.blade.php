@extends('layouts.base')

@section('content')
    <!-- Основной контент -->
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6" style="min-height: 800px;">
            @auth
            <!-- Для авторизованных: трехколоночный layout -->
            <div class="main-grid w-full">
                <!-- Левая панель -->
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow" style="min-height: 180px;">
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
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700" style="min-height: 150px;">
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
                    @if($userStats && $friendsOnline->count() > 0)
                    <!-- Друзья онлайн -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700" style="min-height: 150px;">
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
                    
                    <!-- Статистика проекта -->
                    <x-project-statistics :stats="$globalStats" />
                    
                    <!-- Последние толкования -->
                    @if(isset($latestInterpretations) && $latestInterpretations->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700" style="min-height: 200px;">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-link"></i> Последние толкования
                        </h3>
                        <ul class="space-y-3">
                            @foreach($latestInterpretations as $interpretation)
                                @php
                                    // Загружаем связь report, если она есть
                                    if ($interpretation->report_id && !$interpretation->relationLoaded('report')) {
                                        $interpretation->load('report');
                                    }
                                    
                                    // Используем правильный метод SEO в зависимости от типа
                                    if ($interpretation->report_id && $interpretation->report) {
                                        // Это анализ отчета
                                        $interpretationSeo = \App\Helpers\SeoHelper::forReportAnalysis($interpretation->report, $interpretation);
                                        $linkUrl = route('reports.analysis', $interpretation->report->id);
                                    } else {
                                        // Это толкование сна
                                        $interpretationSeo = \App\Helpers\SeoHelper::forDreamAnalyzerResult($interpretation);
                                        $linkUrl = route('dream-analyzer.show', ['hash' => $interpretation->hash]);
                                    }
                                    
                                    $linkTitle = $interpretationSeo['title'] ?? 'Толкование сна';
                                    // Обрезаем title если слишком длинный
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
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700" style="min-height: 150px;">
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
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow" style="min-height: 180px;">
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
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700" style="min-height: 150px;">
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
                    <!-- Последние толкования -->
                    @if(isset($latestInterpretations) && $latestInterpretations->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-link"></i> Последние толкования
                        </h3>
                        <ul class="space-y-3">
                            @foreach($latestInterpretations as $interpretation)
                                @php
                                    // Загружаем связь report, если она есть
                                    if ($interpretation->report_id && !$interpretation->relationLoaded('report')) {
                                        $interpretation->load('report');
                                    }
                                    
                                    // Используем правильный метод SEO в зависимости от типа
                                    if ($interpretation->report_id && $interpretation->report) {
                                        // Это анализ отчета
                                        $interpretationSeo = \App\Helpers\SeoHelper::forReportAnalysis($interpretation->report, $interpretation);
                                        $linkUrl = route('reports.analysis', $interpretation->report->id);
                                    } else {
                                        // Это толкование сна
                                        $interpretationSeo = \App\Helpers\SeoHelper::forDreamAnalyzerResult($interpretation);
                                        $linkUrl = route('dream-analyzer.show', ['hash' => $interpretation->hash]);
                                    }
                                    
                                    $linkTitle = $interpretationSeo['title'] ?? 'Толкование сна';
                                    // Обрезаем title если слишком длинный
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
    </div>
@endsection
