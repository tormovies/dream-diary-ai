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
            <title>Дневник: {{ $user->nickname }} - {{ config('app.name', 'Дневник сновидений') }}</title>
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
                    <!-- Информационная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Публичный дневник</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Дневник {{ $user->nickname }}
                        </p>
                        @guest
                            <a href="{{ route('register') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                                <i class="fas fa-user-plus mr-2"></i>Присоединиться
                            </a>
                        @endguest
                    </div>
                    
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
                                    {{ $reports->total() }} {{ $reports->total() == 1 ? 'запись' : ($reports->total() < 5 ? 'записи' : 'записей') }}
                                </div>
                            </div>
                            
                            @php
                                // Подсчет статистики для отображения (только опубликованные отчеты)
                                $userDreamsCount = $user->reports()
                                    ->where('status', 'published')
                                    ->where('access_level', 'all')
                                    ->withCount('dreams')
                                    ->get()
                                    ->sum('dreams_count');
                                
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
                                
                                // Среднее количество снов в месяц (только опубликованные)
                                $firstReport = $user->reports()
                                    ->where('status', 'published')
                                    ->where('access_level', 'all')
                                    ->orderBy('created_at')
                                    ->first();
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
                        <div class="sidebar-menu bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
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
                    @endauth
                </aside>
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $user->getDiaryName() }}</h2>
                        @if($user->bio)
                            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $user->bio }}</p>
                        @endif
                    </div>

                    <!-- Список отчетов -->
                    @if($reports->count() > 0)
                        <div class="space-y-6">
                            @foreach($reports as $report)
                                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                            {{ $report->report_date->format('d.m.Y') }} ({{ $report->dreams->count() }} {{ trans_choice('сон|сна|снов', $report->dreams->count()) }})
                                        </h3>
                                    </div>

                                    @if($report->tags->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            @foreach($report->tags as $tag)
                                                <span class="text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @php
                                        // Получаем минимальную длину для спойлера из настроек (по умолчанию 1000)
                                        $minLength = (int)\App\Models\Setting::getValue('diary_spoiler_min_length', 1000);
                                        
                                        // Собираем весь текст из всех описаний снов для подсчета общей длины
                                        $allDescriptions = '';
                                        foreach ($report->dreams as $dream) {
                                            $allDescriptions .= ($dream->description ?? '') . ' ';
                                        }
                                        $allDescriptions = trim($allDescriptions);
                                        $totalLength = mb_strlen($allDescriptions);
                                        
                                        // Проверяем, нужно ли показывать спойлер
                                        $needsSpoiler = $totalLength > $minLength;
                                        
                                        // Если нужен спойлер, распределяем видимую часть по снам
                                        $processedDreams = [];
                                        $currentLength = 0;
                                        
                                        foreach ($report->dreams as $index => $dream) {
                                            $dreamDescription = $dream->description ?? '';
                                            $dreamLength = mb_strlen($dreamDescription);
                                            
                                            if (!$needsSpoiler) {
                                                // Все видимо полностью
                                                $processedDreams[] = [
                                                    'dream' => $dream,
                                                    'visible_text' => $dreamDescription,
                                                    'hidden_text' => '',
                                                    'has_hidden' => false
                                                ];
                                            } elseif ($currentLength + $dreamLength <= $minLength) {
                                                // Весь сон помещается в видимую часть
                                                $processedDreams[] = [
                                                    'dream' => $dream,
                                                    'visible_text' => $dreamDescription,
                                                    'hidden_text' => '',
                                                    'has_hidden' => false
                                                ];
                                                $currentLength += $dreamLength;
                                            } elseif ($currentLength < $minLength) {
                                                // Часть сна помещается, часть нужно скрыть
                                                $remaining = $minLength - $currentLength;
                                                $visibleText = mb_substr($dreamDescription, 0, $remaining);
                                                
                                                // Обрезаем по границе слова
                                                $lastSpace = mb_strrpos($visibleText, ' ');
                                                if ($lastSpace !== false && $lastSpace > $remaining * 0.7) {
                                                    $visibleText = mb_substr($visibleText, 0, $lastSpace);
                                                    $hiddenText = mb_substr($dreamDescription, $lastSpace + 1);
                                                } else {
                                                    $hiddenText = mb_substr($dreamDescription, $remaining);
                                                }
                                                
                                                $processedDreams[] = [
                                                    'dream' => $dream,
                                                    'visible_text' => $visibleText,
                                                    'hidden_text' => $hiddenText,
                                                    'has_hidden' => true
                                                ];
                                                $currentLength = $minLength;
                                            } else {
                                                // Этот сон полностью в скрытую часть
                                                $processedDreams[] = [
                                                    'dream' => $dream,
                                                    'visible_text' => '',
                                                    'hidden_text' => $dreamDescription,
                                                    'has_hidden' => true
                                                ];
                                            }
                                        }
                                        
                                        $hasHiddenContent = false;
                                        foreach ($processedDreams as $item) {
                                            if ($item['has_hidden'] && !empty($item['hidden_text'])) {
                                                $hasHiddenContent = true;
                                                break;
                                            }
                                        }
                                        
                                        $reportId = $report->id;
                                    @endphp
                                    
                                    <div class="space-y-4" id="dreams-container-{{ $reportId }}">
                                        @foreach($processedDreams as $item)
                                            @if(!empty($item['visible_text']) || !$item['has_hidden'])
                                                @php
                                                    // Если сон обрезан, убираем border и padding снизу, чтобы текст продолжался плавно
                                                    $isTruncated = $item['has_hidden'] && !empty($item['hidden_text']);
                                                    $divClasses = $isTruncated 
                                                        ? '' 
                                                        : 'pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0';
                                                @endphp
                                                <div class="{{ $divClasses }}">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <div class="flex-1 min-w-0">
                                                            @if(!empty($item['dream']->title))
                                                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                    {{ $item['dream']->title }}
                                                                </h4>
                                                            @endif
                                                        </div>
                                                        <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-1 rounded whitespace-nowrap ml-2 flex-shrink-0">
                                                            {{ $item['dream']->dream_type }}
                                                        </span>
                                                    </div>
                                                    @if(!empty($item['visible_text']))
                                                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $item['visible_text'] }}</p>
                                                    @endif
                                                    
                                                    {{-- Продолжение обрезанного сна сразу после видимой части, без отступов --}}
                                                    @if($isTruncated && !empty($item['hidden_text']))
                                                        <p class="hidden text-gray-700 dark:text-gray-300 whitespace-pre-wrap" id="hidden-continuation-{{ $reportId }}-{{ $item['dream']->id }}">{{ $item['hidden_text'] }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        {{-- Полностью скрытые сны --}}
                                        @if($hasHiddenContent)
                                            <div class="hidden space-y-4" id="hidden-dreams-{{ $reportId }}">
                                                @foreach($processedDreams as $item)
                                                    @if($item['has_hidden'] && !empty($item['hidden_text']) && empty($item['visible_text']))
                                                        <div class="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div class="flex-1 min-w-0">
                                                                    @if(!empty($item['dream']->title))
                                                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                            {{ $item['dream']->title }}
                                                                        </h4>
                                                                    @endif
                                                                </div>
                                                                <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-1 rounded whitespace-nowrap ml-2 flex-shrink-0">
                                                                    {{ $item['dream']->dream_type }}
                                                                </span>
                                                            </div>
                                                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $item['hidden_text'] }}</p>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    @if($hasHiddenContent)
                                        <div class="mt-4">
                                            <button type="button" 
                                                    onclick="toggleReportExpand({{ $reportId }})" 
                                                    id="expand-btn-{{ $reportId }}"
                                                    class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium inline-flex items-center">
                                                <i class="fas fa-chevron-down mr-2" id="expand-icon-{{ $reportId }}"></i>
                                                <span id="expand-text-{{ $reportId }}">Развернуть</span>
                                            </button>
                                        </div>
                                    @endif

                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('reports.show', $report) }}" 
                                           class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium inline-flex items-center">
                                            К отчёту <i class="fas fa-arrow-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Пагинация -->
                        <div class="mt-6">
                            {{ $reports->links() }}
                        </div>
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <div class="text-center text-gray-600 dark:text-gray-400 py-8">
                                <i class="fas fa-moon text-4xl mb-4 text-gray-400 dark:text-gray-600"></i>
                                <p class="text-lg">В этом дневнике пока нет публичных отчетов.</p>
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

            function toggleReportExpand(reportId) {
                const hiddenDreams = document.getElementById('hidden-dreams-' + reportId);
                const expandIcon = document.getElementById('expand-icon-' + reportId);
                const expandText = document.getElementById('expand-text-' + reportId);
                
                // Находим все продолжения обрезанных снов
                const continuations = document.querySelectorAll('[id^="hidden-continuation-' + reportId + '-"]');
                
                // Проверяем, развернут ли отчет
                const checkElement = hiddenDreams || (continuations.length > 0 ? continuations[0] : null);
                if (!checkElement) return;
                
                const isExpanded = !checkElement.classList.contains('hidden');
                
                if (isExpanded) {
                    // Сворачиваем - скрываем скрытую часть
                    continuations.forEach(el => el.classList.add('hidden'));
                    if (hiddenDreams) hiddenDreams.classList.add('hidden');
                    expandIcon.classList.remove('fa-chevron-up');
                    expandIcon.classList.add('fa-chevron-down');
                    expandText.textContent = 'Развернуть';
                } else {
                    // Разворачиваем - показываем скрытую часть
                    continuations.forEach(el => el.classList.remove('hidden'));
                    if (hiddenDreams) hiddenDreams.classList.remove('hidden');
                    expandIcon.classList.remove('fa-chevron-down');
                    expandIcon.classList.add('fa-chevron-up');
                    expandText.textContent = 'Свернуть';
                }
            }
        </script>
    </body>
</html>



