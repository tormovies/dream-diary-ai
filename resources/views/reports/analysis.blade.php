<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: '{{ auth()->check() ? (auth()->user()->theme ?? 'light') : 'light' }}' }"
      x-bind:class="{ 'dark': theme === 'dark' }"
      x-init="
        const savedTheme = localStorage.getItem('theme') || '{{ auth()->check() ? (auth()->user()->theme ?? 'light') : 'light' }}';
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
            @php
                $result = $interpretation->result;
                $isSeries = $result ? ($result->type === 'series') : ($report->dreams->count() > 1);
                $titleText = $isSeries ? 'Расшифровка снов' : 'Расшифровка сна';
            @endphp
            <title>{{ $titleText }} - {{ config('app.name', 'Дневник сновидений') }}</title>
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
            .btn-form-secondary {
                background-color: transparent;
                color: #495057;
                border: 2px solid #dee2e6;
                padding: 8px 16px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 0.875rem;
                transition: all 0.2s;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                white-space: nowrap;
            }
            .dark .btn-form-secondary {
                color: #adb5bd;
                border-color: #343a40;
            }
            .btn-form-secondary:hover {
                background-color: #f8f9fa;
            }
            .dark .btn-form-secondary:hover {
                background-color: #2d2d44;
            }
            .btn-form-danger {
                background-color: transparent;
                color: #fa5252;
                border: 2px solid #fa5252;
                padding: 8px 16px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 0.875rem;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                white-space: nowrap;
            }
            .dark .btn-form-danger {
                color: #ff8787;
                border-color: #ff8787;
            }
            .btn-form-danger:hover {
                background-color: #fa5252;
                color: white;
                transform: translateY(-2px);
            }
            .dark .btn-form-danger:hover {
                background-color: #ff8787;
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
                        @php
                            $result = $interpretation->result;
                            $isSeries = $result ? ($result->type === 'series') : ($report->dreams->count() > 1);
                            $h1Text = $seo['h1'] ?? ($isSeries ? 'Расшифровка снов' : 'Расшифровка сна');
                        @endphp
                        <h1 class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-2">{{ $h1Text }}</h1>
                        <div class="flex flex-row items-center justify-between mb-4">
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Отчёт от {{ $report->report_date->format('d.m.Y') }} • {{ $report->dreams->count() }} {{ $report->dreams->count() === 1 ? 'сон' : 'снов' }}
                            </p>
                        </div>
                        
                        <!-- Спойлер с исходным описанием сна и кнопки -->
                        <div class="flex flex-wrap items-start gap-3 mt-4">
                            <details class="flex-1 min-w-[200px]">
                                <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <i class="fas fa-eye mr-2"></i>Исходное описание {{ $isSeries ? 'снов' : 'сна' }}
                                </summary>
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $interpretation->dream_description }}</div>
                                </div>
                            </details>
                            
                            <!-- Кнопка "Поделиться" -->
                            <div class="relative flex-shrink-0" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                                
                                <!-- Dropdown меню -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10"
                                     style="display: none;">
                                    <div class="py-1">
                                        <a href="#" onclick="shareToVK(event)" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <i class="fab fa-vk mr-2"></i>ВКонтакте
                                        </a>
                                        <a href="#" onclick="shareToTelegram(event)" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <i class="fab fa-telegram mr-2"></i>Telegram
                                        </a>
                                        <a href="#" onclick="copyShareLink(event)" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <i class="fas fa-link mr-2"></i>Копировать
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Кнопки действий (переносятся на новую строку на мобильных) -->
                            <div class="flex flex-wrap gap-3 basis-full md:basis-auto">
                                <a href="{{ route('reports.show', $report) }}" class="btn-form-secondary flex-shrink-0">
                                    <i class="fas fa-arrow-left mr-2"></i>К отчёту
                                </a>
                                
                                @auth
                                    @if(auth()->id() === $report->user_id)
                                        <form action="{{ route('reports.analysis.detach', $report) }}" method="POST" class="inline flex-shrink-0" 
                                              onsubmit="return confirm('Вы уверены? Связь с анализом будет удалена, но сам анализ останется доступен в истории.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-form-danger">
                                                <i class="fas fa-unlink mr-2"></i>Удалить анализ
                                            </button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @php
                        $processingStatus = $interpretation->processing_status ?? 'completed';
                    @endphp

                    @if($processingStatus === 'failed' || $interpretation->api_error)
                        <!-- Ошибка API -->
                        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-2xl p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-4xl text-red-600 dark:text-red-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h2 class="font-bold text-xl mb-2">Ошибка при анализе</h2>
                                    <p class="mb-4">{{ $interpretation->api_error ?? 'Анализ завершился с ошибкой' }}</p>
                                    
                                    <div class="space-y-3 mt-4">
                                        <h4 class="font-semibold text-red-800 dark:text-red-200">
                                            <i class="fas fa-lightbulb text-yellow-600 dark:text-yellow-400 mr-2"></i>Что делать:
                                        </h4>
                                        <ul class="list-disc list-inside space-y-2 text-red-700 dark:text-red-300">
                                            <li>
                                                <strong>Попробуйте создать анализ заново</strong> — в большинстве случаев повторный запрос работает успешно
                                            </li>
                                            <li>
                                                Если проблема повторяется, попробуйте:
                                                <ul class="list-circle list-inside ml-6 mt-1 text-sm">
                                                    <li>Сократить описание сна/снов</li>
                                                    <li>Разделить серию снов на несколько анализов</li>
                                                    <li>Убрать лишние детали из контекста</li>
                                                </ul>
                                            </li>
                                            <li>
                                                Возможно, API временно перегружен — попробуйте позже
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-3 mt-6">
                                        <a href="{{ route('reports.show', $report) }}" 
                                           class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                                            <i class="fas fa-arrow-left mr-2"></i>
                                            Вернуться к отчёту
                                        </a>
                                    </div>
                                    
                                    @if(isset($interpretation->raw_api_response) && $interpretation->raw_api_response)
                                        <details class="mt-4">
                                            <summary class="cursor-pointer font-semibold">Техническая информация (ответ API)</summary>
                                            <pre class="mt-2 text-xs overflow-auto bg-red-50 dark:bg-red-950 p-4 rounded">{{ $interpretation->raw_api_response }}</pre>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif(!$interpretation->result)
                        <!-- Прогресс (если анализ еще выполняется) -->
                        <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 rounded-2xl p-6">
                            <div class="flex items-center gap-4">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-1">
                                        Анализ выполняется...
                                    </h3>
                                    <p class="text-blue-700 dark:text-blue-300 text-sm">
                                        Это может занять до 3 минут. Страница обновится автоматически.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Запускаем обработку через AJAX если статус pending
                            @if($interpretation->processing_status === 'pending')
                                // Запускаем обработку в фоне через 2 секунды (чтобы страница успела отрендериться)
                                setTimeout(function() {
                                    fetch('{{ route('reports.analysis.process', $report) }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        console.log('Analysis process started:', data);
                                        // После запуска обработки - перезагружаем через 3 секунды
                                        setTimeout(function() {
                                            location.reload();
                                        }, 3000);
                                    })
                                    .catch(error => {
                                        console.error('Error starting analysis:', error);
                                        // При ошибке тоже перезагружаем
                                        setTimeout(function() {
                                            location.reload();
                                        }, 3000);
                                    });
                                }, 2000);
                            @else
                                // Если уже в processing - просто ждем и перезагружаем
                                setTimeout(function() {
                                    location.reload();
                                }, 5000);
                            @endif
                        </script>
                    @else
                        <!-- Результаты анализа -->
                        @if($interpretation->analysis_type === 'single')
                            <!-- Одиночный анализ -->
                            @include('dream-analyzer.partials.single-analysis-normalized', ['result' => $interpretation->result, 'interpretation' => $interpretation])
                        @else
                            <!-- Серия снов -->
                            @include('dream-analyzer.partials.series-analysis-normalized', ['result' => $interpretation->result, 'interpretation' => $interpretation])
                        @endif
                    @endif

                    @auth
                        @if(auth()->user()->isAdmin())
                            <!-- Отладочная информация (только для администраторов) -->
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 mt-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Отладочная информация (только для администраторов)</h3>
                                
                                @php
                                    $hasJsonData = isset($interpretation->raw_api_request) || isset($interpretation->raw_api_response) || isset($interpretation->analysis_data);
                                @endphp
                                
                                @if(!$hasJsonData && !$interpretation->api_error)
                                    <!-- Подсказка для админа о том, как загрузить JSON-данные -->
                                    <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-lg mb-4">
                                        <p class="text-sm">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <strong>JSON-данные не загружены</strong> (оптимизация производительности). 
                                            Чтобы просмотреть отладочную информацию, добавьте <code class="bg-blue-200 dark:bg-blue-950 px-2 py-1 rounded">?debug=1</code> к URL:
                                        </p>
                                        <a href="?debug=1" class="inline-block mt-2 text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition-colors">
                                            <i class="fas fa-bug mr-1"></i> Загрузить JSON-данные
                                        </a>
                                    </div>
                                @endif
                                
                                <div class="space-y-4">
                                    @php
                                        // Функция для форматирования JSON
                                        $formatJson = function($data) {
                                            if (is_string($data)) {
                                                $decoded = json_decode($data, true);
                                                if (json_last_error() === JSON_ERROR_NONE) {
                                                    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                                }
                                                return $data;
                                            } elseif (is_array($data) || is_object($data)) {
                                                return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                            }
                                            return $data;
                                        };
                                        
                                        // Вычисляем время обработки
                                        $processingDuration = null;
                                        if ($interpretation->processing_started_at && $interpretation->updated_at) {
                                            $start = \Carbon\Carbon::parse($interpretation->processing_started_at);
                                            $end = \Carbon\Carbon::parse($interpretation->updated_at);
                                            $durationSeconds = $start->diffInSeconds($end);
                                            $processingDuration = [
                                                'seconds' => $durationSeconds,
                                                'minutes' => round($durationSeconds / 60, 2),
                                                'formatted' => $start->diffForHumans($end, true)
                                            ];
                                        }
                                        
                                        // Извлекаем информацию о токенах
                                        $tokensInfo = null;
                                        if (!empty($interpretation->raw_api_response)) {
                                            $response = json_decode($interpretation->raw_api_response, true);
                                            if ($response && isset($response['usage'])) {
                                                $tokensInfo = [
                                                    'prompt_tokens' => $response['usage']['prompt_tokens'] ?? null,
                                                    'completion_tokens' => $response['usage']['completion_tokens'] ?? null,
                                                    'total_tokens' => $response['usage']['total_tokens'] ?? null,
                                                ];
                                            }
                                        }
                                    @endphp
                                    
                                    <!-- Информация о времени обработки и токенах -->
                                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">
                                            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mr-2"></i>Статистика обработки
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @if($processingDuration)
                                                <div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Время обработки:</div>
                                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                                        {{ $processingDuration['seconds'] }} сек
                                                        <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
                                                            ({{ $processingDuration['minutes'] }} мин)
                                                        </span>
                                                    </div>
                                                    @if($interpretation->processing_started_at && $interpretation->updated_at)
                                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                            {{ $interpretation->processing_started_at->format('H:i:s') }} → {{ $interpretation->updated_at->format('H:i:s') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Время обработки:</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-500">Недоступно</div>
                                                </div>
                                            @endif
                                            
                                            @if($tokensInfo)
                                                <div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Токены:</div>
                                                    <div class="space-y-1">
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-sm text-gray-600 dark:text-gray-400">Отправлено (prompt):</span>
                                                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                                                {{ number_format($tokensInfo['prompt_tokens'], 0, ',', ' ') }}
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-sm text-gray-600 dark:text-gray-400">Получено (completion):</span>
                                                            <span class="font-semibold text-green-600 dark:text-green-400">
                                                                {{ number_format($tokensInfo['completion_tokens'], 0, ',', ' ') }}
                                                            </span>
                                                        </div>
                                                        <div class="flex justify-between items-center pt-1 border-t border-blue-200 dark:border-blue-700">
                                                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Всего:</span>
                                                            <span class="font-bold text-purple-600 dark:text-purple-400">
                                                                {{ number_format($tokensInfo['total_tokens'], 0, ',', ' ') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Токены:</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-500">Недоступно</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if(!empty($interpretation->raw_api_request))
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">JSON запрос к API:</h4>
                                            <details class="cursor-pointer" open>
                                                <summary class="text-purple-600 dark:text-purple-400 hover:underline mb-2">Показать/скрыть JSON</summary>
                                                <pre class="bg-gray-800 dark:bg-gray-950 text-blue-400 p-4 rounded-lg overflow-auto text-xs" style="max-height: 500px;">{{ $formatJson($interpretation->raw_api_request) }}</pre>
                                            </details>
                                        </div>
                                    @else
                                        <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded-lg">
                                            <p class="text-sm">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                <strong>JSON запрос не сохранен</strong> (поле raw_api_request пусто)
                                            </p>
                                        </div>
                                    @endif
                                    
                                    @if(!empty($interpretation->raw_api_response))
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Полный JSON ответ от API:</h4>
                                            <details class="cursor-pointer" open>
                                                <summary class="text-purple-600 dark:text-purple-400 hover:underline mb-2">Показать/скрыть JSON</summary>
                                                <pre class="bg-gray-800 dark:bg-gray-950 text-green-400 p-4 rounded-lg overflow-auto text-xs" style="max-height: 500px;">{{ $formatJson($interpretation->raw_api_response) }}</pre>
                                            </details>
                                        </div>
                                    @else
                                        <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded-lg">
                                            <p class="text-sm">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                <strong>JSON ответ не сохранен</strong> (поле raw_api_response пусто)
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endauth
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
        
        <!-- Toast уведомление -->
        <div id="toast" class="fixed bottom-8 right-8 bg-green-600 text-white px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 translate-y-20 opacity-0" style="z-index: 9999;">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <span id="toast-message" class="font-semibold text-base">Ссылка скопирована!</span>
            </div>
        </div>
        
        <style>
            #toast.show {
                transform: translateY(0) !important;
                opacity: 1 !important;
            }
        </style>
        
        <script>
            // Получаем SEO данные для шаринга
            const shareUrl = window.location.href;
            const shareTitle = document.querySelector('meta[property="og:title"]')?.content || document.title;
            const shareDescription = document.querySelector('meta[property="og:description"]')?.content || document.querySelector('meta[name="description"]')?.content || '';
            
            // Функция показа toast
            function showToast(message = 'Ссылка скопирована!') {
                console.log('showToast called:', message);
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toast-message');
                
                if (!toast || !toastMessage) {
                    console.error('Toast elements not found');
                    return;
                }
                
                toastMessage.textContent = message;
                toast.classList.add('show');
                console.log('Toast shown');
                
                setTimeout(() => {
                    toast.classList.remove('show');
                    console.log('Toast hidden');
                }, 3000);
            }
            
            // Поделиться ВКонтакте
            function shareToVK(event) {
                event.preventDefault();
                const url = `https://vk.com/share.php?url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent(shareTitle)}&description=${encodeURIComponent(shareDescription)}`;
                window.open(url, '_blank', 'width=600,height=400');
            }
            
            // Поделиться в Telegram
            function shareToTelegram(event) {
                event.preventDefault();
                const text = `${shareTitle}\n\n${shareDescription}`;
                const url = `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(text)}`;
                window.open(url, '_blank', 'width=600,height=400');
            }
            
            // Копировать ссылку
            function copyShareLink(event) {
                event.preventDefault();
                
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    // Современный способ
                    navigator.clipboard.writeText(shareUrl).then(() => {
                        showToast('✅ Ссылка скопирована!');
                    }).catch(() => {
                        // Fallback если не сработало
                        fallbackCopy();
                    });
                } else {
                    // Fallback для старых браузеров
                    fallbackCopy();
                }
            }
            
            // Fallback метод копирования
            function fallbackCopy() {
                const textarea = document.createElement('textarea');
                textarea.value = shareUrl;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                
                try {
                    document.execCommand('copy');
                    showToast('✅ Ссылка скопирована!');
                } catch (err) {
                    showToast('❌ Не удалось скопировать');
                }
                
                document.body.removeChild(textarea);
            }
        </script>
    </body>
</html>












