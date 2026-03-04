@php
    $analysisTypeNames = [
        'single' => 'Единичный',
        'integrated' => 'Интегрированный',
        'comparative' => 'Сравнительный',
    ];
@endphp

<!-- Анализ сна -->
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">{{ \App\Helpers\HtmlHelper::sanitizeTitle($result->dream_title ?? 'Анализ сна') }}</h2>
    
    @if($result->dream_type)
        <div class="mb-4 flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Тип сна:</span>
            <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-3 py-1 rounded-full text-sm">
                {!! \App\Helpers\HtmlHelper::sanitize($result->dream_type) !!}
            </span>
        </div>
    @endif
    
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
            <div class="text-gray-700 dark:text-gray-300">{!! \App\Helpers\HtmlHelper::sanitize($result->summary_insight) !!}</div>
        </div>
    @endif
    
    @if($result->emotional_tone)
        <div class="mb-6">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Эмоциональный тон</h3>
            <div class="text-gray-700 dark:text-gray-300">{!! \App\Helpers\HtmlHelper::sanitize($result->emotional_tone) !!}</div>
        </div>
    @endif
</div>

<!-- Детальный анализ -->
@if($result->dream_detailed)
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">Детальный анализ</h2>
        
        <div class="space-y-6">
            <div>
                <div class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-purple dark:prose-invert max-w-none [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-purple-600 [&_h2]:dark:text-purple-400 [&_h2]:mt-6 [&_h2]:mb-4 [&_h2]:first:mt-0 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-gray-900 [&_h3]:dark:text-white [&_h3]:mt-5 [&_h3]:mb-3 [&_p]:mb-4 [&_p]:leading-relaxed [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-4 [&_ul]:space-y-2 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-4 [&_ol]:space-y-2 [&_li]:mb-1 [&_strong]:font-semibold [&_strong]:text-gray-900 [&_strong]:dark:text-gray-100 [&_em]:italic">
                    {!! \App\Helpers\HtmlHelper::sanitize($result->dream_detailed) !!}
                </div>
            </div>
            
            <!-- Key Symbols -->
            @if($result->key_symbols && count($result->key_symbols) > 0)
                @php $symbolPageUrlBySlug = $symbolPageUrlBySlug ?? []; @endphp
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">КЛЮЧЕВЫЕ СИМВОЛЫ И ИХ ЗНАЧЕНИЕ</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($result->key_symbols as $symbol)
                            @php
                                $symName = $symbol['symbol'] ?? 'Символ';
                                $symSlug = \App\Models\DreamInterpretationEntity::nameToSlug(strip_tags($symName));
                                $symUrl = $symbolPageUrlBySlug[$symSlug] ?? null;
                            @endphp
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                                <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">
                                    @if($symUrl)
                                        <a href="{{ $symUrl }}" class="hover:underline">{!! \App\Helpers\HtmlHelper::sanitize($symName) !!}</a>
                                    @else
                                        {!! \App\Helpers\HtmlHelper::sanitize($symName) !!}
                                    @endif
                                </h4>
                                
                                @if(isset($symbol['meaning']))
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        {!! \App\Helpers\HtmlHelper::sanitize($symbol['meaning']) !!}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Unified Locations -->
            @if($result->unified_locations && count($result->unified_locations) > 0)
                @php $symbolPageUrlBySlug = $symbolPageUrlBySlug ?? []; @endphp
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Локации</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($result->unified_locations as $location)
                            @php $locSlug = \App\Models\DreamInterpretationEntity::nameToSlug($location); $locUrl = ($symbolPageUrlBySlug ?? [])[$locSlug] ?? null; @endphp
                            @if($locUrl)
                                <a href="{{ $locUrl }}" class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm hover:underline">{{ $location }}</a>
                            @else
                                <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm">{{ $location }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Key Tags -->
            @if($result->key_tags && count($result->key_tags) > 0)
                @php $symbolPageUrlBySlug = $symbolPageUrlBySlug ?? []; @endphp
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Теги</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($result->key_tags as $tag)
                            @php $tagSlug = \App\Models\DreamInterpretationEntity::nameToSlug($tag); $tagUrl = ($symbolPageUrlBySlug ?? [])[$tagSlug] ?? null; @endphp
                            @if($tagUrl)
                                <a href="{{ $tagUrl }}" class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm hover:underline">{{ $tag }}</a>
                            @else
                                <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm">{{ $tag }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- Анализ в традиции (dream_tradition) -->
@php
    $dreamTradition = null;
    // Проверяем в result->analysis_data (новая система)
    if ($result && isset($result->analysis_data['dream_analysis']['dream_tradition'])) {
        $dreamTradition = $result->analysis_data['dream_analysis']['dream_tradition'];
    } elseif ($result && isset($result->analysis_data['dream_tradition'])) {
        $dreamTradition = $result->analysis_data['dream_tradition'];
    }
    // Проверяем в interpretation->analysis_data (старая система)
    elseif (isset($interpretation->analysis_data['dream_analysis']['dream_tradition'])) {
        $dreamTradition = $interpretation->analysis_data['dream_analysis']['dream_tradition'];
    } elseif (isset($interpretation->analysis_data['dream_tradition'])) {
        $dreamTradition = $interpretation->analysis_data['dream_tradition'];
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
        <div class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-purple dark:prose-invert max-w-none [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-purple-600 [&_h2]:dark:text-purple-400 [&_h2]:mt-6 [&_h2]:mb-4 [&_h2]:first:mt-0 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-gray-900 [&_h3]:dark:text-white [&_h3]:mt-5 [&_h3]:mb-3 [&_p]:mb-4 [&_p]:leading-relaxed [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-4 [&_ul]:space-y-2 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-4 [&_ol]:space-y-2 [&_li]:mb-1 [&_strong]:font-semibold [&_strong]:text-gray-900 [&_strong]:dark:text-gray-100 [&_em]:italic">
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
























