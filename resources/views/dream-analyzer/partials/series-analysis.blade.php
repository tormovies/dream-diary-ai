@php
    $seriesAnalysis = $analysis['series_analysis'] ?? [];
    $dreams = $analysis['dreams'] ?? [];
    $recommendations = $analysis['recommendations'] ?? [];
    $fullContent = $analysis['full_content'] ?? '';
    
    // Извлекаем текст до JSON блока (основная расшифровка)
    $textAnalysis = '';
    if (!empty($fullContent)) {
        // Ищем начало JSON блока (```json или ```)
        $jsonStart = strpos($fullContent, '```json');
        if ($jsonStart === false) {
            $jsonStart = strpos($fullContent, '```');
        }
        
        if ($jsonStart !== false && $jsonStart > 0) {
            // Текст до JSON блока - это основная расшифровка
            $textAnalysis = trim(substr($fullContent, 0, $jsonStart));
        } else {
            // Проверяем, не является ли весь контент JSON
            $trimmedContent = trim($fullContent);
            if (substr($trimmedContent, 0, 1) === '{' || substr($trimmedContent, 0, 1) === '[') {
                // Это JSON, не текст - очищаем
                $textAnalysis = '';
            } else {
                // Если JSON блока нет и это не JSON, весь content - это текст
                $textAnalysis = $fullContent;
            }
        }
    }
    
    // Дополнительная проверка: если textAnalysis похож на JSON, очищаем его
    if (!empty($textAnalysis)) {
        $trimmed = trim($textAnalysis);
        if (substr($trimmed, 0, 1) === '{' || substr($trimmed, 0, 1) === '[') {
            // Похоже на JSON - не показываем
            $textAnalysis = '';
        }
    }
    
    $traditionNames = [
        'freudian' => 'Фрейдистский',
        'jungian' => 'Юнгианский',
        'cognitive' => 'Когнитивный',
        'symbolic' => 'Символический',
        'shamanic' => 'Шаманистический',
        'gestalt' => 'Гештальт',
        'lucid_centered' => 'Практика ОС',
        'eclectic' => 'Комплексный',
    ];
@endphp

<!-- Заголовок серии -->
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">
        {{ $seriesAnalysis['series_title'] ?? 'Анализ серии снов' }}
    </h2>
    
    @if(isset($seriesAnalysis['traditions']) && is_array($seriesAnalysis['traditions']))
        <div class="mb-4">
            <div class="flex items-center flex-wrap gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Традиции анализа:</span>
                @foreach($seriesAnalysis['traditions'] as $tradition)
                    @php
                        $traditionKey = strtolower($tradition);
                        $traditionName = $traditionNames[$traditionKey] ?? ucfirst($tradition);
                    @endphp
                    <span class="inline-block bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                        {{ $traditionName }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    @if(isset($seriesAnalysis['overall_theme']))
        <div class="mb-4 p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg border-l-4 border-purple-500">
            <h3 class="font-semibold text-purple-800 dark:text-purple-200 mb-2">Общая тема серии</h3>
            <p class="text-gray-700 dark:text-gray-300">{{ $seriesAnalysis['overall_theme'] }}</p>
        </div>
    @endif

    @if(isset($seriesAnalysis['emotional_arc']))
        <div class="mb-4 p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg border-l-4 border-indigo-500">
            <h3 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">Эмоциональная дуга</h3>
            <p class="text-gray-700 dark:text-gray-300">{{ $seriesAnalysis['emotional_arc'] }}</p>
        </div>
    @endif

    @if(isset($seriesAnalysis['key_connections']) && is_array($seriesAnalysis['key_connections']) && count($seriesAnalysis['key_connections']) > 0)
        <div class="mb-4">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Ключевые связи</h3>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                @foreach($seriesAnalysis['key_connections'] as $connection)
                    <li>{{ $connection }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<!-- Анализ каждого сна -->
@if(!empty($dreams) && is_array($dreams))
    @foreach($dreams as $dream)
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                Сон {{ $dream['dream_number'] ?? ($loop->index + 1) }}: {{ $dream['dream_title'] ?? 'Без названия' }}
            </h3>
            @if(isset($dream['dream_type']))
                <div class="mb-4 flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Тип сна:</span>
                    <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                        {{ $dream['dream_type'] }}
                    </span>
                </div>
            @endif

            @if(isset($dream['dream_detailed']) && !empty($dream['dream_detailed']))
                @php
                    // Убираем все лишние пробелы и отступы
                    $detailedText = $dream['dream_detailed'];
                    // Убираем все пробелы и табы в начале строк
                    $lines = explode("\n", $detailedText);
                    $processedLines = [];
                    foreach ($lines as $line) {
                        // Убираем все пробелы и табы в начале и конце строки
                        $cleanedLine = trim($line);
                        // Если строка не пустая, добавляем её
                        if (!empty($cleanedLine)) {
                            $processedLines[] = $cleanedLine;
                        }
                    }
                    $detailedText = implode("\n", $processedLines);
                    $detailedText = trim($detailedText);
                @endphp
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-l-4 border-purple-500">
                    <h4 class="font-semibold text-purple-800 dark:text-purple-200 mb-3">Детальный анализ</h4>
                    <div class="text-gray-700 dark:text-gray-300 leading-relaxed" style="text-align: left; padding: 0; margin: 0;">
                        {!! nl2br(e($detailedText)) !!}
                    </div>
                </div>
            @endif

            @if(isset($dream['summary_insight']))
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border-l-4 border-blue-500">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Ключевая мысль</h4>
                    <p class="text-gray-700 dark:text-gray-300">{{ $dream['summary_insight'] }}</p>
                </div>
            @endif

            @if(isset($dream['emotional_tone']))
                <div class="mb-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Эмоциональный тон: </span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $dream['emotional_tone'] }}</span>
                </div>
            @endif

            @if(isset($dream['key_symbols']) && is_array($dream['key_symbols']) && count($dream['key_symbols']) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Ключевые символы</h4>
                    <div class="space-y-2">
                        @foreach($dream['key_symbols'] as $symbol)
                            @if(is_array($symbol) && isset($symbol['symbol']))
                                <div class="border-l-4 border-indigo-500 pl-4">
                                    <strong class="text-indigo-800 dark:text-indigo-200">{{ $symbol['symbol'] }}</strong>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $symbol['meaning'] ?? '' }}</p>
                                </div>
                            @elseif(is_string($symbol))
                                <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm mr-2 mb-2">
                                    {{ $symbol }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($dream['unified_locations']) && is_array($dream['unified_locations']) && count($dream['unified_locations']) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Локации</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($dream['unified_locations'] as $location)
                            <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm">
                                {{ $location }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($dream['key_tags']) && is_array($dream['key_tags']) && count($dream['key_tags']) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Теги</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($dream['key_tags'] as $tag)
                            <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($dream['connection_to_previous']))
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Связь с предыдущими снами</h4>
                    <p class="text-gray-700 dark:text-gray-300 text-sm">{{ $dream['connection_to_previous'] }}</p>
                </div>
            @endif
        </div>
    @endforeach
@endif

<!-- Общие рекомендации -->
@if(!empty($recommendations) && is_array($recommendations))
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Практические рекомендации</h3>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
            @foreach($recommendations as $recommendation)
                <li>{{ $recommendation }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Блок "Поделиться" (Вариант 3) -->
<div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 mb-6">
    <p class="text-gray-700 dark:text-gray-300 mb-4 text-center">
        💬 Понравился анализ? Поделитесь с друзьями!
    </p>
    <div class="flex flex-wrap justify-center gap-3">
        <button onclick="shareToVK(event)" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <i class="fab fa-vk mr-2"></i>ВКонтакте
        </button>
        <button onclick="shareToTelegram(event)" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <i class="fab fa-telegram mr-2"></i>Telegram
        </button>
        <button onclick="copyShareLink(event)" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <i class="fas fa-link mr-2"></i>Копировать
        </button>
    </div>
</div>

@auth
    @if(auth()->user()->isAdmin())
        <!-- НЕОБРАБОТАННЫЙ JSON ОТВЕТ API (только для администраторов) -->
        <div class="bg-red-50 dark:bg-red-900/30 rounded-2xl p-6 border-4 border-red-500 mb-6">
            <h2 class="text-2xl font-bold text-red-700 dark:text-red-400 mb-4">🔍 НЕОБРАБОТАННЫЙ JSON ОТВЕТ API (raw_api_response)</h2>
            
            @if(isset($interpretation) && $interpretation->raw_api_response)
                <div>
                    <h3 class="font-bold text-red-800 dark:text-red-300 mb-2">Полный необработанный ответ от API:</h3>
                    <details class="cursor-pointer">
                        <summary class="text-red-700 dark:text-red-400 hover:underline mb-2 font-semibold">Развернуть/свернуть raw_api_response</summary>
                        <pre class="bg-gray-900 text-yellow-400 p-4 rounded-lg overflow-auto text-xs max-h-[800px] border-2 border-red-500 whitespace-pre-wrap">{{ $interpretation->raw_api_response }}</pre>
                    </details>
                </div>
                
                @php
                    // Пытаемся распарсить raw_api_response как JSON
                    $rawJson = null;
                    try {
                        $rawJson = json_decode($interpretation->raw_api_response, true);
                    } catch (\Exception $e) {
                        // Не JSON или ошибка парсинга
                    }
                @endphp
                
                @if($rawJson !== null)
                    <div class="mt-4">
                        <h3 class="font-bold text-red-800 dark:text-red-300 mb-2">Распарсенный raw_api_response (JSON):</h3>
                        <details class="cursor-pointer">
                            <summary class="text-red-700 dark:text-red-400 hover:underline mb-2 font-semibold">Развернуть/свернуть распарсенный JSON</summary>
                            <pre class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-auto text-xs max-h-[600px] border-2 border-red-500">{{ json_encode($rawJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    </div>
                    
                    @if(isset($rawJson['choices']) && is_array($rawJson['choices']) && isset($rawJson['choices'][0]['message']['content']))
                        <div class="mt-4">
                            <h3 class="font-bold text-red-800 dark:text-red-300 mb-2">Содержимое ответа (choices[0].message.content) - ПОЛНЫЙ ТЕКСТ:</h3>
                            <details class="cursor-pointer">
                                <summary class="text-red-700 dark:text-red-400 hover:underline mb-2 font-semibold">Развернуть/свернуть content ({{ strlen($rawJson['choices'][0]['message']['content']) }} символов)</summary>
                                <pre class="bg-gray-900 text-blue-400 p-4 rounded-lg overflow-auto text-xs max-h-[800px] border-2 border-red-500 whitespace-pre-wrap">{{ $rawJson['choices'][0]['message']['content'] }}</pre>
                            </details>
                        </div>
                    @endif
                    
                    @if(isset($analysis['full_content']))
                        <div class="mt-4">
                            <h3 class="font-bold text-red-800 dark:text-red-300 mb-2">full_content из analysis_data (сохраненный полный content):</h3>
                            <details class="cursor-pointer">
                                <summary class="text-red-700 dark:text-red-400 hover:underline mb-2 font-semibold">Развернуть/свернуть full_content ({{ strlen($analysis['full_content']) }} символов)</summary>
                                <pre class="bg-gray-900 text-cyan-400 p-4 rounded-lg overflow-auto text-xs max-h-[800px] border-2 border-red-500 whitespace-pre-wrap">{{ $analysis['full_content'] }}</pre>
                            </details>
                        </div>
                    @endif
                @endif
            @else
                <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 text-yellow-700 dark:text-yellow-300 p-4 rounded-lg">
                    <p><strong>Внимание:</strong> raw_api_response отсутствует или пуст.</p>
                </div>
            @endif
        </div>
    @endif
@endauth






















