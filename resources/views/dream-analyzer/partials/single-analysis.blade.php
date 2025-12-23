@php
    /**
     * СТРУКТУРА dream_analysis (JSON ответ от API для единичных снов):
     * 
     * dream_analysis: {
     *   - traditions: array[string] - Массив традиций анализа (freudian, jungian, cognitive, symbolic, shamanic, gestalt, eclectic)
     *     Используется для: Отображения бейджей с традициями анализа
     * 
     *   - analysis_type: string - Тип анализа (single/integrated/comparative)
     *     Используется для: Отображения типа анализа
     * 
     *   - dream_title: string - Название сна, предложенное на основе основной темы
     *     Используется для: Заголовка блока "Анализ сна"
     * 
     *   - dream_detailed: string - Детальный текстовый анализ сновидения
     *     Используется для: Блока "Детальный анализ" с форматированием (заголовки ###, жирный текст **, списки)
     * 
     *   - dream_type: string - Тип сна (архетипический/бытовой/осознанный/кошмар/пророческий/повторяющийся/исследовательский и т.д.)
     *     Используется для: Отображения бейджа с типом сна
     * 
     *   - key_symbols: array[object] - Массив ключевых символов из сна
     *     Структура: [{symbol: string, meaning: string}, ...]
     *     Используется для: Блока "Ключевые символы и их значение"
     * 
     *   - unified_locations: array[string] - Массив стандартизированных названий локаций (Дом, Метро, Поле боя, Офис, Школа, Лес и т.д.)
     *     Используется для: Отображения локаций в виде бейджей
     * 
     *   - key_tags: array[string] - Массив тегов (например: интеграция, сила, границы, творчество)
     *     Используется для: Отображения тегов в виде бейджей
     * 
     *   - summary_insight: string - Одна ключевая мысль из сна
     *     Используется для: Блока "Ключевая мысль" в карточке анализа
     * 
     *   - emotional_tone: string - Эмоциональный тон сна (нейтральный, тревожный, радостный, исследовательский и т.д.)
     *     Используется для: Отображения эмоционального тона в карточке анализа
     * 
     *   - tradition: array[string]|string - Традиция анализа (может быть массивом или строкой)
     *     Используется для: Отображения традиции (дублирует или дополняет traditions)
     * }
     */
    
    $dreamAnalysis = $analysis['dream_analysis'] ?? [];
    $fullContent = $analysis['full_content'] ?? [];
    $recommendations = $analysis['recommendations'] ?? [];
    
    // Извлекаем данные из новой структуры dream_analysis
    $keySymbols = $dreamAnalysis['key_symbols'] ?? [];
    $unifiedLocations = $dreamAnalysis['unified_locations'] ?? [];
    $keyTags = $dreamAnalysis['key_tags'] ?? [];
    
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
            // Если JSON блока нет, весь content - это текст
            $textAnalysis = $fullContent;
        }
    }
    
    // Обрабатываем текст для читаемости и форматирования
    // НО сохраняем структуру для нумерованных списков
    if (!empty($textAnalysis)) {
        // Убираем множественные переносы строк (более 2 подряд)
        $textAnalysis = preg_replace('/\n{3,}/', "\n\n", $textAnalysis);
        
        // Убираем пробелы в начале строк (но сохраняем структуру списков)
        $lines = explode("\n", $textAnalysis);
        $processedLines = [];
        foreach ($lines as $line) {
            // Если строка начинается с номера списка, не трогаем пробелы после номера
            if (preg_match('/^\d+\.\s{1,2}/', $line)) {
                $processedLines[] = $line;
            } else {
                $processedLines[] = trim($line);
            }
        }
        $textAnalysis = implode("\n", $processedLines);
        
        // Убираем пустые строки в начале и конце
        $textAnalysis = trim($textAnalysis);
    }
    
    // Извлекаем текст расшифровки из raw_api_response или full_content для детального анализа
    $textAnalysisForDetail = '';
    if (isset($interpretation) && $interpretation->raw_api_response) {
        $rawJson = json_decode($interpretation->raw_api_response, true);
        $contentFromRaw = $rawJson['choices'][0]['message']['content'] ?? '';
        
        if (!empty($contentFromRaw)) {
            // Ищем начало JSON блока
            $jsonStart = strpos($contentFromRaw, '```json');
            if ($jsonStart === false) {
                $jsonStart = strpos($contentFromRaw, '```');
            }
            
            if ($jsonStart !== false && $jsonStart > 0) {
                $textAnalysisForDetail = trim(substr($contentFromRaw, 0, $jsonStart));
            } else {
                $textAnalysisForDetail = $contentFromRaw;
            }
        }
    } elseif (!empty($fullContent)) {
        $jsonStart = strpos($fullContent, '```json');
        if ($jsonStart === false) {
            $jsonStart = strpos($fullContent, '```');
        }
        
        if ($jsonStart !== false && $jsonStart > 0) {
            $textAnalysisForDetail = trim(substr($fullContent, 0, $jsonStart));
        } else {
            $textAnalysisForDetail = $fullContent;
        }
    }
    
    // Обрабатываем текст для форматирования
    if (!empty($textAnalysisForDetail)) {
        $textAnalysisForDetail = preg_replace('/\n{3,}/', "\n\n", $textAnalysisForDetail);
        $textAnalysisForDetail = trim($textAnalysisForDetail);
    }
@endphp

<!-- Анализ сна -->
@if(!empty($dreamAnalysis))
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">
            {{ $dreamAnalysis['dream_title'] ?? 'Анализ сна' }}
        </h2>
        @if(isset($dreamAnalysis['dream_type']))
            <div class="mb-4 flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Тип сна:</span>
                <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                    {{ $dreamAnalysis['dream_type'] }}
                </span>
            </div>
        @endif
        
        @if(isset($dreamAnalysis['traditions']) && is_array($dreamAnalysis['traditions']))
            <div class="mb-4">
                <div class="flex items-center flex-wrap gap-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Традиции анализа:</span>
                    @php
                        $traditionNames = [
                            'freudian' => 'Фрейдистский',
                            'jungian' => 'Юнгианский',
                            'cognitive' => 'Когнитивный',
                            'symbolic' => 'Символический',
                            'shamanic' => 'Шаманистический',
                            'gestalt' => 'Гештальт',
                            'eclectic' => 'Комплексный',
                        ];
                    @endphp
                    @foreach($dreamAnalysis['traditions'] as $tradition)
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
        
        @if(isset($dreamAnalysis['analysis_type']))
            <div class="mb-4 flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Тип анализа:</span>
                @php
                    $analysisTypeNames = [
                        'single' => 'Единичный',
                        'integrated' => 'Интегрированный',
                        'comparative' => 'Сравнительный',
                    ];
                    $analysisTypeKey = strtolower($dreamAnalysis['analysis_type']);
                    $analysisTypeName = $analysisTypeNames[$analysisTypeKey] ?? ucfirst($dreamAnalysis['analysis_type']);
                @endphp
                <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                    {{ $analysisTypeName }}
                </span>
            </div>
        @endif
        
        @if(isset($dreamAnalysis['tradition']) && (is_array($dreamAnalysis['tradition']) || is_string($dreamAnalysis['tradition'])))
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Традиция:</h3>
                <div class="flex flex-wrap gap-2">
                    @php
                        $traditionNames = [
                            'freudian' => 'Фрейдистский',
                            'jungian' => 'Юнгианский',
                            'cognitive' => 'Когнитивный',
                            'symbolic' => 'Символический',
                            'shamanic' => 'Шаманистический',
                            'gestalt' => 'Гештальт',
                            'eclectic' => 'Комплексный',
                        ];
                        $traditionValue = is_array($dreamAnalysis['tradition']) ? $dreamAnalysis['tradition'] : [$dreamAnalysis['tradition']];
                    @endphp
                    @foreach($traditionValue as $tradition)
                        @php
                            $traditionKey = strtolower($tradition);
                            $traditionName = $traditionNames[$traditionKey] ?? ucfirst($tradition);
                        @endphp
                        <span class="inline-block bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm">
                            {{ $traditionName }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if(isset($dreamAnalysis['summary_insight']))
            <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg border-l-4 border-purple-500">
                <h3 class="font-semibold text-purple-800 dark:text-purple-200 mb-2">Ключевая мысль</h3>
                <p class="text-gray-700 dark:text-gray-300">{{ $dreamAnalysis['summary_insight'] }}</p>
            </div>
        @endif
        
        @if(isset($dreamAnalysis['emotional_tone']))
            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Эмоциональный тон</h3>
                <p class="text-gray-700 dark:text-gray-300">{{ $dreamAnalysis['emotional_tone'] }}</p>
            </div>
        @endif
    </div>
@endif

<!-- Детальный анализ (структурированные данные из JSON в виде форматированного текста) -->
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">Детальный анализ</h2>
    
    <div class="space-y-6">
        <!-- Dream Detailed (детальный анализ из JSON) -->
        @if(isset($dreamAnalysis['dream_detailed']) && !empty($dreamAnalysis['dream_detailed']))
            <div>
                <div class="text-gray-700 dark:text-gray-300 leading-relaxed" style="text-align: left;">
                    @php
                        $detailedText = $dreamAnalysis['dream_detailed'];
                        // Обрабатываем форматирование (заголовки ###, жирный текст **, списки)
                        $parts = preg_split('/(\n\n+)/', $detailedText, -1, PREG_SPLIT_DELIM_CAPTURE);
                        $formattedParts = [];
                        $firstHeadingSkipped = false;
                        
                        foreach ($parts as $part) {
                            $part = trim($part);
                            if (empty($part)) {
                                continue;
                            }
                            
                            // Проверяем, является ли это заголовком (начинается с ###)
                            if (preg_match('/^###\s+(.+)$/m', $part, $matches)) {
                                $title = trim($matches[1]);
                                // Пропускаем первый заголовок "Детальный анализ" (в любом регистре), так как уже есть h2
                                if (!$firstHeadingSkipped && mb_stripos($title, 'Детальный анализ') !== false) {
                                    $firstHeadingSkipped = true;
                                    continue;
                                }
                                $formattedParts[] = ['type' => 'heading', 'content' => $title];
                            } 
                            // Проверяем, является ли это нумерованным списком
                            elseif (preg_match('/^\d+\.\s{1,2}/m', $part)) {
                                preg_match_all('/(\d+\.\s{1,2})(.+?)(?=\d+\.\s{1,2}|$)/s', $part, $matches, PREG_SET_ORDER);
                                
                                $listItems = [];
                                
                                if (!empty($matches)) {
                                    foreach ($matches as $match) {
                                        $item = trim($match[2]);
                                        if (empty($item)) {
                                            continue;
                                        }
                                        
                                        $item = preg_replace('/\s+/', ' ', $item);
                                        $item = trim($item);
                                        $item = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $item);
                                        
                                        if (!empty($item)) {
                                            $listItems[] = $item;
                                        }
                                    }
                                }
                                
                                if (!empty($listItems) && count($listItems) > 0) {
                                    $formattedParts[] = ['type' => 'list', 'content' => $listItems];
                                } else {
                                    $formattedText = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $part);
                                    $formattedParts[] = ['type' => 'paragraph', 'content' => $formattedText];
                                }
                            } else {
                                $formattedText = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $part);
                                $formattedParts[] = ['type' => 'paragraph', 'content' => $formattedText];
                            }
                        }
                    @endphp
                    
                    @foreach($formattedParts as $part)
                        @if($part['type'] === 'heading')
                            <h3 class="text-xl font-bold text-purple-600 dark:text-purple-400 mt-6 mb-3 first:mt-0">{{ $part['content'] }}</h3>
                        @elseif($part['type'] === 'list')
                            <ol class="list-decimal list-inside mb-4 space-y-2 ml-4">
                                @foreach($part['content'] as $item)
                                    <li class="text-gray-700 dark:text-gray-300">{!! $item !!}</li>
                                @endforeach
                            </ol>
                        @else
                            <p class="mb-4 last:mb-0">{!! $part['content'] !!}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Key Symbols -->
        @if(!empty($keySymbols) && is_array($keySymbols))
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">КЛЮЧЕВЫЕ СИМВОЛЫ И ИХ ЗНАЧЕНИЕ</h3>
                <div class="space-y-4">
                    @foreach($keySymbols as $symbol)
                        <div class="border-l-4 border-indigo-500 pl-4">
                            <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">{{ $symbol['symbol'] ?? 'Символ' }}</h4>
                            
                            @if(isset($symbol['meaning']))
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $symbol['meaning'] }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Unified Locations -->
        @if(!empty($unifiedLocations) && is_array($unifiedLocations))
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Локации</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($unifiedLocations as $location)
                        <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm">
                            {{ $location }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Key Tags -->
        @if(!empty($keyTags) && is_array($keyTags))
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Теги</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($keyTags as $tag)
                        <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Практические рекомендации -->
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


