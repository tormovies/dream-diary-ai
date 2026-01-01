@php
    // Извлекаем данные из analysis_data
    $analysisData = $result->analysis_data ?? [];
    $dreamMetadata = $analysisData['dream_metadata'] ?? [];
    $coreAnalysis = $analysisData['core_analysis'] ?? [];
    $symbolicElements = $analysisData['symbolic_elements'] ?? [];
    $practicalGuidance = $analysisData['practical_guidance'] ?? [];
    $recommendations = $analysisData['recommendations'] ?? [];
    $tagsAndCategories = $analysisData['tags_and_categories'] ?? [];
    $traditionSpecific = $analysisData['tradition_specific'] ?? [];
    
    // Функция для заглавной буквы с поддержкой UTF-8
    if (!function_exists('mb_ucfirst')) {
        function mb_ucfirst($string, $encoding = 'UTF-8') {
            $firstChar = mb_substr($string, 0, 1, $encoding);
            $rest = mb_substr($string, 1, null, $encoding);
            return mb_strtoupper($firstChar, $encoding) . $rest;
        }
    }
@endphp

<!-- Новая система анализа -->
<div class="space-y-4">
    <!-- Dream Metadata Block -->
    @if(!empty($dreamMetadata))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">
                {{ $dreamMetadata['dream_title'] ?? 'Анализ сна' }}
            </h2>
            
            <div class="flex flex-wrap items-center gap-3 mb-3">
                @if(isset($dreamMetadata['dream_type']))
                    <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                        {{ mb_ucfirst(mb_strtolower($dreamMetadata['dream_type'])) }}
                    </span>
                @endif
                @if(!empty($traditionSpecific['tradition_name']))
                    <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                        {{ $traditionSpecific['tradition_name'] }}
                    </span>
                @endif
                @if(isset($dreamMetadata['emotional_tone']))
                    <span class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 px-3 py-1 rounded-full text-sm">
                        {{ mb_ucfirst(mb_strtolower($dreamMetadata['emotional_tone'])) }}
                    </span>
                @endif
                @if(isset($dreamMetadata['recall_quality']))
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Качество воспоминания:</strong> {{ number_format($dreamMetadata['recall_quality'] * 100, 0) }}%
                    </span>
                @endif
            </div>
            
            @if(isset($dreamMetadata['summary_insight']) && !empty(trim($dreamMetadata['summary_insight'])))
                <div class="mb-3 p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg border-l-4 border-purple-500">
                    <h3 class="font-semibold text-purple-800 dark:text-purple-200 mb-1 text-sm">Ключевая мысль</h3>
                    <p class="text-gray-700 dark:text-gray-300 text-sm">{{ $dreamMetadata['summary_insight'] }}</p>
                </div>
            @endif
            
            @if(isset($dreamMetadata['context_summary']) && !empty(trim($dreamMetadata['context_summary'])))
                <div class="mb-3">
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Контекст:</span>
                    <span class="text-gray-700 dark:text-gray-300 text-sm ml-1">{{ $dreamMetadata['context_summary'] }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Детальный анализ -->
    @if(isset($dreamMetadata['dream_detailed']) && !empty(trim($dreamMetadata['dream_detailed'])))
        @php
            $detailedText = trim($dreamMetadata['dream_detailed']);
            // Убираем множественные пробелы и переносы строк
            $detailedText = preg_replace('/\s+/', ' ', $detailedText);
            // Убираем пробелы в начале и конце каждого предложения (после точки, восклицательного знака и т.д.)
            $detailedText = preg_replace('/\s+([.!?])\s+/', '$1 ', $detailedText);
            $detailedText = trim($detailedText);
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Детальный анализ</h2>
            <div class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg">
                {{ $detailedText }}
            </div>
        </div>
    @endif

    <!-- Core Analysis -->
    @if(!empty($coreAnalysis))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Основной анализ</h2>
            
            @php
                $emotionalBreakdown = $coreAnalysis['emotional_breakdown'] ?? [];
                $hasEmotionalData = !empty($emotionalBreakdown['primary_emotion']) || 
                                   !empty($emotionalBreakdown['emotional_triggers']) || 
                                   !empty($emotionalBreakdown['secondary_emotions']) ||
                                   !empty($emotionalBreakdown['emotional_trajectory']);
            @endphp
            
            @if($hasEmotionalData)
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Эмоциональная структура</h3>
                    
                    <div class="space-y-2">
                        @if(!empty($emotionalBreakdown['primary_emotion']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Основная эмоция:</span>
                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full text-xs ml-2">
                                    {{ $emotionalBreakdown['primary_emotion'] }}
                                </span>
                            </div>
                        @endif
                        
                        @if(!empty($emotionalBreakdown['secondary_emotions']) && is_array($emotionalBreakdown['secondary_emotions']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Вторичные:</span>
                                @foreach($emotionalBreakdown['secondary_emotions'] as $emotion)
                                    @if(!empty(trim($emotion)))
                                        <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full text-xs ml-1">
                                            {{ $emotion }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        
                        @if(!empty($emotionalBreakdown['emotional_triggers']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Триггеры:</span>
                                @if(is_array($emotionalBreakdown['emotional_triggers']))
                                    <span class="text-sm text-gray-700 dark:text-gray-300 ml-2">
                                        {{ implode(', ', $emotionalBreakdown['emotional_triggers']) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-700 dark:text-gray-300 ml-2">{{ $emotionalBreakdown['emotional_triggers'] }}</span>
                                @endif
                            </div>
                        @endif
                        
                        @if(!empty($emotionalBreakdown['emotional_trajectory']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Траектория:</span>
                                <span class="text-sm text-gray-700 dark:text-gray-300 ml-2">{{ $emotionalBreakdown['emotional_trajectory'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            @if(!empty($coreAnalysis['archetypal_patterns']) && is_array($coreAnalysis['archetypal_patterns']) && count($coreAnalysis['archetypal_patterns']) > 0)
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Архетипические паттерны</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($coreAnalysis['archetypal_patterns'] as $pattern)
                            @if(!empty(trim($pattern)))
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-full text-sm border border-gray-300 dark:border-gray-600">
                                    {{ $pattern }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            @if(!empty($coreAnalysis['key_insights']) && is_array($coreAnalysis['key_insights']))
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Ключевые инсайты</h3>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        @foreach($coreAnalysis['key_insights'] as $insight)
                            @if(!empty(trim($insight)))
                                <li class="text-sm text-gray-700 dark:text-gray-300">{{ $insight }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(!empty($coreAnalysis['life_context_connections']))
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Контекст жизни</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $coreAnalysis['life_context_connections'] }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Symbolic Elements -->
    @if(!empty($symbolicElements))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Символические элементы</h2>
            
            @foreach(['objects' => 'Объекты', 'locations' => 'Локации', 'characters' => 'Персонажи', 'actions' => 'Действия'] as $key => $title)
                @if(isset($symbolicElements[$key]) && is_array($symbolicElements[$key]) && count($symbolicElements[$key]) > 0)
                    <div class="mb-4 last:mb-0">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ $title }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($symbolicElements[$key] as $element)
                                @if(is_array($element) && !empty($element['element']))
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 flex-1">
                                                {{ $element['element'] }}
                                            </h4>
                                            @if(!empty($element['emotional_charge']))
                                                <span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                                    {{ $element['emotional_charge'] }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if(!empty($element['symbolic_meaning_primary']))
                                            <p class="text-xs text-gray-700 dark:text-gray-300 mb-1">{{ $element['symbolic_meaning_primary'] }}</p>
                                        @endif
                                        
                                        @if(!empty($element['symbolic_meaning_secondary']))
                                            <p class="text-xs text-gray-700 dark:text-gray-300 italic">{{ $element['symbolic_meaning_secondary'] }}</p>
                                        @endif
                                        
                                        @if(!empty($element['symbolic_meaning']) && empty($element['symbolic_meaning_primary']))
                                            <p class="text-xs text-gray-700 dark:text-gray-300">{{ $element['symbolic_meaning'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Tags and Categories -->
    @if(!empty($tagsAndCategories))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Теги и категории</h2>
            
            <div class="space-y-2">
                @foreach(['primary_tags' => 'Основные', 'emotional_tags' => 'Эмоциональные', 'theme_tags' => 'Тематические', 'skill_tags' => 'Навыки'] as $key => $title)
                    @if(isset($tagsAndCategories[$key]) && is_array($tagsAndCategories[$key]) && count($tagsAndCategories[$key]) > 0)
                        <div>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 mr-2">{{ ucfirst(mb_strtolower($title)) }}:</span>
                            <div class="inline-flex flex-wrap gap-1.5">
                                @foreach($tagsAndCategories[$key] as $tag)
                                    @if(!empty(trim($tag)))
                                        @php
                                            $tagDisplay = str_replace('_', ' ', $tag);
                                            $tagDisplay = mb_ucfirst(mb_strtolower($tagDisplay));
                                        @endphp
                                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 px-2 py-0.5 rounded-full text-xs">
                                            {{ $tagDisplay }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tradition Specific -->
    @if(!empty($traditionSpecific))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">
                Специфика традиции
            </h2>
            
            @if(!empty($traditionSpecific['lucidity_index']))
                @php $lucidityIndex = $traditionSpecific['lucidity_index']; @endphp
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            Индекс люцидности: {{ isset($lucidityIndex['индекс']) ? number_format($lucidityIndex['индекс'], 2) : ($lucidityIndex['индекс'] ?? 'N/A') }}
                        </span>
                    </div>
                    @if(!empty($lucidityIndex['расчёт']))
                        <div class="mb-1">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Расчёт:</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $lucidityIndex['расчёт'] }}</p>
                        </div>
                    @endif
                    @if(!empty($lucidityIndex['интерпретация']))
                        <div>
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Интерпретация:</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $lucidityIndex['интерпретация'] }}</p>
                        </div>
                    @endif
                </div>
            @endif
            
            @if(!empty($traditionSpecific['analysis']) && is_array($traditionSpecific['analysis']))
                <div class="space-y-4">
                    @foreach($traditionSpecific['analysis'] as $analysisKey => $analysisValue)
                        @if(is_array($analysisValue) || (is_string($analysisValue) && !empty(trim($analysisValue))))
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0 last:pb-0">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    {{ mb_ucfirst(mb_strtolower(str_replace('_', ' ', $analysisKey))) }}
                                </h3>
                                
                                @if(is_array($analysisValue))
                                    @if(isset($analysisValue[0]) && is_numeric(array_keys($analysisValue)[0]))
                                        {{-- Numeric array --}}
                                        @if(is_array($analysisValue[0]))
                                            {{-- Array of objects --}}
                                            <div class="space-y-2">
                                                @foreach($analysisValue as $item)
                                                    @if(is_array($item))
                                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded text-sm">
                                                            @foreach($item as $itemKey => $itemValue)
                                                                @if(!empty($itemValue))
                                                                    <div class="mb-1 last:mb-0">
                                                                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ mb_ucfirst(mb_strtolower(str_replace('_', ' ', $itemKey))) }}:</span>
                                                                        <span class="text-gray-600 dark:text-gray-400 ml-1">
                                                                            {{ is_array($itemValue) ? json_encode($itemValue, JSON_UNESCAPED_UNICODE) : $itemValue }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="text-sm text-gray-700 dark:text-gray-300">{{ $item }}</div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            {{-- Array of strings --}}
                                            <ul class="list-disc list-inside space-y-1 ml-2">
                                                @foreach($analysisValue as $item)
                                                    <li class="text-sm text-gray-700 dark:text-gray-300">{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @else
                                        {{-- Associative array --}}
                                        @php
                                            // Ключи, которые нужно отображать в одну строку
                                            $inlineKeys = ['эффективность_ПР', 'точки_входа_выхода', 'энергетические_состояния', 'фаза_внетелесных_ощущений'];
                                            $isInline = in_array($analysisKey, $inlineKeys);
                                        @endphp
                                        <div class="{{ $isInline ? 'space-y-1' : 'space-y-2' }}">
                                            @foreach($analysisValue as $subKey => $subValue)
                                                @if(!empty($subValue))
                                                    @if($isInline)
                                                        <div class="text-sm">
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ mb_ucfirst(mb_strtolower(str_replace('_', ' ', $subKey))) }}:</span>
                                                            <span class="text-gray-600 dark:text-gray-400 ml-1">
                                                                @if(is_array($subValue))
                                                                    {{ json_encode($subValue, JSON_UNESCAPED_UNICODE) }}
                                                                @else
                                                                    {{ $subValue }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @else
                                                        <div>
                                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ mb_ucfirst(mb_strtolower(str_replace('_', ' ', $subKey))) }}:</span>
                                                            @if(is_array($subValue))
                                                                @if(isset($subValue[0]) && is_numeric(array_keys($subValue)[0]))
                                                                    <ul class="list-disc list-inside space-y-1 ml-2 mt-1">
                                                                        @foreach($subValue as $item)
                                                                            <li class="text-sm text-gray-700 dark:text-gray-300">{{ $item }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    <div class="text-sm text-gray-700 dark:text-gray-300 mt-1 ml-2">
                                                                        {{ json_encode($subValue, JSON_UNESCAPED_UNICODE) }}
                                                                    </div>
                                                                @endif
                                                            @else
                                                                @php
                                                                    // Проверяем, является ли это пошаговым планом с нумерованным списком
                                                                    $isStepPlan = ($subKey === 'пошаговый_план_для_повтора' || strpos($subKey, 'пошаговый') !== false);
                                                                    if ($isStepPlan && preg_match('/\d+\.\s+[^0-9]+/', $subValue)) {
                                                                        // Парсим нумерованный список
                                                                        if (preg_match_all('/\d+\.\s*([^0-9]+?)(?=\d+\.|$)/', $subValue, $stepMatches)) {
                                                                            $stepItems = array_map('trim', $stepMatches[1]);
                                                                        } else {
                                                                            $stepItems = [];
                                                                        }
                                                                    } else {
                                                                        $stepItems = [];
                                                                    }
                                                                @endphp
                                                                @if(!empty($stepItems) && count($stepItems) > 1)
                                                                    <ol class="list-decimal list-inside space-y-1 ml-2 mt-1 text-sm text-gray-700 dark:text-gray-300">
                                                                        @foreach($stepItems as $stepItem)
                                                                            @if(!empty(trim($stepItem)))
                                                                                <li>{{ trim($stepItem) }}</li>
                                                                            @endif
                                                                        @endforeach
                                                                    </ol>
                                                                @else
                                                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 ml-2">{{ $subValue }}</p>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    {{-- String value --}}
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $analysisValue }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Practical Guidance -->
    @if(!empty($practicalGuidance))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Практические рекомендации</h2>
            
            @php
                $osScenarios = $practicalGuidance['os_scenarios'] ?? [];
                $therapeuticApproaches = $practicalGuidance['therapeutic_approaches'] ?? [];
                $immediateActions = $practicalGuidance['immediate_actions'] ?? [];
            @endphp
            
            @if(!empty($osScenarios) && is_array($osScenarios) && count($osScenarios) > 0)
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Сценарии для осознанных сновидений</h3>
                    <div class="space-y-2">
                        @foreach($osScenarios as $scenario)
                            @if(is_array($scenario))
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                    @if(!empty($scenario['сценарий']))
                                        <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-1">
                                            {{ mb_ucfirst(mb_strtolower($scenario['сценарий'])) }}
                                        </h4>
                                    @endif
                                    @if(!empty($scenario['действия']))
                                        @php
                                            $actions = $scenario['действия'];
                                            // Парсим действия: ищем паттерн "1. текст 2. текст" и разбиваем на список
                                            if (preg_match_all('/\d+\.\s*([^0-9]+?)(?=\d+\.|$)/', $actions, $matches)) {
                                                $actionItems = array_map('trim', $matches[1]);
                                            } else {
                                                // Если не найдено, просто разбиваем по переносу строки или точке с пробелом
                                                $actionItems = preg_split('/\n|\d+\.\s+/', $actions, -1, PREG_SPLIT_NO_EMPTY);
                                                $actionItems = array_map('trim', $actionItems);
                                                $actionItems = array_filter($actionItems, function($item) {
                                                    return !empty($item) && strlen($item) > 3;
                                                });
                                            }
                                        @endphp
                                        @if(!empty($actionItems) && count($actionItems) > 1)
                                            <ol class="list-decimal list-inside space-y-1 ml-2 text-xs text-gray-700 dark:text-gray-300">
                                                @foreach($actionItems as $action)
                                                    @if(!empty(trim($action)))
                                                        <li>{{ trim($action) }}</li>
                                                    @endif
                                                @endforeach
                                            </ol>
                                        @else
                                            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $actions }}</p>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            @if(!empty($therapeuticApproaches) && is_array($therapeuticApproaches) && count($therapeuticApproaches) > 0)
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Терапевтические подходы</h3>
                    <div class="space-y-2">
                        @foreach($therapeuticApproaches as $approach)
                            @if(is_array($approach))
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                    @if(!empty($approach['approach']) || !empty($approach['подход']))
                                        <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-1">
                                            {{ $approach['approach'] ?? $approach['подход'] ?? 'Подход' }}
                                        </h4>
                                    @endif
                                    @if(!empty($approach['application']) || !empty($approach['применение']))
                                        <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">{{ $approach['application'] ?? $approach['применение'] }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            @if(!empty($immediateActions) && is_array($immediateActions) && count($immediateActions) > 0)
                @php
                    $firstAction = reset($immediateActions);
                    $isStringArray = is_string($firstAction);
                @endphp
                
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Немедленные действия</h3>
                    <div class="space-y-1">
                        @if($isStringArray)
                            @foreach($immediateActions as $action)
                                @if(!empty(trim($action)))
                                    <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        <p class="text-xs text-gray-700 dark:text-gray-300">{{ $action }}</p>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            @foreach($immediateActions as $action)
                                @if(is_array($action))
                                    <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        @if(!empty($action['action']) || !empty($action['действие']))
                                            <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-1">
                                                {{ $action['action'] ?? $action['действие'] ?? 'Действие' }}
                                            </h4>
                                        @endif
                                        @if(!empty($action['description']) || !empty($action['описание']))
                                            <p class="text-xs text-gray-700 dark:text-gray-300">{{ $action['description'] ?? $action['описание'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Recommendations -->
    @if(!empty($recommendations))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Рекомендации</h2>
            
            @if(!empty($recommendations['warnings']) && is_array($recommendations['warnings']))
                <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <h3 class="text-base font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Предупреждения</h3>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        @foreach($recommendations['warnings'] as $warning)
                            @if(!empty(trim($warning)))
                                <li class="text-sm text-gray-700 dark:text-gray-300">{{ $warning }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @foreach(['short_term' => 'Краткосрочные', 'medium_term' => 'Среднесрочные', 'long_term' => 'Долгосрочные'] as $key => $title)
                @if(isset($recommendations[$key]) && is_array($recommendations[$key]) && count($recommendations[$key]) > 0)
                    <div class="mb-3 last:mb-0">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ $title }}</h3>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            @foreach($recommendations[$key] as $rec)
                                @if(!empty(trim($rec)))
                                    <li class="text-sm text-gray-700 dark:text-gray-300">{{ $rec }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Блок "Поделиться" -->
    <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
        <p class="text-gray-700 dark:text-gray-300 mb-4 text-center text-sm">
            💬 Понравился анализ? Поделитесь с друзьями!
        </p>
        <div class="flex flex-wrap justify-center gap-3">
            <button onclick="shareToVK(event)" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-sm">
                <i class="fab fa-vk mr-2"></i>ВКонтакте
            </button>
            <button onclick="shareToTelegram(event)" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-sm">
                <i class="fab fa-telegram mr-2"></i>Telegram
            </button>
            <button onclick="copyShareLink(event)" class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 transition-colors py-2 px-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-sm">
                <i class="fas fa-link mr-2"></i>Копировать
            </button>
        </div>
    </div>

    <!-- Debug: JSON dump для админов -->
    @if(auth()->check() && auth()->user()->isAdmin())
        <details class="bg-gray-100 dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-700">
            <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-300 mb-4">
                [Admin] Полный JSON ответ
            </summary>
            <pre class="text-xs overflow-auto bg-white dark:bg-gray-800 p-4 rounded mt-4">{{ json_encode($analysisData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    @endif
</div>
