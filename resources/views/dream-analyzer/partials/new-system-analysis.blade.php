@php
    use App\View\Helpers\DreamAnalysisViewHelper;
    
    // Извлекаем данные из analysis_data
    $analysisData = $result->analysis_data ?? [];
    $dreamMetadata = $analysisData['dream_metadata'] ?? [];
    $coreAnalysis = $analysisData['core_analysis'] ?? [];
    $symbolicElements = $analysisData['symbolic_elements'] ?? [];
    $practicalGuidance = $analysisData['practical_guidance'] ?? [];
    $recommendations = $analysisData['recommendations'] ?? [];
    $tagsAndCategories = $analysisData['tags_and_categories'] ?? [];
    $traditionSpecific = $analysisData['tradition_specific'] ?? [];
    // Проверяем lucidity_analysis на разных уровнях
    $lucidityAnalysis = $analysisData['lucidity_analysis'] ?? $coreAnalysis['lucidity_analysis'] ?? $traditionSpecific['lucidity_analysis'] ?? null;
    
    // Определяем традицию
    $tradition = DreamAnalysisViewHelper::detectTradition($result, $analysisData);
    
    // Функция для заглавной буквы с поддержкой UTF-8
    if (!function_exists('mb_ucfirst')) {
        function mb_ucfirst($string, $encoding = 'UTF-8') {
            $firstChar = mb_substr($string, 0, 1, $encoding);
            $rest = mb_substr($string, 1, null, $encoding);
            return mb_strtoupper($firstChar, $encoding) . $rest;
        }
    }
    
    // Helper функция для безопасного отображения
    $safeDisplay = function($value, $fieldName = '') {
        return DreamAnalysisViewHelper::safeDisplay($value, $fieldName);
    };
    
    // Функция для перевода английских названий полей на русский
    $translateField = function($fieldName) {
        $translations = [
            'primary_emotion' => 'Основная эмоция',
            'secondary_emotions' => 'Вторичные эмоции',
            'emotional_triggers' => 'Эмоциональные триггеры',
            'emotional_trajectory' => 'Эмоциональная траектория',
            'pattern_name' => 'Название паттерна',
            'description' => 'Описание',
            'strength' => 'Сила',
            'function' => 'Функция',
            'manifestation' => 'Проявление',
            'element' => 'Элемент',
            'emotional_charge' => 'Эмоциональный заряд',
            'symbolic_meaning_primary' => 'Основное символическое значение',
            'symbolic_meaning_secondary' => 'Дополнительное символическое значение',
            'symbolic_meaning' => 'Символическое значение',
            'primary_tags' => 'Основные теги',
            'emotional_tags' => 'Эмоциональные теги',
            'theme_tags' => 'Тематические теги',
            'skill_tags' => 'Теги навыков',
            'lucidity_index' => 'Индекс люцидности',
            'lucidity_score' => 'Оценка люцидности',
            'lucidity_level' => 'Уровень люцидности',
            'calculation' => 'Расчёт',
            'interpretation' => 'Интерпретация',
            'lucid_moments' => 'Люцидные моменты',
            'lucid_moment' => 'Люцидный момент',
            'moment_time' => 'Время момента',
            'moment_description' => 'Описание момента',
            'moment_type' => 'Тип момента',
            'moment_quality' => 'Качество момента',
            'certainty_level' => 'Уровень уверенности',
            'possible_connections' => 'Возможные связи',
            'awareness_level' => 'Уровень осознанности',
            'control_level' => 'Уровень контроля',
            'clarity_level' => 'Уровень ясности',
            'stability_level' => 'Уровень стабильности',
            'scenario' => 'Сценарий',
            'guidance' => 'Руководство',
            'actions' => 'Действия',
            'title' => 'Название',
            'steps' => 'Шаги',
            'step_by_step' => 'Пошагово',
            'approach' => 'Подход',
            'application' => 'Применение',
            'action' => 'Действие',
            'warnings' => 'Предупреждения',
            'short_term' => 'Краткосрочные',
            'medium_term' => 'Среднесрочные',
            'long_term' => 'Долгосрочные',
            'immediate_actions' => 'Немедленные действия',
            'therapeutic_approaches' => 'Терапевтические подходы',
            'os_scenarios' => 'Сценарии для осознанных сновидений',
            'step_by_step_guides' => 'Пошаговые руководства',
            'objects' => 'Объекты',
            'locations' => 'Локации',
            'characters' => 'Персонажи',
            'key_insights' => 'Ключевые инсайты',
            'life_context_connections' => 'Контекст жизни',
        ];
        
        $fieldNameLower = mb_strtolower($fieldName);
        if (isset($translations[$fieldNameLower])) {
            return $translations[$fieldNameLower];
        }
        
        // Если нет прямого перевода, форматируем название
        return mb_ucfirst(mb_strtolower(str_replace('_', ' ', $fieldName)));
    };
@endphp

<!-- Новая система анализа -->
<div class="space-y-4">
    <!-- Dream Metadata Block -->
    @if(!empty($dreamMetadata))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">
                {{ $safeDisplay($dreamMetadata['dream_title'] ?? null, 'dream_title') ?: 'Анализ сна' }}
            </h2>
            
            <div class="flex flex-wrap items-center gap-3 mb-3">
                @if(isset($dreamMetadata['dream_type']))
                    @php $dreamType = $safeDisplay($dreamMetadata['dream_type'], 'dream_type'); @endphp
                    @if(!empty($dreamType))
                        <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                            {{ mb_ucfirst(mb_strtolower($dreamType)) }}
                        </span>
                    @endif
                @endif
                @php
                    // Получаем полное название традиции из config/traditions.php
                    $traditionKey = null;
                    if (!empty($analysisData['response_metadata']['tradition_used'])) {
                        $traditionKey = $analysisData['response_metadata']['tradition_used'];
                    } elseif (!empty($tradition)) {
                        $traditionKey = $tradition;
                    } elseif (!empty($traditionSpecific['tradition_name'])) {
                        $traditionKey = $safeDisplay($traditionSpecific['tradition_name'], 'tradition_name');
                    }
                    
                    $traditionDisplayName = $traditionKey;
                    if (!empty($traditionKey)) {
                        $traditionsConfig = config('traditions', []);
                        if (isset($traditionsConfig[$traditionKey]['name_full'])) {
                            $traditionDisplayName = $traditionsConfig[$traditionKey]['name_full'];
                        } elseif (isset($traditionsConfig[$traditionKey]['name_short'])) {
                            $traditionDisplayName = $traditionsConfig[$traditionKey]['name_short'];
                        }
                    }
                @endphp
                @if(!empty($traditionDisplayName))
                    <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                        {{ $traditionDisplayName }}
                    </span>
                @endif
                @if(isset($dreamMetadata['emotional_tone']))
                    @php $emotionalTone = $safeDisplay($dreamMetadata['emotional_tone'], 'emotional_tone'); @endphp
                    @if(!empty($emotionalTone))
                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 px-3 py-1 rounded-full text-sm">
                            {{ mb_ucfirst(mb_strtolower($emotionalTone)) }}
                        </span>
                    @endif
                @endif
                @if(isset($dreamMetadata['recall_quality']))
                    @php 
                        $recallQuality = $dreamMetadata['recall_quality'];
                        $recallQualityNum = is_numeric($recallQuality) ? (float)$recallQuality : 0;
                    @endphp
                    @if($recallQualityNum > 0)
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Качество воспоминания:</strong> {{ number_format($recallQualityNum * 100, 0) }}%
                        </span>
                    @endif
                @endif
            </div>
            
            @if(isset($dreamMetadata['summary_insight']))
                @php $summaryInsight = $safeDisplay($dreamMetadata['summary_insight'], 'summary_insight'); @endphp
                @if(!empty($summaryInsight))
                    <div class="mb-3 p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg border-l-4 border-purple-500">
                        <h3 class="font-semibold text-purple-800 dark:text-purple-200 mb-1 text-sm">Ключевая мысль</h3>
                        <p class="text-gray-700 dark:text-gray-300 text-sm">{{ $summaryInsight }}</p>
                    </div>
                @endif
            @endif
            
            @if(isset($dreamMetadata['context_summary']))
                @php $contextSummary = $safeDisplay($dreamMetadata['context_summary'], 'context_summary'); @endphp
                @if(!empty($contextSummary))
                    <div class="mb-3">
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Контекст:</span>
                        <span class="text-gray-700 dark:text-gray-300 text-sm ml-1">{{ $contextSummary }}</span>
                    </div>
                @endif
            @endif
        </div>
    @endif

    <!-- Детальный анализ -->
    @if(isset($dreamMetadata['dream_detailed']))
        @php
            $detailedTextRaw = $safeDisplay($dreamMetadata['dream_detailed'], 'dream_detailed');
            $detailedText = '';
            if (!empty($detailedTextRaw)) {
                $detailedText = trim($detailedTextRaw);
                // Убираем множественные пробелы и переносы строк
                $detailedText = preg_replace('/\s+/', ' ', $detailedText);
                // Убираем пробелы в начале и конце каждого предложения (после точки, восклицательного знака и т.д.)
                $detailedText = preg_replace('/\s+([.!?])\s+/', '$1 ', $detailedText);
                $detailedText = trim($detailedText);
            }
        @endphp
        @if(!empty($detailedText))
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Детальный анализ</h2>
                <div class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg">
                    {{ $detailedText }}
                </div>
            </div>
        @endif
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
                            @php $primaryEmotion = $safeDisplay($emotionalBreakdown['primary_emotion'], 'primary_emotion'); @endphp
                            @if(!empty($primaryEmotion))
                                <div>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Основная эмоция:</span>
                                    <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full text-xs ml-2">
                                        {{ $primaryEmotion }}
                                    </span>
                                </div>
                            @endif
                        @endif
                        
                        @if(!empty($emotionalBreakdown['secondary_emotions']) && is_array($emotionalBreakdown['secondary_emotions']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Вторичные:</span>
                                @foreach($emotionalBreakdown['secondary_emotions'] as $emotion)
                                    @php $emotionText = $safeDisplay($emotion, 'secondary_emotion'); @endphp
                                    @if(!empty($emotionText))
                                        <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full text-xs ml-1">
                                            {{ $emotionText }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        
                        @if(!empty($emotionalBreakdown['emotional_triggers']))
                            @php $triggersText = $safeDisplay($emotionalBreakdown['emotional_triggers'], 'emotional_triggers'); @endphp
                            @if(!empty($triggersText))
                                <div>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Триггеры:</span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 ml-2">{{ $triggersText }}</span>
                                </div>
                            @endif
                        @endif
                        
                        @if(!empty($emotionalBreakdown['emotional_trajectory']))
                            @php $trajectoryText = $safeDisplay($emotionalBreakdown['emotional_trajectory'], 'emotional_trajectory'); @endphp
                            @if(!empty($trajectoryText))
                                <div>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Траектория:</span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 ml-2">{{ $trajectoryText }}</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
            
            @php
                // Проверяем archetypal_patterns в разных местах
                $archetypalPatterns = null;
                if (!empty($coreAnalysis['archetypal_patterns'])) {
                    $archetypalPatterns = $coreAnalysis['archetypal_patterns'];
                } elseif (!empty($analysisData['archetypal_patterns'])) {
                    $archetypalPatterns = $analysisData['archetypal_patterns'];
                }
                // Если это строка, преобразуем в массив
                if (is_string($archetypalPatterns) && !empty(trim($archetypalPatterns))) {
                    $archetypalPatterns = [$archetypalPatterns];
                }
                // Проверяем, что это массив и не пустой
                if (!is_array($archetypalPatterns) || empty($archetypalPatterns)) {
                    $archetypalPatterns = null;
                }
            @endphp
            @if(!empty($archetypalPatterns) && is_array($archetypalPatterns) && count($archetypalPatterns) > 0)
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Архетипические паттерны</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($archetypalPatterns as $pattern)
                            @php
                                // Обрабатываем разные форматы данных
                                if (is_string($pattern)) {
                                    $patternName = $pattern;
                                    $description = null;
                                    $strength = null;
                                    $function = null;
                                    $manifestation = null;
                                } elseif (is_array($pattern)) {
                                    // Новый формат: pattern_name, description, strength
                                    $patternName = $pattern['pattern_name'] ?? $pattern['patternName'] ?? $pattern['название_паттерна'] ?? 
                                                  $pattern['archetype'] ?? $pattern['архетип'] ?? $pattern['name'] ?? $pattern['название'] ?? $pattern[0] ?? '';
                                    $description = $pattern['description'] ?? $pattern['описание'] ?? null;
                                    $strength = $pattern['strength'] ?? $pattern['сила'] ?? null;
                                    // Старый формат: function, manifestation
                                    $function = $pattern['function'] ?? $pattern['функция'] ?? null;
                                    $manifestation = $pattern['manifestation'] ?? $pattern['проявление'] ?? null;
                                } elseif (is_object($pattern)) {
                                    $patternName = $pattern->pattern_name ?? $pattern->patternName ?? $pattern->название_паттерна ?? 
                                                  $pattern->archetype ?? $pattern->архетип ?? $pattern->name ?? $pattern->название ?? '';
                                    $description = $pattern->description ?? $pattern->описание ?? null;
                                    $strength = $pattern->strength ?? $pattern->сила ?? null;
                                    $function = $pattern->function ?? $pattern->функция ?? null;
                                    $manifestation = $pattern->manifestation ?? $pattern->проявление ?? null;
                                } else {
                                    $patternName = '';
                                    $description = null;
                                    $strength = null;
                                    $function = null;
                                    $manifestation = null;
                                }
                                $patternName = trim($safeDisplay($patternName, 'pattern_name'));
                            @endphp
                            @if(!empty($patternName))
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-start justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $patternName }}</h4>
                                        @if(!empty($strength) && is_numeric($strength))
                                            <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300 px-2 py-1 rounded-full whitespace-nowrap ml-2">
                                                Сила: {{ number_format($strength * 100, 0) }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(!empty($description))
                                        @php $descriptionText = $safeDisplay($description, 'pattern_description'); @endphp
                                        @if(!empty($descriptionText))
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2 leading-relaxed">{{ $descriptionText }}</p>
                                        @endif
                                    @endif
                                    @if(!empty($function))
                                        @php $functionText = $safeDisplay($function, 'archetype_function'); @endphp
                                        @if(!empty($functionText))
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-1">
                                                <span class="font-medium">Функция:</span> {{ $functionText }}
                                            </p>
                                        @endif
                                    @endif
                                    @if(!empty($manifestation))
                                        @php $manifestationText = $safeDisplay($manifestation, 'archetype_manifestation'); @endphp
                                        @if(!empty($manifestationText))
                                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                                <span class="font-medium">Проявление:</span> {{ $manifestationText }}
                                            </p>
                                        @endif
                                    @endif
                                </div>
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
                            @php $insightText = $safeDisplay($insight, 'key_insight'); @endphp
                            @if(!empty($insightText))
                                <li class="text-sm text-gray-700 dark:text-gray-300">{{ $insightText }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(!empty($coreAnalysis['life_context_connections']))
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Контекст жизни</h3>
                    @php
                        $lifeContext = $coreAnalysis['life_context_connections'];
                    @endphp
                    @if(is_array($lifeContext))
                        @if(isset($lifeContext[0]) && is_string($lifeContext[0]))
                            {{-- Простой массив строк --}}
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                @foreach($lifeContext as $item)
                                    @php $itemText = $safeDisplay($item, 'life_context_item'); @endphp
                                    @if(!empty($itemText))
                                        <li class="text-sm text-gray-700 dark:text-gray-300">{{ $itemText }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            {{-- Ассоциативный массив или массив объектов --}}
                            <div class="space-y-2">
                                @foreach($lifeContext as $key => $value)
                                    @php
                                        $keyLower = mb_strtolower($key);
                                        // Пропускаем только Certainty level (и заголовок, и значение)
                                        if (in_array($keyLower, ['certainty_level', 'certainty level'])) {
                                            continue;
                                        }
                                        // Для Possible connections убираем только заголовок, но оставляем значение
                                        $showTitle = !in_array($keyLower, ['possible_connections', 'possible connections']);
                                        $fieldTitle = is_numeric($key) ? '' : ($showTitle ? $translateField($key) : '');
                                        $valueText = $safeDisplay($value, 'life_context_field');
                                    @endphp
                                    @if(!empty($valueText))
                                        <div>
                                            @if(!empty($fieldTitle))
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $fieldTitle }}:</span>
                                            @endif
                                            <p class="text-sm text-gray-700 dark:text-gray-300 {{ !empty($fieldTitle) ? 'mt-1' : '' }}">{{ $valueText }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @else
                        {{-- Строка или другой тип --}}
                        @php $lifeContextText = $safeDisplay($lifeContext, 'life_context_connections'); @endphp
                        @if(!empty($lifeContextText))
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $lifeContextText }}</p>
                        @endif
                    @endif
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
                        @php
                            $elements = $symbolicElements[$key];
                            if (empty($elements)) {
                                continue;
                            }
                            $firstElement = reset($elements);
                            // Проверяем разные варианты структуры данных
                            // Если первый элемент - массив и не числовой ключ, то это объект
                            $isObjectArray = is_array($firstElement) && (
                                isset($firstElement['element']) || 
                                isset($firstElement['name']) || 
                                isset($firstElement['symbolic_meaning']) ||
                                isset($firstElement['symbolic_meaning_primary']) ||
                                isset($firstElement['emotional_charge']) ||
                                (count($firstElement) > 0 && !isset($firstElement[0])) // Ассоциативный массив
                            );
                            $isSimpleArray = is_string($firstElement);
                        @endphp
                        
                        @if($isObjectArray)
                            {{-- Массив объектов --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($elements as $element)
                                    @if(is_array($element))
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                            @php
                                                $elementName = $safeDisplay($element['element'] ?? $element['name'] ?? '');
                                                $emotionalCharge = $safeDisplay($element['emotional_charge'] ?? '');
                                                $symbolicMeaning = $safeDisplay($element['symbolic_meaning_primary'] ?? $element['symbolic_meaning'] ?? '');
                                                $symbolicMeaningSecondary = $safeDisplay($element['symbolic_meaning_secondary'] ?? '');
                                            @endphp
                                            
                                            @if(!empty($elementName) || !empty($emotionalCharge))
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    @if(!empty($elementName))
                                                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex-1">
                                                            {{ $elementName }}
                                                        </h4>
                                                    @endif
                                                    
                                                    @if(!empty($emotionalCharge))
                                                        <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300 px-2 py-0.5 rounded-full whitespace-nowrap flex-shrink-0">
                                                            {{ $emotionalCharge }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            @if(!empty($symbolicMeaning))
                                                <p class="text-xs text-gray-700 dark:text-gray-300 mb-1">{{ $symbolicMeaning }}</p>
                                            @endif
                                            
                                            @if(!empty($symbolicMeaningSecondary))
                                                <p class="text-xs text-gray-700 dark:text-gray-300 italic">{{ $symbolicMeaningSecondary }}</p>
                                            @endif
                                        </div>
                                    @else
                                        {{-- Простой элемент --}}
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $safeDisplay($element) }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @elseif($isSimpleArray)
                            {{-- Простой массив строк - тоже отображаем плиткой --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($elements as $element)
                                    @php $elementText = $safeDisplay($element); @endphp
                                    @if(!empty($elementText))
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $elementText }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            {{-- Смешанный или неизвестный формат - пытаемся отобразить плиткой --}}
                            @if(is_array($elements))
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($elements as $element)
                                        @if(is_array($element))
                                            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                                @php
                                                    $elementName = $safeDisplay($element['element'] ?? $element['name'] ?? array_values($element)[0] ?? '');
                                                    $emotionalCharge = $safeDisplay($element['emotional_charge'] ?? '');
                                                    $symbolicMeaning = $safeDisplay($element['symbolic_meaning_primary'] ?? $element['symbolic_meaning'] ?? '');
                                                    $symbolicMeaningSecondary = $safeDisplay($element['symbolic_meaning_secondary'] ?? '');
                                                @endphp
                                                
                                                @if(!empty($elementName) || !empty($emotionalCharge))
                                                    <div class="flex items-start justify-between gap-2 mb-2">
                                                        @if(!empty($elementName))
                                                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex-1">
                                                                {{ $elementName }}
                                                            </h4>
                                                        @endif
                                                        
                                                        @if(!empty($emotionalCharge))
                                                            <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300 px-2 py-0.5 rounded-full whitespace-nowrap flex-shrink-0">
                                                                {{ $emotionalCharge }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                @if(!empty($symbolicMeaning))
                                                    <p class="text-xs text-gray-700 dark:text-gray-300 mb-1">{{ $symbolicMeaning }}</p>
                                                @endif
                                                
                                                @if(!empty($symbolicMeaningSecondary))
                                                    <p class="text-xs text-gray-700 dark:text-gray-300 italic">{{ $symbolicMeaningSecondary }}</p>
                                                @endif
                                            </div>
                                        @else
                                            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $safeDisplay($element) }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    {{ $safeDisplay($elements, $key) }}
                                </div>
                            @endif
                        @endif
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
                                    @php
                                        $tagText = $safeDisplay($tag, 'tag');
                                    @endphp
                                    @if(!empty($tagText))
                                        @php
                                            $tagDisplay = str_replace('_', ' ', $tagText);
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

    <!-- Lucidity Analysis -->
    @if(!empty($lucidityAnalysis))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-4">Анализ осознанности</h2>
            @if(is_array($lucidityAnalysis))
                <div class="space-y-4">
                    {{-- Индекс люцидности как бейдж достижения --}}
                    @if(isset($lucidityAnalysis['lucidity_index']))
                        @php
                            $lucidityIndexValue = $lucidityAnalysis['lucidity_index'];
                            $indexValue = is_array($lucidityIndexValue) ? ($lucidityIndexValue['индекс'] ?? $lucidityIndexValue['index'] ?? $lucidityIndexValue['value'] ?? $lucidityIndexValue) : $lucidityIndexValue;
                            $indexNum = is_numeric($indexValue) ? (float)$indexValue : 0;
                            $indexPercent = $indexNum * 100;
                            // Определяем цвет в зависимости от значения
                            $badgeColor = $indexNum >= 0.7 ? 'bg-yellow-400 dark:bg-yellow-600 text-yellow-900 dark:text-yellow-100' : 
                                         ($indexNum >= 0.4 ? 'bg-blue-400 dark:bg-blue-600 text-blue-900 dark:text-blue-100' : 
                                         'bg-gray-400 dark:bg-gray-600 text-gray-900 dark:text-gray-100');
                        @endphp
                        <div class="flex items-center justify-center mb-4">
                            <div class="relative inline-flex items-center justify-center">
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-400 to-blue-400 rounded-full blur opacity-30"></div>
                                <div class="relative {{ $badgeColor }} rounded-full px-6 py-3 shadow-lg">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">⭐</span>
                                        <div>
                                            <div class="text-xs font-semibold opacity-80">Индекс люцидности</div>
                                            <div class="text-2xl font-bold">{{ number_format($indexPercent, 0) }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @foreach($lucidityAnalysis as $key => $value)
                        @php
                            // Пропускаем lucidity_index, так как он уже обработан выше
                            if ($key === 'lucidity_index') {
                                continue;
                            }
                            $fieldTitle = $translateField($key);
                        @endphp
                        @if(is_array($value))
                            @if(isset($value[0]) && is_string($value[0]))
                                {{-- Простой массив строк --}}
                                <div class="mb-3">
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ $fieldTitle }}</h4>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        @foreach($value as $item)
                                            @php $itemText = $safeDisplay($item, 'lucidity_item'); @endphp
                                            @if(!empty($itemText))
                                                <li class="text-sm text-gray-700 dark:text-gray-300">{{ $itemText }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif($key === 'lucid_moments' || $key === 'lucid_moment')
                                {{-- Специальная обработка для Lucid moments --}}
                                <div class="mb-4">
                                    <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ $fieldTitle }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($value as $moment)
                                            @if(is_array($moment))
                                                <div class="px-4 py-3 bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                                                    @foreach($moment as $momentKey => $momentValue)
                                                        @php
                                                            $momentTitle = $translateField($momentKey);
                                                            $momentText = $safeDisplay($momentValue, 'lucid_moment_field');
                                                        @endphp
                                                        @if(!empty($momentText))
                                                            <div class="mb-2 last:mb-0">
                                                                @if(!empty($momentTitle) && $momentKey !== 'description' && $momentKey !== 'описание')
                                                                    <span class="text-xs font-semibold text-purple-700 dark:text-purple-300">{{ $momentTitle }}:</span>
                                                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">{{ $momentText }}</p>
                                                                @else
                                                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $momentText }}</p>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                @php $momentText = $safeDisplay($moment, 'lucid_moment'); @endphp
                                                @if(!empty($momentText))
                                                    <div class="px-4 py-3 bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $momentText }}</p>
                                                    </div>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Ассоциативный массив или массив объектов --}}
                                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ $fieldTitle }}</h4>
                                    <div class="space-y-2">
                                        @foreach($value as $subKey => $subValue)
                                            @php
                                                $subTitle = is_numeric($subKey) ? '' : $translateField($subKey);
                                                $subText = $safeDisplay($subValue, 'lucidity_subfield');
                                            @endphp
                                            @if(!empty($subText))
                                                <div>
                                                    @if(!empty($subTitle))
                                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $subTitle }}:</span>
                                                    @endif
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $subText }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            @php $valueText = $safeDisplay($value, 'lucidity_analysis'); @endphp
                            @if(!empty($valueText))
                                <div class="mb-2">
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ $fieldTitle }}</h4>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $valueText }}</p>
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            @else
                @php $lucidityText = $safeDisplay($lucidityAnalysis, 'lucidity_analysis'); @endphp
                @if(!empty($lucidityText))
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $lucidityText }}</p>
                @endif
            @endif
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
                        @php $calculationText = $safeDisplay($lucidityIndex['расчёт'], 'lucidity_calculation'); @endphp
                        @if(!empty($calculationText))
                            <div class="mb-1">
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Расчёт:</span>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $calculationText }}</p>
                            </div>
                        @endif
                    @endif
                    @if(!empty($lucidityIndex['интерпретация']))
                        @php $interpretationText = $safeDisplay($lucidityIndex['интерпретация'], 'lucidity_interpretation'); @endphp
                        @if(!empty($interpretationText))
                            <div>
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Интерпретация:</span>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $interpretationText }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
            
            @if(!empty($traditionSpecific['analysis']) && is_array($traditionSpecific['analysis']))
                <div class="space-y-4">
                    @foreach($traditionSpecific['analysis'] as $analysisKey => $analysisValue)
                        @php
                            $hasValue = false;
                            $testValue = $safeDisplay($analysisValue, $analysisKey);
                            $hasValue = !empty($testValue);
                        @endphp
                        @if($hasValue)
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0 last:pb-0">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    {{ $translateField($analysisKey) }}
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
                                                                @php $itemValueText = $safeDisplay($itemValue, $itemKey); @endphp
                                                                @if(!empty($itemValueText))
                                                                    <div class="mb-1 last:mb-0">
                                                                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $translateField($itemKey) }}:</span>
                                                                        <span class="text-gray-600 dark:text-gray-400 ml-1">
                                                                            {{ $itemValueText }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        @php $itemText = $safeDisplay($item, 'analysis_item'); @endphp
                                                        @if(!empty($itemText))
                                                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $itemText }}</div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            {{-- Array of strings --}}
                                            <ul class="list-disc list-inside space-y-1 ml-2">
                                                @foreach($analysisValue as $item)
                                                    @php $itemText = $safeDisplay($item, 'analysis_item'); @endphp
                                                    @if(!empty($itemText))
                                                        <li class="text-sm text-gray-700 dark:text-gray-300">{{ $itemText }}</li>
                                                    @endif
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
                                                        @php $subValueText = $safeDisplay($subValue, $subKey); @endphp
                                                        @if(!empty($subValueText))
                                                            <div class="text-sm">
                                                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $translateField($subKey) }}:</span>
                                                                <span class="text-gray-600 dark:text-gray-400 ml-1">
                                                                    {{ $subValueText }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div>
                                                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $translateField($subKey) }}:</span>
                                                            @if(is_array($subValue))
                                                                @if(isset($subValue[0]) && is_numeric(array_keys($subValue)[0]))
                                                                    <ul class="list-disc list-inside space-y-1 ml-2 mt-1">
                                                                        @foreach($subValue as $item)
                                                                            @php $itemText = $safeDisplay($item, $subKey . '_item'); @endphp
                                                                            @if(!empty($itemText))
                                                                                <li class="text-sm text-gray-700 dark:text-gray-300">{{ $itemText }}</li>
                                                                            @endif
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    @php $subValueText = $safeDisplay($subValue, $subKey); @endphp
                                                                    @if(!empty($subValueText))
                                                                        <div class="text-sm text-gray-700 dark:text-gray-300 mt-1 ml-2 whitespace-pre-wrap">
                                                                            {{ $subValueText }}
                                                                        </div>
                                                                    @endif
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
                                                                            @php $stepItemText = $safeDisplay($stepItem, 'step_item'); @endphp
                                                                            @if(!empty($stepItemText))
                                                                                <li>{{ $stepItemText }}</li>
                                                                            @endif
                                                                        @endforeach
                                                                    </ol>
                                                                @else
                                                                    @php $subValueText = $safeDisplay($subValue, $subKey); @endphp
                                                                    @if(!empty($subValueText))
                                                                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 ml-2">{{ $subValueText }}</p>
                                                                    @endif
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
                                    @php $valueText = $safeDisplay($analysisValue, $analysisKey); @endphp
                                    @if(!empty($valueText))
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $valueText }}</p>
                                    @endif
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
                $stepByStepGuides = $practicalGuidance['step_by_step_guides'] ?? $practicalGuidance['step_by_step'] ?? [];
            @endphp
            
            @if(!empty($osScenarios) && is_array($osScenarios) && count($osScenarios) > 0)
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Сценарии для осознанных сновидений</h3>
                    <div class="space-y-2">
                        @foreach($osScenarios as $scenario)
                            @if(is_array($scenario))
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                    @php
                                        $scenarioTitle = $safeDisplay($scenario['сценарий'] ?? $scenario['scenario'] ?? null, 'scenario_title');
                                        $guidance = $safeDisplay($scenario['guidance'] ?? $scenario['руководство'] ?? null, 'scenario_guidance');
                                        $actionsRaw = $scenario['действия'] ?? $scenario['actions'] ?? null;
                                    @endphp
                                    @if(!empty($scenarioTitle))
                                        <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-1">
                                            {{ mb_ucfirst(mb_strtolower($scenarioTitle)) }}
                                        </h4>
                                    @endif
                                    @if(!empty($guidance))
                                        <p class="text-xs text-gray-700 dark:text-gray-300 mb-2 leading-relaxed">{{ $guidance }}</p>
                                    @endif
                                    @if(!empty($actionsRaw))
                                        @php
                                            $actions = $safeDisplay($actionsRaw, 'scenario_actions');
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
                                                    @php $actionText = $safeDisplay($action, 'action_item'); @endphp
                                                    @if(!empty($actionText))
                                                        <li>{{ $actionText }}</li>
                                                    @endif
                                                @endforeach
                                            </ol>
                                        @else
                                            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $actions }}</p>
                                        @endif
                                    @endif
                                    
                                    {{-- Универсальная обработка других полей сценария --}}
                                    @php
                                        $knownKeys = ['сценарий', 'scenario', 'guidance', 'руководство', 'действия', 'actions'];
                                        $additionalKeys = array_filter(array_keys($scenario), function($key) use ($knownKeys) {
                                            return !in_array($key, $knownKeys) && !empty($scenario[$key]);
                                        });
                                    @endphp
                                    @if(count($additionalKeys) > 0)
                                        @foreach($additionalKeys as $key)
                                            @php
                                                $value = $scenario[$key];
                                                $fieldTitle = $translateField($key);
                                                $valueText = $safeDisplay($value, 'scenario_field');
                                            @endphp
                                            @if(!empty($valueText))
                                                <div class="mt-2">
                                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $fieldTitle }}:</span>
                                                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-1">{{ $valueText }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            @else
                                {{-- Если сценарий не массив, а строка --}}
                                @php $scenarioText = $safeDisplay($scenario, 'scenario'); @endphp
                                @if(!empty($scenarioText))
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        <p class="text-xs text-gray-700 dark:text-gray-300">{{ $scenarioText }}</p>
                                    </div>
                                @endif
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
                                    @php
                                        $approachTitle = $safeDisplay($approach['approach'] ?? $approach['подход'] ?? null, 'therapeutic_approach');
                                        $approachApplication = $safeDisplay($approach['application'] ?? $approach['применение'] ?? null, 'therapeutic_application');
                                    @endphp
                                    @if(!empty($approachTitle))
                                        <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-1">
                                            {{ $approachTitle }}
                                        </h4>
                                    @endif
                                    @if(!empty($approachApplication))
                                        <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">{{ $approachApplication }}</p>
                                    @endif
                                </div>
                            @else
                                @php $approachText = $safeDisplay($approach, 'therapeutic_approach'); @endphp
                                @if(!empty($approachText))
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $approachText }}</p>
                                    </div>
                                @endif
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
                                @php $actionText = $safeDisplay($action, 'immediate_action'); @endphp
                                @if(!empty($actionText))
                                    <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        <p class="text-xs text-gray-700 dark:text-gray-300">{{ $actionText }}</p>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            @foreach($immediateActions as $action)
                                @if(is_array($action))
                                    <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        @php
                                            $actionTitle = $safeDisplay($action['action'] ?? $action['действие'] ?? null, 'action_title');
                                            $actionDesc = $safeDisplay($action['description'] ?? $action['описание'] ?? null, 'action_description');
                                        @endphp
                                        @if(!empty($actionTitle))
                                            <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-1">
                                                {{ $actionTitle }}
                                            </h4>
                                        @endif
                                        @if(!empty($actionDesc))
                                            <p class="text-xs text-gray-700 dark:text-gray-300">{{ $actionDesc }}</p>
                                        @endif
                                    </div>
                                @else
                                    @php $actionText = $safeDisplay($action, 'immediate_action'); @endphp
                                    @if(!empty($actionText))
                                        <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                            <p class="text-xs text-gray-700 dark:text-gray-300">{{ $actionText }}</p>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
            
            @if(!empty($stepByStepGuides) && is_array($stepByStepGuides) && count($stepByStepGuides) > 0)
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Пошаговые руководства</h3>
                    <div class="space-y-3">
                        @foreach($stepByStepGuides as $guide)
                            @if(is_array($guide))
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                    @php
                                        $guideTitle = $safeDisplay($guide['title'] ?? $guide['название'] ?? $guide['guide'] ?? $guide['руководство'] ?? null, 'guide_title');
                                        $guideSteps = $guide['steps'] ?? $guide['шаги'] ?? $guide['step_by_step'] ?? null;
                                    @endphp
                                    @if(!empty($guideTitle))
                                        <h4 class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-2">
                                            {{ $guideTitle }}
                                        </h4>
                                    @endif
                                    @if(!empty($guideSteps))
                                        @if(is_array($guideSteps))
                                            <ol class="list-decimal list-inside space-y-1 ml-2 text-xs text-gray-700 dark:text-gray-300">
                                                @foreach($guideSteps as $step)
                                                    @php $stepText = $safeDisplay($step, 'guide_step'); @endphp
                                                    @if(!empty($stepText))
                                                        <li>{{ $stepText }}</li>
                                                    @endif
                                                @endforeach
                                            </ol>
                                        @else
                                            @php
                                                $stepsText = $safeDisplay($guideSteps, 'guide_steps');
                                                // Парсим шаги: ищем паттерн "1. текст 2. текст" и разбиваем на список
                                                if (preg_match_all('/\d+\.\s*([^0-9]+?)(?=\d+\.|$)/', $stepsText, $matches)) {
                                                    $stepItems = array_map('trim', $matches[1]);
                                                } else {
                                                    $stepItems = preg_split('/\n|\d+\.\s+/', $stepsText, -1, PREG_SPLIT_NO_EMPTY);
                                                    $stepItems = array_map('trim', $stepItems);
                                                    $stepItems = array_filter($stepItems, function($item) {
                                                        return !empty($item) && strlen($item) > 3;
                                                    });
                                                }
                                            @endphp
                                            @if(!empty($stepItems) && count($stepItems) > 1)
                                                <ol class="list-decimal list-inside space-y-1 ml-2 text-xs text-gray-700 dark:text-gray-300">
                                                    @foreach($stepItems as $step)
                                                        @php $stepText = $safeDisplay($step, 'guide_step'); @endphp
                                                        @if(!empty($stepText))
                                                            <li>{{ $stepText }}</li>
                                                        @endif
                                                    @endforeach
                                                </ol>
                                            @else
                                                <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $stepsText }}</p>
                                            @endif
                                        @endif
                                    @endif
                                    
                                    {{-- Универсальная обработка других полей руководства --}}
                                    @php
                                        $guideKnownKeys = ['title', 'название', 'guide', 'руководство', 'steps', 'шаги', 'step_by_step'];
                                        $guideAdditionalKeys = array_filter(array_keys($guide), function($key) use ($guideKnownKeys) {
                                            return !in_array($key, $guideKnownKeys) && !empty($guide[$key]);
                                        });
                                    @endphp
                                    @if(count($guideAdditionalKeys) > 0)
                                        @foreach($guideAdditionalKeys as $key)
                                            @php
                                                $value = $guide[$key];
                                                $fieldTitle = $translateField($key);
                                                $valueText = $safeDisplay($value, 'guide_field');
                                            @endphp
                                            @if(!empty($valueText))
                                                <div class="mt-2">
                                                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $fieldTitle }}:</span>
                                                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-1">{{ $valueText }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            @else
                                @php $guideText = $safeDisplay($guide, 'guide'); @endphp
                                @if(!empty($guideText))
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $guideText }}</p>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Универсальная обработка других полей practical_guidance --}}
            @php
                $knownKeys = ['os_scenarios', 'therapeutic_approaches', 'immediate_actions', 'step_by_step_guides', 'step_by_step'];
                if (is_array($practicalGuidance)) {
                    $additionalKeys = array_filter(array_keys($practicalGuidance), function($key) use ($knownKeys, $practicalGuidance) {
                        return !in_array($key, $knownKeys) && !empty($practicalGuidance[$key]);
                    });
                } else {
                    $additionalKeys = [];
                }
            @endphp
            @if(count($additionalKeys) > 0)
                @foreach($additionalKeys as $key)
                    @php
                        $value = $practicalGuidance[$key];
                        $title = $translateField($key);
                    @endphp
                    @if(is_array($value) && count($value) > 0)
                        <div class="mb-4">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ $title }}</h3>
                            <div class="space-y-2">
                                @foreach($value as $item)
                                    @if(is_array($item))
                                        {{-- Массив объектов --}}
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                            @foreach($item as $itemKey => $itemValue)
                                                @php
                                                    $itemTitle = $translateField($itemKey);
                                                    $itemText = $safeDisplay($itemValue, 'practical_guidance_item');
                                                @endphp
                                                @if(!empty($itemText))
                                                    <div class="mb-1">
                                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $itemTitle }}:</span>
                                                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $itemText }}</p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        {{-- Простое значение --}}
                                        @php $itemText = $safeDisplay($item, 'practical_guidance_item'); @endphp
                                        @if(!empty($itemText))
                                            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $itemText }}</p>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @elseif(!is_array($value))
                        @php $valueText = $safeDisplay($value, 'practical_guidance_field'); @endphp
                        @if(!empty($valueText))
                            <div class="mb-4">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ $title }}</h3>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $valueText }}</p>
                            </div>
                        @endif
                    @endif
                @endforeach
            @endif
        </div>
    @endif

    <!-- Recommendations -->
    @if(!empty($recommendations))
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Рекомендации</h2>
            
            {{-- Предупреждения --}}
            @if(!empty($recommendations['warnings']))
                @php
                    $warnings = $recommendations['warnings'];
                    $warningsArray = is_array($warnings) ? $warnings : [$warnings];
                @endphp
                @if(count($warningsArray) > 0)
                    <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <h3 class="text-base font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Предупреждения</h3>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            @foreach($warningsArray as $warning)
                                @php $warningText = $safeDisplay($warning, 'warning'); @endphp
                                @if(!empty($warningText))
                                    <li class="text-sm text-gray-700 dark:text-gray-300">{{ $warningText }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
            
            {{-- Стандартные рекомендации по срокам --}}
            @foreach(['short_term' => 'Краткосрочные', 'medium_term' => 'Среднесрочные', 'long_term' => 'Долгосрочные'] as $key => $title)
                @if(isset($recommendations[$key]) && !empty($recommendations[$key]))
                    @php
                        $recs = $recommendations[$key];
                        $recsArray = is_array($recs) ? $recs : [$recs];
                    @endphp
                    @if(count($recsArray) > 0)
                        <div class="mb-3 last:mb-0">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ $title }}</h3>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                @foreach($recsArray as $rec)
                                    @php $recText = $safeDisplay($rec, 'recommendation'); @endphp
                                    @if(!empty($recText))
                                        <li class="text-sm text-gray-700 dark:text-gray-300">{{ $recText }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            @endforeach
            
            {{-- Дополнительные поля рекомендаций (универсальная обработка) --}}
            @php
                $knownKeys = ['warnings', 'short_term', 'medium_term', 'long_term'];
                if (is_array($recommendations)) {
                    $additionalKeys = array_filter(array_keys($recommendations), function($key) use ($knownKeys, $recommendations) {
                        return !in_array($key, $knownKeys) && !empty($recommendations[$key]);
                    });
                } else {
                    $additionalKeys = [];
                }
            @endphp
            @if(count($additionalKeys) > 0)
                @foreach($additionalKeys as $key)
                    @php
                        $recValue = $recommendations[$key];
                        $title = $translateField($key);
                    @endphp
                    @if(is_array($recValue) && count($recValue) > 0)
                        <div class="mb-3 last:mb-0">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ $title }}</h3>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                @foreach($recValue as $rec)
                                    @php $recText = $safeDisplay($rec, 'recommendation'); @endphp
                                    @if(!empty($recText))
                                        <li class="text-sm text-gray-700 dark:text-gray-300">{{ $recText }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @elseif(!is_array($recValue))
                        @php $recText = $safeDisplay($recValue, 'recommendation'); @endphp
                        @if(!empty($recText))
                            <div class="mb-3 last:mb-0">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">{{ $title }}</h3>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $recText }}</p>
                            </div>
                        @endif
                    @endif
                @endforeach
            @endif
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
