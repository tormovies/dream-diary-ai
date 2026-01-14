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
            <title>Статистика - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-header-styles />
        
        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
            (function(m,e,t,r,i,k,a){
                m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();
                for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
                k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
            })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=89409547', 'ym');
            ym(89409547, 'init', {ssr:true, clickmap:true, accurateTrackBounce:true, trackLinks:true});
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/89409547" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->
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
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Анализ ваших сновидений
                        </p>
                        <a href="{{ route('reports.create') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>Добавить сон
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
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Статистика по снам</h2>
                    </div>

                    <!-- Основная статистика -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalReports }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Всего отчетов</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalDreams }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Всего снов</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $avgDreamsPerReport }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Среднее снов на отчет</div>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика по типам снов -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Распределение по типам снов</h3>
                        <div class="space-y-3">
                            @foreach($dreamsByType as $dreamType)
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $dreamType->dream_type }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $dreamType->count }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full" 
                                             style="width: {{ $totalDreams > 0 ? ($dreamType->count / $totalDreams * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                            @if($dreamsByType->isEmpty())
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Нет данных</p>
                            @endif
                        </div>
                    </div>

                    <!-- Самые используемые теги -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Популярные теги</h3>
                        <div class="space-y-2">
                            @foreach($topTags as $tag)
                                <div class="flex justify-between items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition-colors">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $tag->name }}</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $tag->count }}</span>
                                </div>
                            @endforeach
                            @if($topTags->isEmpty())
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Нет данных</p>
                            @endif
                        </div>
                    </div>

                    <!-- Активность: 30 дней и дни недели -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Отчеты по дням (последние 30 дней) -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Активность за последние 30 дней (количество снов)</h3>
                            <div class="space-y-2">
                                @php
                                    $maxCount = $reportsByDay->max() ?: 1;
                                @endphp
                                @for($i = 0; $i <= 29; $i++)
                                    @php
                                        $date = now()->subDays($i)->format('Y-m-d');
                                        $count = $reportsByDay->get($date, 0);
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-600 dark:text-gray-400 w-20">{{ now()->subDays($i)->format('d.m') }}</span>
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                            @if($count > 0)
                                                <div class="bg-green-600 dark:bg-green-500 h-4 rounded-full" 
                                                     style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-600 dark:text-gray-400 w-8 text-right">{{ $count }}</span>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Самый активный день недели -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Активность по дням недели</h3>
                            <div class="space-y-3">
                                @php
                                    $weekdayNames = [
                                        'Monday' => 'Понедельник',
                                        'Tuesday' => 'Вторник',
                                        'Wednesday' => 'Среда',
                                        'Thursday' => 'Четверг',
                                        'Friday' => 'Пятница',
                                        'Saturday' => 'Суббота',
                                        'Sunday' => 'Воскресенье',
                                    ];
                                    $maxWeekdayCount = $reportsByWeekday->max('count') ?: 1;
                                @endphp
                                @foreach($reportsByWeekday as $weekday)
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $weekdayNames[$weekday->weekday] ?? $weekday->weekday }}</span>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $weekday->count }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-purple-600 dark:bg-purple-500 h-2 rounded-full" 
                                                 style="width: {{ ($weekday->count / $maxWeekdayCount) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($reportsByWeekday->isEmpty())
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Нет данных</p>
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
