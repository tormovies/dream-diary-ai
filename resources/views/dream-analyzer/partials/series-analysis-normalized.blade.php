@php
    $seriesDreams = $result->seriesDreams;
@endphp

<!-- Заголовок серии -->
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">
        {{ $result->series_title ?? 'Анализ серии снов' }}
    </h2>
    
    @php
        // Берём traditions из DreamInterpretation (правильные ключи), а не из Result (могут быть переведённые значения)
        $traditionsToDisplay = null;
        if (isset($interpretation) && $interpretation->traditions && is_array($interpretation->traditions)) {
            $traditionsToDisplay = $interpretation->traditions;
        } elseif ($result->traditions && is_array($result->traditions)) {
            $traditionsToDisplay = $result->traditions;
        }
    @endphp
    
    @if($traditionsToDisplay && count($traditionsToDisplay) > 0)
        <div class="mb-4">
            <div class="flex items-center flex-wrap gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Традиции анализа:</span>
                @foreach($traditionsToDisplay as $tradition)
                    @php
                        $traditionName = \App\Helpers\TraditionHelper::getDisplayName($tradition);
                    @endphp
                    <span class="inline-block bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                        {{ $traditionName }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    @if($result->overall_theme)
        <div class="mb-4 p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg border-l-4 border-purple-500">
            <h3 class="font-semibold text-purple-800 dark:text-purple-200 mb-2">Общая тема серии</h3>
            <div class="text-gray-700 dark:text-gray-300">{!! \App\Helpers\HtmlHelper::sanitize($result->overall_theme) !!}</div>
        </div>
    @endif

    @if($result->emotional_arc)
        <div class="mb-4 p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg border-l-4 border-indigo-500">
            <h3 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">Эмоциональная дуга</h3>
            <div class="text-gray-700 dark:text-gray-300">{!! \App\Helpers\HtmlHelper::sanitize($result->emotional_arc) !!}</div>
        </div>
    @endif

    @if($result->key_connections && count($result->key_connections) > 0)
        <div class="mb-4">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Ключевые связи</h3>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                @foreach($result->key_connections as $connection)
                    <li>{{ $connection }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<!-- Анализ в традиции (dream_tradition) -->
@php
    $dreamTradition = null;
    // Проверяем в переданной переменной $interpretation (приоритет)
    if (isset($interpretation) && isset($interpretation->analysis_data['series_analysis']['dream_tradition'])) {
        $dreamTradition = $interpretation->analysis_data['series_analysis']['dream_tradition'];
    } elseif (isset($interpretation) && isset($interpretation->analysis_data['dream_tradition'])) {
        $dreamTradition = $interpretation->analysis_data['dream_tradition'];
    }
    // Проверяем через связь result->interpretation (fallback)
    elseif ($result && $result->relationLoaded('interpretation') && $result->interpretation && isset($result->interpretation->analysis_data['series_analysis']['dream_tradition'])) {
        $dreamTradition = $result->interpretation->analysis_data['series_analysis']['dream_tradition'];
    } elseif ($result && $result->relationLoaded('interpretation') && $result->interpretation && isset($result->interpretation->analysis_data['dream_tradition'])) {
        $dreamTradition = $result->interpretation->analysis_data['dream_tradition'];
    }
@endphp

@if($dreamTradition)
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">Анализ в традиции</h2>
        <div class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-purple dark:prose-invert max-w-none [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-purple-600 [&_h2]:dark:text-purple-400 [&_h2]:mt-6 [&_h2]:mb-4 [&_h2]:first:mt-0 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-gray-900 [&_h3]:dark:text-white [&_h3]:mt-5 [&_h3]:mb-3 [&_p]:mb-4 [&_p]:leading-relaxed [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-4 [&_ul]:space-y-2 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-4 [&_ol]:space-y-2 [&_li]:mb-1 [&_strong]:font-semibold [&_strong]:text-gray-900 [&_strong]:dark:text-gray-100 [&_em]:italic">
            {!! \App\Helpers\HtmlHelper::sanitize($dreamTradition) !!}
        </div>
    </div>
@endif

<!-- Анализ каждого сна -->
@if($seriesDreams && $seriesDreams->count() > 0)
    @foreach($seriesDreams as $dream)
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                Сон {{ $dream->dream_number }}: {{ \App\Helpers\HtmlHelper::sanitizeTitle($dream->dream_title ?? 'Без названия') }}
            </h3>
            
            @if($dream->dream_type)
                <div class="mb-4 flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Тип сна:</span>
                    <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                        {{ $dream->dream_type }}
                    </span>
                </div>
            @endif

            @if($dream->dream_detailed)
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-l-4 border-purple-500">
                    <h4 class="font-semibold text-purple-800 dark:text-purple-200 mb-3">Детальный анализ</h4>
                    <div class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-purple dark:prose-invert max-w-none">
                        {!! \App\Helpers\HtmlHelper::sanitize($dream->dream_detailed) !!}
                    </div>
                </div>
            @endif

            @if($dream->summary_insight)
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border-l-4 border-blue-500">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Ключевая мысль</h4>
                    <div class="text-gray-700 dark:text-gray-300">{!! \App\Helpers\HtmlHelper::sanitize($dream->summary_insight) !!}</div>
                </div>
            @endif

            @if($dream->emotional_tone)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Эмоциональный тон</h4>
                    <div class="text-gray-700 dark:text-gray-300">{!! \App\Helpers\HtmlHelper::sanitize($dream->emotional_tone) !!}</div>
                </div>
            @endif

            @if($dream->key_symbols && count($dream->key_symbols) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Ключевые символы</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($dream->key_symbols as $symbol)
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                                <h5 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">{!! \App\Helpers\HtmlHelper::sanitize($symbol['symbol'] ?? 'Символ') !!}</h5>
                                @php
                                    // Проверяем оба варианта ключа (на случай, если в данных ключ с угловыми скобками)
                                    $meaning = $symbol['meaning'] ?? $symbol['<meaning>'] ?? null;
                                @endphp
                                @if($meaning)
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        {!! \App\Helpers\HtmlHelper::sanitize($meaning) !!}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($dream->unified_locations && count($dream->unified_locations) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Локации</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($dream->unified_locations as $location)
                            <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm">
                                {{ $location }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($dream->key_tags && count($dream->key_tags) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Теги</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($dream->key_tags as $tag)
                            <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endforeach
@endif

<!-- Практические рекомендации -->
@if($result->recommendations && count($result->recommendations) > 0)
    @php
        // Объединяем рекомендации в один HTML
        $recommendationsHtml = \App\Helpers\HtmlHelper::sanitize(implode('', array_map(function($rec) { return '<p>' . $rec . '</p>'; }, $result->recommendations)));
        // Удаляем заголовок "Практические рекомендации" из любого места (включая внутри <p>)
        $recommendationsHtml = preg_replace('/<p>\s*<h3[^>]*>Практические рекомендации<\/h3>\s*<\/p>/is', '', $recommendationsHtml);
        $recommendationsHtml = preg_replace('/<h3[^>]*>Практические рекомендации<\/h3>\s*/is', '', $recommendationsHtml);
        $recommendationsHtml = trim($recommendationsHtml);
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Практические рекомендации</h3>
        <div class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-purple dark:prose-invert max-w-none">
            {!! $recommendationsHtml !!}
        </div>
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
























