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
    
    $analysisTypeNames = [
        'single' => 'Единичный',
        'integrated' => 'Интегрированный',
        'comparative' => 'Сравнительный',
    ];
@endphp

<!-- Анализ сна -->
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">{{ $result->dream_title ?? 'Анализ сна' }}</h2>
    
    @if($result->dream_type)
        <div class="mb-4 flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Тип сна:</span>
            <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                {{ $result->dream_type }}
            </span>
        </div>
    @endif
    
    @if($result->traditions && count($result->traditions) > 0)
        <div class="mb-4">
            <div class="flex items-center flex-wrap gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Традиции анализа:</span>
                @foreach($result->traditions as $tradition)
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
    
    @if($result->analysis_type)
        <div class="mb-4 flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Тип анализа:</span>
            @php
                $analysisTypeKey = strtolower($result->analysis_type);
                $analysisTypeName = $analysisTypeNames[$analysisTypeKey] ?? ucfirst($result->analysis_type);
            @endphp
            <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                {{ $analysisTypeName }}
            </span>
        </div>
    @endif
    
    @if($result->summary_insight)
        <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg border-l-4 border-purple-500">
            <h3 class="font-semibold text-purple-800 dark:text-purple-200 mb-2">Ключевая мысль</h3>
            <p class="text-gray-700 dark:text-gray-300">{{ $result->summary_insight }}</p>
        </div>
    @endif
    
    @if($result->emotional_tone)
        <div class="mb-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Эмоциональный тон</h3>
            <p class="text-gray-700 dark:text-gray-300">{{ $result->emotional_tone }}</p>
        </div>
    @endif
</div>

<!-- Детальный анализ -->
@if($result->dream_detailed)
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">Детальный анализ</h2>
        
        <div class="space-y-6">
            <div>
                <div class="text-gray-700 dark:text-gray-300 leading-relaxed" style="text-align: left;">
                    @php
                        $detailedText = $result->dream_detailed;
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
                                // Пропускаем первый заголовок "Детальный анализ"
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
            
            <!-- Key Symbols -->
            @if($result->key_symbols && count($result->key_symbols) > 0)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">КЛЮЧЕВЫЕ СИМВОЛЫ И ИХ ЗНАЧЕНИЕ</h3>
                    <div class="space-y-4">
                        @foreach($result->key_symbols as $symbol)
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
            @if($result->unified_locations && count($result->unified_locations) > 0)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Локации</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($result->unified_locations as $location)
                            <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm">
                                {{ $location }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Key Tags -->
            @if($result->key_tags && count($result->key_tags) > 0)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Теги</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($result->key_tags as $tag)
                            <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- Практические рекомендации -->
@if($result->recommendations && count($result->recommendations) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Практические рекомендации</h3>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
            @foreach($result->recommendations as $recommendation)
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
            <i class="fas fa-copy mr-2"></i>Копировать
        </button>
    </div>
</div>


