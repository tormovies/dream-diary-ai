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
        
        {{-- Структурированные данные (JSON-LD) --}}
        @if(isset($structuredData) && !empty($structuredData))
            @foreach($structuredData as $data)
                <x-structured-data :data="$data" />
            @endforeach
        @endif
        
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
                <!-- Левая панель -->
                <aside class="space-y-6">
                    @auth
                        @if(auth()->id() === $user->id)
                            <!-- Приветственная карточка для своего дневника -->
                            <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                                <h3 class="text-xl font-bold mb-2">Мой дневник</h3>
                                <p class="text-purple-100 mb-4 text-sm">
                                    Все ваши записи о сновидениях
                                </p>
                                <a href="{{ route('reports.create') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                                    <i class="fas fa-plus mr-2"></i>Добавить сон
                                </a>
                            </div>
                        @else
                            <!-- Информационная карточка для чужого дневника -->
                            <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                                <h3 class="text-xl font-bold mb-2">Дневник пользователя</h3>
                                <p class="text-purple-100 mb-4 text-sm">
                                    Дневник {{ $user->nickname }}
                                </p>
                            </div>
                        @endif
                    @else
                        <!-- Информационная карточка для неавторизованных -->
                        <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                            <h3 class="text-xl font-bold mb-2">Дневник пользователя</h3>
                            <p class="text-purple-100 mb-4 text-sm">
                                Дневник {{ $user->nickname }}
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
                                    {{ $reports->total() }} {{ $reports->total() == 1 ? 'запись' : ($reports->total() < 5 ? 'записи' : 'записей') }}
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
                    @if(isset($breadcrumbs) && !empty($breadcrumbs))
                        <x-breadcrumbs :items="$breadcrumbs" />
                    @endif
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
                                            {{ $report->report_date->format('d.m.Y') }}
                                        </h3>
                                        <span class="text-xs px-3 py-1 rounded font-semibold
                                            @if($report->access_level === 'all') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                            @elseif($report->access_level === 'friends') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                            @endif">
                                            @if($report->access_level === 'all') Всем
                                            @elseif($report->access_level === 'friends') Друзьям
                                            @else Никому
                                            @endif
                                        </span>
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

                                    <div class="space-y-4">
                                        @foreach($report->dreams as $dream)
                                            <div class="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div class="flex-1 min-w-0">
                                                        @if(!empty($dream->title))
                                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                {{ $dream->title }}
                                                            </h4>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-1 rounded whitespace-nowrap ml-2 flex-shrink-0">
                                                        {{ $dream->dream_type }}
                                                    </span>
                                                </div>
                                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $dream->description }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('reports.show', $report) }}" 
                                           class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium inline-flex items-center">
                                            Подробнее <i class="fas fa-arrow-right ml-2"></i>
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
                                <p class="text-lg">В этом дневнике пока нет отчетов.</p>
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



