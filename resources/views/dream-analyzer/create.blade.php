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
            <title>Толкование снов - {{ config('app.name', 'Дневник сновидений') }}</title>
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
            .main-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
                width: 100%;
            }
            @media (min-width: 1024px) {
                .main-grid {
                    grid-template-columns: 1fr 320px;
                    gap: 2rem;
                }
            }
            @media (min-width: 1400px) {
                .main-grid {
                    grid-template-columns: 1fr 360px;
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
            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }
            .animate-spin {
                animation: spin 1s linear infinite;
            }
        </style>
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="main-grid w-full">
                <!-- Объединенная левая и центральная панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h1 class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-2">Толкование снов</h1>
                        <p class="text-gray-600 dark:text-gray-400">Получите глубокий психологический анализ вашего сна с использованием различных традиций интерпретации</p>
                    </div>

                    <!-- Форма -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            @if ($errors->any())
                                <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('dream-analyzer.store') }}" id="analyzerForm">
                                @csrf

                                <!-- Описание сна -->
                                <div class="mb-6">
                                    <x-input-label for="dream_description" :value="__('Описание сна')" />
                                    <textarea id="dream_description" 
                                             name="dream_description" 
                                             rows="12"
                                             class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white p-3"
                                             required
                                             minlength="10"
                                             maxlength="10000"
                                             placeholder="Опишите ваш сон максимально подробно. Если снов несколько, разделите их двумя переносами строк или тремя и более тире (----). Постарайтесь описать всё, что помните - детали, эмоции, цвета, звуки, ощущения. Чем больше контекста вы предоставите, тем точнее будет анализ.">{{ old('dream_description') }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Если снов несколько, разделите их двумя переносами строк или тремя и более тире (----). Постарайтесь описать максимально всё, что помните, а не одно слово, потому что ваш контекст важен для точного анализа.
                                    </p>
                                    <x-input-error :messages="$errors->get('dream_description')" class="mt-2" />
                                </div>

                                <!-- Контекст (опционально) -->
                                <div class="mb-6">
                                    <x-input-label for="context" :value="__('Контекст (опционально)')" />
                                    <textarea id="context" 
                                             name="context" 
                                             rows="4"
                                             class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white p-3"
                                             maxlength="2000"
                                             placeholder="Опишите актуальную жизненную ситуацию, эмоциональное состояние, проблемы или переживания, которые могут быть связаны со сном. Это поможет сделать анализ более точным.">{{ old('context') }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-lightbulb mr-1"></i>
                                        Укажите, в связи с чем интересуетесь расшифровкой: ситуация на работе, переживания или что-то другое.
                                    </p>
                                    <x-input-error :messages="$errors->get('context')" class="mt-2" />
                                </div>

                                <!-- Выбор традиций -->
                                <div class="mb-6">
                                    <x-input-label :value="__('Традиции анализа (можно выбрать несколько)')" />
                                    <p class="mt-1 mb-3 text-sm text-gray-500 dark:text-gray-400">
                                        Выберите одну или несколько традиций интерпретации. Если ничего не выбрано, будет использован комплексный анализ.
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @php
                                            $traditions = [
                                                'freudian' => 'Фрейдистский анализ',
                                                'jungian' => 'Юнгианский анализ',
                                                'cognitive' => 'Когнитивная психология сна',
                                                'symbolic' => 'Символическая трактовка',
                                                'shamanic' => 'Шаманистическая трактовка',
                                                'gestalt' => 'Гештальт-подход',
                                                'lucid_centered' => 'Анализ осознанности',
                                                'eclectic' => 'Комплексный анализ',
                                            ];
                                            $oldTraditions = old('traditions', []);
                                        @endphp
                                        @foreach($traditions as $key => $label)
                                            <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                                <input type="checkbox" 
                                                       name="traditions[]" 
                                                       value="{{ $key }}"
                                                       id="tradition_{{ $key }}"
                                                       {{ in_array($key, $oldTraditions) ? 'checked' : '' }}
                                                       class="tradition-checkbox rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500"
                                                       onchange="updateAnalysisTypeVisibility()">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <x-input-error :messages="$errors->get('traditions')" class="mt-2" />
                                </div>

                                <!-- Выбор типа анализа (только если выбрано несколько традиций) -->
                                <div class="mb-6" id="analysisTypeBlock" style="display: none;">
                                    <x-input-label for="analysis_type" :value="__('Тип анализа (обязательно при выборе нескольких традиций)')" />
                                    <p class="mt-1 mb-3 text-sm text-gray-500 dark:text-gray-400">
                                        Выберите тип анализа для интеграции выбранных традиций.
                                    </p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                            <input type="radio" 
                                                   name="analysis_type" 
                                                   value="integrated"
                                                   {{ old('analysis_type') === 'integrated' ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Интегрированный</span>
                                        </label>
                                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                            <input type="radio" 
                                                   name="analysis_type" 
                                                   value="comparative"
                                                   {{ old('analysis_type') === 'comparative' ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Сравнительный</span>
                                        </label>
                                    </div>
                                    <x-input-error :messages="$errors->get('analysis_type')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-end space-x-4">
                                    <button type="submit" 
                                            id="submitBtn"
                                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fas fa-search mr-2" id="submitIcon"></i>
                                        <span id="submitText">Проанализировать сон</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Индикатор загрузки -->
                    <div id="loadingIndicator" class="hidden bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center space-x-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-4 border-purple-200 dark:border-purple-800 border-t-purple-600 dark:border-t-purple-400" style="animation: spin 1s linear infinite;"></div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">Анализ выполняется...</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Это может занять до 2-3 минут. Пожалуйста, подождите.</p>
                            </div>
                        </div>
                    </div>
                </main>

                <!-- Правая панель -->
                <aside class="space-y-6">
                    @guest
                        <!-- Статистика проекта (для неавторизованных) -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                                <i class="fas fa-chart-bar"></i> Статистика проекта
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['users'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Пользователей</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['reports'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Отчетов</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['dreams'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Снов</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($stats['comments'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Комментариев</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">{{ number_format($stats['tags'], 0, ',', ' ') }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Тегов</div>
                                </div>
                            </div>
                        </div>
                    @endguest
                    @auth
                        <!-- Приветственная карточка (для авторизованных) -->
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
                        
                        <!-- Карточка пользователя (для авторизованных) -->
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
                    @else
                        <!-- Быстрые действия (для неавторизованных) -->
                        <x-guest-quick-actions />
                    @endauth
                </aside>
            </div>
        </div>

        <script>
            // Функция для показа/скрытия блока выбора типа анализа
            function updateAnalysisTypeVisibility() {
                const checkboxes = document.querySelectorAll('.tradition-checkbox:checked');
                const analysisTypeBlock = document.getElementById('analysisTypeBlock');
                const analysisTypeInputs = document.querySelectorAll('input[name="analysis_type"]');
                
                if (checkboxes.length > 1) {
                    // Если выбрано больше одной традиции - показываем блок и делаем обязательным
                    analysisTypeBlock.style.display = 'block';
                    analysisTypeInputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                } else {
                    // Если выбрана одна или ноль традиций - скрываем блок и убираем обязательность
                    analysisTypeBlock.style.display = 'none';
                    analysisTypeInputs.forEach(input => {
                        input.removeAttribute('required');
                        input.checked = false;
                    });
                }
            }

            // Инициализация при загрузке страницы
            document.addEventListener('DOMContentLoaded', function() {
                updateAnalysisTypeVisibility();
            });

            document.getElementById('analyzerForm').addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const submitIcon = document.getElementById('submitIcon');
                const submitText = document.getElementById('submitText');
                const loadingIndicator = document.getElementById('loadingIndicator');

                // Отключаем кнопку
                submitBtn.disabled = true;
                submitIcon.className = 'fas fa-spinner fa-spin mr-2';
                submitText.textContent = 'Обработка...';

                // Показываем индикатор загрузки
                loadingIndicator.classList.remove('hidden');
                
                // Принудительно запускаем анимацию
                const spinner = loadingIndicator.querySelector('.animate-spin');
                if (spinner) {
                    spinner.style.animation = 'none';
                    setTimeout(() => {
                        spinner.style.animation = 'spin 1s linear infinite';
                    }, 10);
                }

                // Прокручиваем к индикатору
                loadingIndicator.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        </script>
    </body>
</html>




















