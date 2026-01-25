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
            <title>Отчет от {{ $report->report_date->format('d.m.Y') }} - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        
        {{-- Структурированные данные (JSON-LD) --}}
        @if(isset($structuredData) && !empty($structuredData))
            @foreach($structuredData as $data)
                <x-structured-data :data="$data" />
            @endforeach
        @endif
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

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
                            Просмотр отчета
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
                    <x-project-statistics :stats="$globalStats" variant="list" />
                    
                    <!-- Быстрые действия -->
                    <x-guest-quick-actions />
                </aside>
                @endauth
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок и кнопки действий -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="mb-4">
                            <div class="mb-4">
                                <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Отчет от {{ $report->report_date->format('d.m.Y') }}</h2>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="text-xs px-2 py-1 rounded 
                                        @if($report->status === 'published') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300
                                        @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                        @endif">
                                        @if($report->status === 'published') Опубликован
                                        @else Черновик
                                        @endif
                                    </span>
                                    <span class="text-xs px-2 py-1 rounded 
                                        @if($report->access_level === 'all') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300
                                        @elseif($report->access_level === 'friends') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300
                                        @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                        @endif">
                                        @if($report->access_level === 'all') Всем
                                        @elseif($report->access_level === 'friends') Друзьям
                                        @else Никому
                                        @endif
                                    </span>
                                </div>
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
                                        : (auth()->check() ? route('dashboard') : route('home'));
                                @endphp
                                
                                <!-- Кнопка "Назад" (всегда первая) -->
                                <a href="{{ $backUrl }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-arrow-left mr-2"></i>Назад
                                </a>
                                
                                <!-- Кнопка "Страница дневника" (видна всем) -->
                                <a href="{{ route('diary.public', $report->user->public_link) }}" 
                                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-book mr-2"></i>Страница дневника
                                </a>
                                
                                <!-- Кнопка "Посмотреть анализ" (видна всем, если анализ существует) -->
                                @if($report->status === 'published' && $report->hasAnalysis())
                                    <a href="{{ route('reports.analysis', $report) }}" 
                                       class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                        <i class="fas fa-crystal-ball mr-2"></i>Посмотреть анализ
                                    </a>
                                @endif
                                
                                <!-- Кнопки для владельца -->
                                @auth
                                    @if(auth()->id() === $report->user_id)
                                        <a href="{{ route('reports.edit', $report) }}" 
                                           class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                            <i class="fas fa-edit mr-2"></i>Редактировать
                                        </a>
                                        
                                        <!-- Кнопка "Анализировать" (только для владельца, если анализа нет) -->
                                        @if($report->status === 'published' && !$report->hasAnalysis())
                                            <button type="button" 
                                                    onclick="openAnalysisModal()"
                                                    class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                                <i class="fas fa-crystal-ball mr-2"></i>Анализировать
                                            </button>
                                        @endif
                                    @endif
                                @endauth
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p>Автор: {{ $report->user->nickname }}</p>
                        </div>
                    </div>

                    @if(session('info'))
                        <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-lg">
                            {{ session('info') }}
                        </div>
                    @endif

                    @if($report->tags->count() > 0)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap gap-2">
                                @foreach($report->tags as $tag)
                                    <span class="text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-3 py-1 rounded">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @foreach($report->dreams as $index => $dream)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1 min-w-0">
                                        @if(!empty($dream->title))
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $dream->title }}
                                            </h3>
                                        @endif
                                    </div>
                                    <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300 px-2 py-1 rounded whitespace-nowrap ml-2 flex-shrink-0">
                                        {{ $dream->dream_type }}
                                    </span>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $dream->description }}</p>
                            </div>
                        </div>
                    @endforeach

                    <!-- Комментарии -->
                    <div id="comments" class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Комментарии ({{ $report->comments->where('parent_id', null)->count() }})</h3>

                            @if(session('success'))
                                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-4">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <!-- Форма добавления комментария (только для авторизованных) -->
                            @auth
                                @php
                                    $user = auth()->user();
                                    $diaryOwner = $report->user;
                                    $isOwner = $report->user_id === $user->id;
                                    $isAdmin = $user->isAdmin();
                                    $commentPrivacy = $diaryOwner->comment_privacy ?? 'all';
                                    
                                    // Определяем, может ли пользователь комментировать
                                    $canComment = false;
                                    $commentDeniedReason = null;
                                    
                                    // Владелец может комментировать, если не запретил сам себе
                                    if ($isOwner) {
                                        $canComment = $commentPrivacy !== 'none';
                                        if (!$canComment) {
                                            $commentDeniedReason = 'owner_disabled';
                                        }
                                    }
                                    // Админ всегда может
                                    elseif ($isAdmin) {
                                        $canComment = true;
                                    }
                                    // Проверяем настройки приватности комментариев
                                    elseif ($commentPrivacy === 'none') {
                                        $commentDeniedReason = 'disabled_by_owner';
                                    }
                                    elseif ($commentPrivacy === 'only_me') {
                                        $commentDeniedReason = 'only_owner';
                                    }
                                    elseif ($commentPrivacy === 'friends') {
                                        // Проверяем дружбу
                                        $areFriends = \App\Models\Friendship::where(function ($query) use ($user, $diaryOwner) {
                                            $query->where('user_id', $user->id)
                                                ->where('friend_id', $diaryOwner->id)
                                                ->where('status', 'accepted');
                                        })->orWhere(function ($query) use ($user, $diaryOwner) {
                                            $query->where('user_id', $diaryOwner->id)
                                                ->where('friend_id', $user->id)
                                                ->where('status', 'accepted');
                                        })->exists();
                                        
                                        if (!$areFriends) {
                                            $commentDeniedReason = 'friends_only';
                                        } else {
                                            $canComment = true;
                                        }
                                    }
                                    else {
                                        // comment_privacy === 'all' - проверяем доступ к отчету
                                        $canComment = $user->can('view', $report);
                                        if (!$canComment) {
                                            $commentDeniedReason = 'no_access';
                                        }
                                    }
                                @endphp
                                @if($canComment)
                                    <form action="{{ route('comments.store', $report) }}" method="POST" class="mb-6">
                                        @csrf
                                        <div class="mb-3">
                                            <textarea name="content" 
                                                      rows="3" 
                                                      class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                                      placeholder="Напишите комментарий..."
                                                      required></textarea>
                                            @error('content')
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
                                            Отправить комментарий
                                        </button>
                                    </form>
                                @else
                                    <div class="mb-6 p-4 bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 rounded-lg">
                                        @if($commentDeniedReason === 'disabled_by_owner')
                                            <p class="text-sm"><i class="fas fa-ban mr-2"></i>Владелец отчёта отключил возможность комментирования.</p>
                                        @elseif($commentDeniedReason === 'only_owner')
                                            <p class="text-sm"><i class="fas fa-lock mr-2"></i>Только владелец может комментировать этот отчёт.</p>
                                        @elseif($commentDeniedReason === 'friends_only')
                                            <p class="text-sm"><i class="fas fa-user-friends mr-2"></i>Только друзья владельца могут комментировать этот отчёт.</p>
                                        @elseif($commentDeniedReason === 'owner_disabled')
                                            <p class="text-sm"><i class="fas fa-info-circle mr-2"></i>Вы отключили комментарии в настройках профиля.</p>
                                        @else
                                            <p class="text-sm"><i class="fas fa-exclamation-circle mr-2"></i>У вас нет доступа для комментирования этого отчета.</p>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="mb-6 p-4 bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-300 rounded-lg">
                                    <p class="text-sm mb-2">Для комментирования необходимо <a href="{{ route('login') }}" class="underline font-semibold">войти</a> или <a href="{{ route('register') }}" class="underline font-semibold">зарегистрироваться</a>.</p>
                                </div>
                            @endauth

                            <!-- Список комментариев -->
                            <div class="space-y-4">
                                @php
                                    $rootComments = $report->comments->where('parent_id', null)->sortBy('created_at');
                                @endphp

                                @foreach($rootComments as $comment)
                                    @include('comments.partials.comment', ['comment' => $comment, 'level' => 0])
                                @endforeach

                                @if($rootComments->isEmpty())
                                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Пока нет комментариев. Будьте первым!</p>
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

        <!-- Модальное окно выбора традиций для анализа -->
        @auth
            @if(auth()->id() === $report->user_id && $report->status === 'published' && !$report->hasAnalysis())
                <div id="analysisModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
                    <div class="min-h-screen flex items-start justify-center p-4 sm:p-6 pt-20 sm:pt-10 pb-20">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-3xl w-full card-shadow">
                            <div class="p-6 sm:p-8">
                        <div class="flex justify-between items-start mb-6">
                            <h2 class="text-xl sm:text-2xl font-bold text-purple-600 dark:text-purple-400">
                                <i class="fas fa-crystal-ball mr-2"></i>Анализ отчёта
                            </h2>
                            <button onclick="closeAnalysisModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex-shrink-0 ml-4">
                                <i class="fas fa-times text-xl sm:text-2xl"></i>
                            </button>
                        </div>

                        <div class="mb-6">
                            <p class="text-gray-700 dark:text-gray-300 mb-4">
                                Выберите традиции толкования снов для анализа отчёта. 
                                Если не выбрать ни одну, будет использован комплексный анализ.
                            </p>
                            <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-800 dark:text-yellow-300 px-4 py-3 rounded-lg">
                                <p class="text-sm">
                                    <i class="fas fa-hourglass-half mr-2"></i>
                                    <strong>Важно:</strong> Анализ может занять до 3 минут. После запуска вы будете перенаправлены на страницу результатов, где сможете отслеживать прогресс.
                                </p>
                            </div>
                        </div>

                        <form id="analysisForm" action="{{ route('reports.analyze', $report) }}" method="POST">
                            @csrf
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Выберите традиции (необязательно):
                                </label>
                                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach(config('traditions') as $key => $tradition)
                                        @if($tradition['enabled'])
                                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                            <input type="checkbox" 
                                                   name="traditions[]" 
                                                   value="{{ $key }}"
                                                   class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                <span class="tradition-short">{{ $tradition['name_short'] }}</span>
                                                <span class="tradition-full">{{ $tradition['name_full'] }}</span>
                                            </span>
                                        </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-6">
                                <button type="button" onclick="closeAnalysisModal()" class="w-full sm:flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-4 sm:px-6 rounded-lg transition-colors">
                                    <i class="fas fa-times mr-2"></i>Отмена
                                </button>
                                <button type="submit" class="w-full sm:flex-1 bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-4 sm:px-6 rounded-lg transition-colors" id="startAnalysisBtn">
                                    <i class="fas fa-crystal-ball mr-2"></i>Начать анализ
                                </button>
                            </div>
                        </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function openAnalysisModal() {
                        const modal = document.getElementById('analysisModal');
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        // Прокручиваем модалку наверх
                        modal.scrollTop = 0;
                    }

                    function closeAnalysisModal() {
                        document.getElementById('analysisModal').classList.add('hidden');
                        document.body.style.overflow = '';
                    }

                    // Закрытие по клику вне модалки
                    document.getElementById('analysisModal')?.addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeAnalysisModal();
                        }
                    });

                    // Закрытие по Escape
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            closeAnalysisModal();
                        }
                    });

                    // Обработка отправки формы
                    document.getElementById('analysisForm')?.addEventListener('submit', function(e) {
                        const btn = document.getElementById('startAnalysisBtn');
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Запуск анализа...';
                    });
                </script>
            @endif
        @endauth
    </body>
</html>
