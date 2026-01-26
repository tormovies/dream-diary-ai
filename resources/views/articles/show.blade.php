@extends('layouts.base')

@push('vite')
    @vite(['resources/css/articles.css'])
@endpush

@section('content')
    <!-- Основной контент -->
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="profile-grid w-full">
                <!-- Левая панель (только для авторизованных) -->
                @auth
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            {{ $article->type === 'guide' ? 'Просмотр инструкции' : 'Просмотр статьи' }}
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
                @else
                <!-- Левая панель для неавторизованных -->
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
                    <x-project-statistics :stats="$globalStats" />
                    
                    <!-- Быстрые действия -->
                    <x-guest-quick-actions />
                </aside>
                @endauth
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок и кнопки действий -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="mb-4">
                            @if(isset($breadcrumbs) && !empty($breadcrumbs))
                                <x-breadcrumbs :items="$breadcrumbs" />
                            @endif
                            <div class="mb-4">
                                <h1 class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $seo['h1'] ?? $article->title }}</h1>
                                @if(isset($seo['h1_description']) && !empty($seo['h1_description']))
                                    <p class="text-gray-600 dark:text-gray-300 mt-3 text-lg leading-relaxed">{{ $seo['h1_description'] }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2 sm:items-stretch">
                                @php
                                    // Получаем предыдущий URL, если он с нашего сайта
                                    $previousUrl = url()->previous();
                                    $currentHost = parse_url(url('/'), PHP_URL_HOST);
                                    $previousHost = parse_url($previousUrl, PHP_URL_HOST);
                                    
                                    // Если предыдущий URL с нашего сайта, используем его, иначе соответствующая стартовая страница
                                    $backUrl = ($previousHost === $currentHost && $previousUrl !== url()->current()) 
                                        ? $previousUrl 
                                        : ($article->type === 'guide' ? route('guide.index') : route('articles.index'));
                                @endphp
                                
                                <!-- Кнопка "Назад" -->
                                <a href="{{ $backUrl }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-arrow-left mr-2"></i>Назад
                                </a>
                                
                                <!-- Кнопка "Список инструкций/статей" -->
                                <a href="{{ $article->type === 'guide' ? route('guide.index') : route('articles.index') }}" 
                                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-list mr-2"></i>{{ $article->type === 'guide' ? 'Все инструкции' : 'Все статьи' }}
                                </a>
                            </div>
                        </div>
                    </div>


                    <!-- Содержимое статьи -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="prose dark:prose-invert max-w-none" style="min-height: 200px;">
                            {!! $article->content !!}
                        </div>
                    </div>
                </main>
            </div>
        </div>
@endsection
