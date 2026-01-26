@extends('layouts.base')

@section('content')
    <!-- Основной контент -->
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="two-column-grid w-full">
                <!-- Объединенная левая и центральная панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        @php
                            $result = $interpretation->result;
                            if ($result) {
                                $isSeries = $result->type === 'series';
                            } else {
                                $analysis = $interpretation->analysis_data ?? [];
                                $isSeries = isset($analysis['series_analysis']) && isset($analysis['dreams']);
                            }
                            // Используем кастомный H1 из SEO, если он указан, иначе дефолтный
                            $h1Text = isset($seo['h1']) && !empty($seo['h1']) 
                                ? $seo['h1'] 
                                : ($isSeries ? 'Расшифровка снов' : 'Расшифровка сна');
                        @endphp
                        @if(isset($breadcrumbs) && !empty($breadcrumbs))
                            <x-breadcrumbs :items="$breadcrumbs" />
                        @endif
                        <h1 class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-2">{{ $h1Text }}</h1>
                        <div class="flex flex-row items-center justify-between mb-4">
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Создано: {{ $interpretation->created_at->format('d.m.Y H:i') }}
                            </p>
                            <a href="{{ route('dream-analyzer.create') }}" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 whitespace-nowrap">
                                <i class="fas fa-plus mr-2"></i>Новое толкование
                            </a>
                        </div>
                        @if(isset($seo['h1_description']) && !empty($seo['h1_description']))
                            <div class="border-l-4 border-purple-500 dark:border-purple-400 pl-4 py-2 my-4 bg-purple-50 dark:bg-purple-900/20 rounded-r-lg">
                                <p class="text-gray-700 dark:text-gray-300 text-base italic leading-relaxed">{{ $seo['h1_description'] }}</p>
                            </div>
                        @endif
                        
                        <!-- Спойлер с исходным описанием сна и кнопка "Поделиться" -->
                        <div class="relative mt-4">
                            <details class="pr-12">
                                <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <i class="fas fa-eye mr-2"></i>Исходное описание {{ $isSeries ? 'снов' : 'сна' }}
                                </summary>
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $interpretation->dream_description }}</div>
                                    @if($interpretation->context)
                                        <div class="mt-4 pt-4 border-t border-gray-300 dark:border-gray-700">
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Контекст</h4>
                                            <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $interpretation->context }}</div>
                                        </div>
                                    @endif
                                </div>
                            </details>
                            
                            <!-- Кнопка "Поделиться" (Вариант 1) - абсолютное позиционирование -->
                            <div class="absolute top-0 right-0" x-data="{ open: false }" @click.away="open = false">
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
                        </div>
                    </div>

                @php
                    // Проверяем статус обработки
                    $processingStatus = $interpretation->processing_status ?? 'completed'; // По умолчанию completed для старых записей
                    $hasResults = ($interpretation->relationLoaded('results') && $interpretation->results->count() > 0) || $interpretation->result;
                @endphp

                @if(session('success'))
                    <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-6 py-4 rounded-lg mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if($processingStatus === 'failed' || $interpretation->api_error)
                    <!-- Ошибка API -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-400 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 px-6 py-6 rounded-lg">
                        <div class="flex items-start gap-3 mb-4">
                            <i class="fas fa-exclamation-triangle text-2xl text-yellow-600 dark:text-yellow-400 mt-1"></i>
                            <div class="flex-1">
                                <h2 class="font-bold text-lg mb-2">Временные трудности в толковании сновидений</h2>
                                <p class="mb-4">
                                    К сожалению, при обработке вашего запроса возникла техническая ошибка. 
                                    Это может быть связано с временными проблемами на стороне сервиса анализа.
                                </p>
                                <div class="bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-800 rounded p-3 mb-4">
                                    <p class="text-sm font-semibold mb-1">Что вы можете сделать:</p>
                                    <ul class="text-sm list-disc list-inside space-y-1">
                                        <li>Попробуйте повторить анализ через несколько минут</li>
                                        <li>Используйте кнопку "Повторить анализ" ниже</li>
                                        <li>Если проблема сохраняется, обратитесь в службу поддержки</li>
                                    </ul>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3 mt-4">
                                    <form method="POST" action="{{ route('dream-analyzer.retry', $interpretation->hash) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                                            <i class="fas fa-redo mr-2"></i>Повторить анализ
                                        </button>
                                    </form>
                                    <a href="https://t.me/snovidec_ru" target="_blank" rel="noopener noreferrer" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors inline-flex items-center justify-center">
                                        <i class="fab fa-telegram mr-2"></i>Служба поддержки
                                    </a>
                                </div>
                            </div>
                        </div>
                        @if(isset($interpretation->raw_api_response) && $interpretation->raw_api_response)
                            <details class="mt-4">
                                <summary class="cursor-pointer font-semibold text-sm">Техническая информация (для отладки)</summary>
                                <pre class="mt-2 text-xs overflow-auto bg-yellow-50 dark:bg-yellow-950 p-4 rounded border border-yellow-200 dark:border-yellow-800">{{ $interpretation->raw_api_response }}</pre>
                            </details>
                        @endif
                        @if($interpretation->api_error)
                            <details class="mt-2">
                                <summary class="cursor-pointer font-semibold text-sm">Сообщение об ошибке</summary>
                                <p class="mt-2 text-sm bg-yellow-50 dark:bg-yellow-950 p-3 rounded border border-yellow-200 dark:border-yellow-800">{{ $interpretation->api_error }}</p>
                            </details>
                        @endif
                    </div>
                @elseif($processingStatus === 'pending' || $processingStatus === 'processing' || !$hasResults)
                    <!-- Прогресс (если анализ еще выполняется) -->
                    <div class="bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-700 rounded-2xl p-6">
                        <div class="flex items-center gap-4">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-1">
                                    Анализ выполняется...
                                </h3>
                                <p class="text-blue-700 dark:text-blue-300 text-sm">
                                    Это может занять до 3 минут. Страница обновится автоматически.
                                </p>
                                <p class="text-blue-700 dark:text-blue-300 text-sm mt-2">
                                    Если вам кажется что анализ затянулся, вы всегда можете вернуться на данную страницу, как только она будет обновлена - анализ будет продолжен, если видите ошибки - сообщите <a href="https://t.me/snovidec_ru" target="_blank" rel="noopener noreferrer" class="text-blue-900 dark:text-blue-100 underline font-semibold hover:text-blue-600 dark:hover:text-blue-300">Службе поддержки</a>.
                                </p>
                                <div class="mt-3">
                                    <button onclick="copyAnalysisLink()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-800 dark:hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                        <i class="fas fa-link"></i>
                                        Скопировать ссылку на эту страницу
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Запускаем обработку через AJAX если статус pending
                        @if($processingStatus === 'pending')
                            // Запускаем обработку в фоне через 2 секунды (чтобы страница успела отрендериться)
                            setTimeout(function() {
                                fetch('{{ route('dream-analyzer.process', $interpretation->hash) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('Analysis process response:', data);
                                    // Если обработка завершена сразу - перезагружаем сразу
                                    if (data.status === 'completed') {
                                        location.reload();
                                    } else {
                                        // Если запущена - перезагружаем через 3 секунды
                                        setTimeout(function() {
                                            location.reload();
                                        }, 3000);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error starting analysis:', error);
                                    // При ошибке перезагружаем через 3 секунды
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
                    @php
                        // Приоритет: сначала нормализованные данные, потом старые
                        $result = $interpretation->result;
                        
                        // Проверяем, есть ли реальные данные в result
                        // Новая система: проверяем analysis_data
                        // Старая система: проверяем отдельные поля
                        $hasResultData = false;
                        $isNewSystem = false;
                        
                        if ($result) {
                            // Проверяем новую систему (analysis_data)
                            if (!empty($result->analysis_data) && is_array($result->analysis_data)) {
                                $hasResultData = true;
                                $isNewSystem = true;
                            }
                            // Проверяем старую систему (отдельные поля)
                            elseif (
                                (is_array($result->general_interpretation ?? null) && count($result->general_interpretation) > 0) ||
                                (is_array($result->key_symbols ?? null) && count($result->key_symbols) > 0) ||
                                (is_array($result->emotional_state ?? null) && count($result->emotional_state) > 0) ||
                                (is_array($result->practical_recommendations ?? null) && count($result->practical_recommendations) > 0)
                            ) {
                                $hasResultData = true;
                                $isNewSystem = false;
                            }
                        }
                        
                        $useNormalized = $hasResultData;
                        
                        if ($useNormalized) {
                            // Используем нормализованные данные
                            $type = $result->type;
                            $isSeries = $type === 'series';
                        } else {
                            // Fallback на старые данные
                            $analysis = $interpretation->analysis_data ?? [];
                            $isSeries = isset($analysis['series_analysis']) && isset($analysis['dreams']);
                            $isNewSingleFormat = isset($analysis['dream_analysis']);
                        }
                        
                    @endphp

                    @if($useNormalized)
                        @if($isNewSystem)
                            <!-- Новая система: данные из analysis_data -->
                            @include('dream-analyzer.partials.new-system-analysis', ['result' => $result, 'interpretation' => $interpretation, 'results' => $interpretation->results ?? collect()])
                        @elseif($isSeries)
                            <!-- Анализ серии снов (нормализованные данные) -->
                            @include('dream-analyzer.partials.series-analysis-normalized', ['result' => $result, 'interpretation' => $interpretation])
                        @else
                            <!-- Анализ одиночного сна (нормализованные данные) -->
                            @include('dream-analyzer.partials.single-analysis-normalized', ['result' => $result, 'interpretation' => $interpretation])
                        @endif
                    @elseif($interpretation->analysis_data)
                        @php
                            $analysis = $interpretation->analysis_data;
                            $isNewSingleFormat = isset($analysis['dream_analysis']);
                            $hasParseError = isset($analysis['parse_error']);
                        @endphp
                        
                        @if($hasParseError)
                            <!-- Ошибка парсинга JSON -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                                <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl p-6 mb-6">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-4xl text-red-600 dark:text-red-400"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-red-800 dark:text-red-300 mb-2">
                                                Анализ не завершён
                                            </h3>
                                            <p class="text-red-700 dark:text-red-400 mb-4">
                                                К сожалению, при обработке ответа от AI произошла ошибка. Анализ был начат, но результат не удалось полностью получить из-за технических ограничений.
                                            </p>
                                            
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-4 border border-red-200 dark:border-red-700">
                                                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                                    <i class="fas fa-info-circle text-red-600 dark:text-red-400 mr-2"></i>Техническая информация:
                                                </h4>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                                    {{ $analysis['parse_error'] ?? 'Неизвестная ошибка' }}
                                                </p>
                                                @if(isset($analysis['json_error_code']) && $analysis['json_error_code'] !== 0)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        Код ошибки: {{ $analysis['json_error_code'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            
                                            <div class="space-y-3">
                                                <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                                                    <i class="fas fa-lightbulb text-yellow-600 dark:text-yellow-400 mr-2"></i>Что делать:
                                                </h4>
                                                <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
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
                                                        Сейчас лимит увеличен с 4000 до 8000 токенов — новые анализы должны работать лучше
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-3 mt-6">
                                                <a href="{{ route('dream-analyzer.create') }}" 
                                                   class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                                                    <i class="fas fa-redo mr-2"></i>
                                                    Создать новый анализ
                                                </a>
                                                <a href="{{ route('dashboard') }}" 
                                                   class="inline-flex items-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                                                    <i class="fas fa-home mr-2"></i>
                                                    На главную
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(isset($analysis['raw_content']))
                                    <!-- Частичный результат (если есть) -->
                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                                        <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">
                                            <i class="fas fa-file-alt mr-2"></i>Частичный результат (может быть неполным):
                                        </h4>
                                        <details class="cursor-pointer">
                                            <summary class="text-purple-600 dark:text-purple-400 hover:underline mb-2">Показать/скрыть текст</summary>
                                            <div class="bg-white dark:bg-gray-800 rounded p-4 max-h-96 overflow-auto">
                                                <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ mb_substr($analysis['raw_content'], 0, 5000) }}{{ mb_strlen($analysis['raw_content']) > 5000 ? '...' : '' }}</pre>
                                            </div>
                                        </details>
                                    </div>
                                @endif
                            </div>
                        @elseif($isSeries)
                            <!-- Анализ серии снов (старый формат) -->
                            @include('dream-analyzer.partials.series-analysis', ['analysis' => $analysis, 'interpretation' => $interpretation])
                        @elseif($isNewSingleFormat)
                            <!-- Новый формат анализа одиночного сна (старый формат) -->
                            @include('dream-analyzer.partials.single-analysis', ['analysis' => $analysis, 'interpretation' => $interpretation])
                        @else
                        <!-- Старый формат анализа (одиночный сон) -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">
                            {{ $analysis['metadata']['title'] ?? 'Анализ сна' }}
                        </h2>
                        
                        @if(isset($analysis['analysis']['traditions']) && is_array($analysis['analysis']['traditions']))
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Традиции анализа:</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($analysis['analysis']['traditions'] as $tradition)
                                        @php
                                            $traditionName = \App\Helpers\TraditionHelper::getDisplayName($tradition);
                                        @endphp
                                        <span class="inline-block bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                                            {{ $traditionName }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(isset($analysis['analysis']['tradition']))
                            <div class="mb-4">
                                @php
                                    $traditionName = \App\Helpers\TraditionHelper::getDisplayName($analysis['analysis']['tradition']);
                                @endphp
                                <span class="inline-block bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                                    {{ $traditionName }}
                                </span>
                            </div>
                        @endif

                        @if(isset($analysis['analysis']['analysis_type']))
                            <div class="mb-4">
                                @php
                                    $analysisTypeNames = [
                                        'single' => 'Одиночный',
                                        'integrated' => 'Интегрированный',
                                        'comparative' => 'Сравнительный',
                                        'series_integrated' => 'Анализ серии снов',
                                    ];
                                    $analysisType = $analysis['analysis']['analysis_type'];
                                    $analysisTypeName = $analysisTypeNames[$analysisType] ?? ucfirst($analysisType);
                                @endphp
                                <span class="inline-block bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm">
                                    Тип анализа: {{ $analysisTypeName }}
                                </span>
                            </div>
                        @endif

                        @if(isset($analysis['analysis']['core_message']))
                            <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg border-l-4 border-purple-500">
                                <h3 class="font-semibold text-purple-800 dark:text-purple-200 mb-2">Главная мысль сна</h3>
                                <p class="text-gray-700 dark:text-gray-300">{{ $analysis['analysis']['core_message'] }}</p>
                            </div>
                        @endif

                        @if(isset($analysis['analysis']['interpretation']))
                            <div class="mb-6">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                    @if(isset($analysis['analysis']['analysis_type']))
                                        @if($analysis['analysis']['analysis_type'] === 'comparative')
                                            Сравнительная интерпретация
                                        @elseif($analysis['analysis']['analysis_type'] === 'series_integrated')
                                            Интерпретация серии снов
                                        @else
                                            Интерпретация
                                        @endif
                                    @else
                                        Интерпретация
                                    @endif
                                </h3>
                                <div class="text-gray-700 dark:text-gray-300 leading-relaxed" style="text-align: left; padding: 0; margin: 0;">
                                    @php
                                        $interpretationText = $analysis['analysis']['interpretation'];
                                        // Для comparative типа выделяем упоминания традиций и добавляем переносы строк
                                        if (isset($analysis['analysis']['analysis_type']) && $analysis['analysis']['analysis_type'] === 'comparative' && isset($analysis['analysis']['traditions'])) {
                                            // Получаем описания традиций из конфига
                                            $traditionNames = [];
                                            foreach (config('traditions') as $key => $tradition) {
                                                $traditionNames[$key] = $tradition['deepseek_description'];
                                            }
                                            
                                            // Сначала добавляем переносы строк и выделяем фразы с традициями после знаков препинания
                                            foreach ($traditionNames as $key => $name) {
                                                // Паттерн для "В [традиция] традиции" - добавляем перенос строки перед фразой и выделяем
                                                $interpretationText = preg_replace(
                                                    '/([.!?])\s+В\s+' . preg_quote($name, '/') . '\s+традиции/i',
                                                    '$1<br><br><strong class="text-purple-600 dark:text-purple-400">В ' . $name . ' традиции</strong>',
                                                    $interpretationText
                                                );
                                                
                                                // Паттерн для "В подходе [традиция]"
                                                $interpretationText = preg_replace(
                                                    '/([.!?])\s+В\s+подходе\s+' . preg_quote($name, '/') . '/i',
                                                    '$1<br><br><strong class="text-purple-600 dark:text-purple-400">В подходе ' . $name . '</strong>',
                                                    $interpretationText
                                                );
                                            }
                                            
                                            // Затем выделяем оставшиеся фразы (просто заменяем все вхождения)
                                            foreach ($traditionNames as $key => $name) {
                                                // Выделяем "В [традиция] традиции"
                                                $interpretationText = preg_replace(
                                                    '/\bВ\s+' . preg_quote($name, '/') . '\s+традиции\b/i',
                                                    '<strong class="text-purple-600 dark:text-purple-400">$0</strong>',
                                                    $interpretationText
                                                );
                                                
                                                // Выделяем "В подходе [традиция]"
                                                $interpretationText = preg_replace(
                                                    '/\bВ\s+подходе\s+' . preg_quote($name, '/') . '\b/i',
                                                    '<strong class="text-purple-600 dark:text-purple-400">$0</strong>',
                                                    $interpretationText
                                                );
                                            }
                                            
                                            // Убираем вложенные теги strong (если они появились)
                                            do {
                                                $oldText = $interpretationText;
                                                $interpretationText = preg_replace(
                                                    '/<strong[^>]*>(<strong[^>]*>.*?<\/strong>)<\/strong>/i',
                                                    '$1',
                                                    $interpretationText
                                                );
                                            } while ($oldText !== $interpretationText);
                                        }
                                    @endphp
                                    {!! nl2br($interpretationText) !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Метаданные -->
                    @if(isset($analysis['metadata']))
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Метаданные</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if(isset($analysis['metadata']['key_symbols']) && is_array($analysis['metadata']['key_symbols']))
                                    <div>
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Ключевые символы</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($analysis['metadata']['key_symbols'] as $symbol)
                                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                                                    {{ $symbol }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(isset($analysis['metadata']['tags']) && is_array($analysis['metadata']['tags']))
                                    <div>
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Теги</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($analysis['metadata']['tags'] as $tag)
                                                <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(isset($analysis['metadata']['unified_locations']) && is_array($analysis['metadata']['unified_locations']))
                                    <div>
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Локации</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($analysis['metadata']['unified_locations'] as $location)
                                                <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm">
                                                    {{ $location }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(isset($analysis['metadata']['dream_type']))
                                    <div>
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Тип сна</h4>
                                        <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                                            {!! \App\Helpers\HtmlHelper::sanitize($analysis['metadata']['dream_type']) !!}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Практические рекомендации -->
                    @if(isset($analysis['practical_recommendations']))
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Практические рекомендации</h3>
                            
                            @if(isset($analysis['practical_recommendations']['journaling_prompts']) && is_array($analysis['practical_recommendations']['journaling_prompts']))
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Вопросы для дневника</h4>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                                        @foreach($analysis['practical_recommendations']['journaling_prompts'] as $prompt)
                                            <li>{{ $prompt }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(isset($analysis['practical_recommendations']['actions']) && is_array($analysis['practical_recommendations']['actions']))
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Рекомендуемые действия</h4>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                                        @foreach($analysis['practical_recommendations']['actions'] as $action)
                                            <li>{{ $action }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(isset($analysis['practical_recommendations']['art_therapy_exercise']))
                                <div>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Упражнение арт-терапии</h4>
                                    <p class="text-gray-700 dark:text-gray-300">{{ $analysis['practical_recommendations']['art_therapy_exercise'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Словарь символов -->
                    @if(isset($analysis['symbols_dictionary']) && is_array($analysis['symbols_dictionary']))
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Словарь символов</h3>
                            <div class="space-y-4">
                                @foreach($analysis['symbols_dictionary'] as $symbol => $meanings)
                                    <div class="border-l-4 border-indigo-500 pl-4">
                                        <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-1">{{ $symbol }}</h4>
                                        @if(isset($meanings['universal_meaning']))
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                <strong>Общее значение:</strong> {{ $meanings['universal_meaning'] }}
                                            </p>
                                        @endif
                                        @if(isset($meanings['tradition_specific']))
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                <strong>В данной традиции:</strong> {{ $meanings['tradition_specific'] }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                        @endif
                    @endif

                    @auth
                        @if(auth()->user()->isAdmin())
                            <!-- Отладочная информация (только для администраторов) -->
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Отладочная информация (только для администраторов)</h3>
                                
                                @php
                                    $hasJsonData = isset($interpretation->raw_api_request) || isset($interpretation->raw_api_response) || isset($interpretation->analysis_data);
                                    
                                    // Отладочная информация для админа
                                    $debugInfo = [
                                        'has_raw_request' => !empty($interpretation->raw_api_request),
                                        'has_raw_response' => !empty($interpretation->raw_api_response),
                                        'has_analysis_data' => !empty($interpretation->analysis_data),
                                        'raw_request_type' => gettype($interpretation->raw_api_request ?? null),
                                        'raw_response_type' => gettype($interpretation->raw_api_response ?? null),
                                        'raw_request_length' => !empty($interpretation->raw_api_request) ? strlen($interpretation->raw_api_request) : 0,
                                        'raw_response_length' => !empty($interpretation->raw_api_response) ? strlen($interpretation->raw_api_response) : 0,
                                        'raw_request_is_null' => is_null($interpretation->raw_api_request ?? null),
                                        'raw_response_is_null' => is_null($interpretation->raw_api_response ?? null),
                                        'processing_status' => $interpretation->processing_status ?? 'unknown',
                                        'interpretation_id' => $interpretation->id,
                                    ];
                                @endphp
                                
                                @if(isset($request) && $request->has('debug'))
                                    <!-- Дополнительная отладочная информация -->
                                    <div class="bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg p-4 mb-4">
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Отладочная информация о данных:</h4>
                                        <pre class="text-xs bg-white dark:bg-gray-900 p-3 rounded overflow-auto">{{ json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif
                                
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
                                    <div>
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Выбранные традиции (из формы):</h4>
                                        <div class="text-gray-700 dark:text-gray-300">
                                            @if($interpretation->traditions && count($interpretation->traditions) > 0)
                                                {{ implode(', ', $interpretation->traditions) }}
                                            @else
                                                <span class="text-gray-500">Не выбраны (использован ECLECTIC по умолчанию)</span>
                                            @endif
                                        </div>
                                    </div>
                                    
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
                                    @endphp
                                    
                                    @php
                                        $hasRawRequest = isset($interpretation->raw_api_request) && $interpretation->raw_api_request !== null && $interpretation->raw_api_request !== '';
                                        $hasRawResponse = isset($interpretation->raw_api_response) && $interpretation->raw_api_response !== null && $interpretation->raw_api_response !== '';
                                    @endphp
                                    
                                    @if($hasRawRequest)
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
                                            <p class="text-xs mt-2">
                                                <strong>Отладочная информация:</strong><br>
                                                Поле существует: {{ isset($interpretation->raw_api_request) ? 'Да' : 'Нет' }}<br>
                                                Значение: {{ $interpretation->raw_api_request ?? 'NULL' }}<br>
                                                Тип: {{ gettype($interpretation->raw_api_request ?? null) }}<br>
                                                Длина: {{ isset($interpretation->raw_api_request) ? strlen($interpretation->raw_api_request) : 0 }}
                                            </p>
                                        </div>
                                    @endif
                                    
                                    @if($hasRawResponse)
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
                                            <p class="text-xs mt-2">
                                                <strong>Отладочная информация:</strong><br>
                                                Поле существует: {{ isset($interpretation->raw_api_response) ? 'Да' : 'Нет' }}<br>
                                                Значение: {{ $interpretation->raw_api_response ?? 'NULL' }}<br>
                                                Тип: {{ gettype($interpretation->raw_api_response ?? null) }}<br>
                                                Длина: {{ isset($interpretation->raw_api_response) ? strlen($interpretation->raw_api_response) : 0 }}
                                            </p>
                                        </div>
                                    @endif
                                    
                                    @if(!empty($interpretation->analysis_data))
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Распарсенный JSON (analysis_data):</h4>
                                            <details class="cursor-pointer">
                                                <summary class="text-purple-600 dark:text-purple-400 hover:underline mb-2">Показать/скрыть JSON</summary>
                                                <pre class="bg-gray-800 dark:bg-gray-950 text-green-400 p-4 rounded-lg overflow-auto text-xs" style="max-height: 500px;">{{ $formatJson($interpretation->analysis_data) }}</pre>
                                            </details>
                                        </div>
                                    @else
                                        <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded-lg">
                                            <p class="text-sm">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                <strong>Распарсенные данные не сохранены</strong> (поле analysis_data пусто)
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endauth
                @endif
                </main>

                <!-- Правая панель -->
                <aside class="space-y-6">
                    <!-- Похожие толкования -->
                    @if(isset($similarInterpretations) && $similarInterpretations->count() > 0)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700" style="min-height: 200px;">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-link mr-2 text-purple-600 dark:text-purple-400"></i>
                                Похожие толкования
                            </h2>
                            <ul class="space-y-3">
                                @foreach($similarInterpretations as $similar)
                                    @php
                                        // Загружаем связь report, если она есть
                                        if ($similar->report_id && !$similar->relationLoaded('report')) {
                                            $similar->load('report');
                                        }
                                        
                                        // Используем правильный метод SEO в зависимости от типа
                                        if ($similar->report_id && $similar->report) {
                                            // Это анализ отчета
                                            $similarSeo = \App\Helpers\SeoHelper::forReportAnalysis($similar->report, $similar);
                                            $linkUrl = route('reports.analysis', $similar->report->id);
                                        } else {
                                            // Это толкование сна
                                            $similarSeo = \App\Helpers\SeoHelper::forDreamAnalyzerResult($similar);
                                            $linkUrl = route('dream-analyzer.show', ['hash' => $similar->hash]);
                                        }
                                        
                                        $linkTitle = $similarSeo['title'] ?? 'Толкование сна';
                                        // Обрезаем title если слишком длинный
                                        if (mb_strlen($linkTitle) > 80) {
                                            $linkTitle = mb_substr($linkTitle, 0, 77) . '...';
                                        }
                                    @endphp
                                    <li>
                                        <a href="{{ $linkUrl }}" 
                                           class="block p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all group">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 line-clamp-2">
                                                {{ $linkTitle }}
                                            </div>
                                            @if(!empty($similarSeo['description']))
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                                    {{ mb_substr($similarSeo['description'], 0, 100) }}{{ mb_strlen($similarSeo['description']) > 100 ? '...' : '' }}
                                                </div>
                                            @endif
                                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                                {{ $similar->created_at->format('d.m.Y') }}
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Статистика проекта -->
                    <x-project-statistics :stats="$stats" />
                    
                    @auth
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
            
            // Копировать ссылку на страницу анализа
            function copyAnalysisLink() {
                const currentUrl = window.location.href;
                
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    // Современный способ
                    navigator.clipboard.writeText(currentUrl).then(() => {
                        showToast('✅ Ссылка на страницу скопирована!');
                    }).catch(() => {
                        // Fallback если не сработало
                        fallbackCopyAnalysisLink(currentUrl);
                    });
                } else {
                    // Fallback для старых браузеров
                    fallbackCopyAnalysisLink(currentUrl);
                }
            }
            
            // Fallback метод копирования для ссылки анализа
            function fallbackCopyAnalysisLink(url) {
                const textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                
                try {
                    document.execCommand('copy');
                    showToast('✅ Ссылка на страницу скопирована!');
                } catch (err) {
                    showToast('❌ Не удалось скопировать');
                }
                
                document.body.removeChild(textarea);
            }
        </script>
@endsection



























