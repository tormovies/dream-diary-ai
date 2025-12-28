@php
    $seriesDreams = $result->seriesDreams;
@endphp

<!-- Заголовок серии -->
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-4">
        {{ $result->series_title ?? 'Анализ серии снов' }}
    </h2>
    
    @if($result->traditions && count($result->traditions) > 0)
        <div class="mb-4">
            <div class="flex items-center flex-wrap gap-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Традиции анализа:</span>
                @foreach($result->traditions as $tradition)
                    @php
                        $traditionKey = strtolower($tradition);
                        $traditionName = config("traditions.{$traditionKey}.name_short", ucfirst($tradition));
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
            <p class="text-gray-700 dark:text-gray-300">{{ $result->overall_theme }}</p>
        </div>
    @endif

    @if($result->emotional_arc)
        <div class="mb-4 p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg border-l-4 border-indigo-500">
            <h3 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">Эмоциональная дуга</h3>
            <p class="text-gray-700 dark:text-gray-300">{{ $result->emotional_arc }}</p>
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

<!-- Анализ каждого сна -->
@if($seriesDreams && $seriesDreams->count() > 0)
    @foreach($seriesDreams as $dream)
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                Сон {{ $dream->dream_number }}: {{ $dream->dream_title ?? 'Без названия' }}
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
                @php
                    $detailedText = $dream->dream_detailed;
                    $lines = explode("\n", $detailedText);
                    $processedLines = [];
                    foreach ($lines as $line) {
                        $cleanedLine = trim($line);
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

            @if($dream->summary_insight)
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border-l-4 border-blue-500">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Ключевая мысль</h4>
                    <p class="text-gray-700 dark:text-gray-300">{{ $dream->summary_insight }}</p>
                </div>
            @endif

            @if($dream->emotional_tone)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Эмоциональный тон</h4>
                    <p class="text-gray-700 dark:text-gray-300">{{ $dream->emotional_tone }}</p>
                </div>
            @endif

            @if($dream->key_symbols && count($dream->key_symbols) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Ключевые символы</h4>
                    <div class="space-y-2">
                        @foreach($dream->key_symbols as $symbol)
                            <div class="border-l-4 border-indigo-500 pl-4">
                                <h5 class="font-semibold text-indigo-800 dark:text-indigo-200">{{ $symbol['symbol'] ?? 'Символ' }}</h5>
                                @if(isset($symbol['meaning']))
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $symbol['meaning'] }}</p>
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
            <i class="fas fa-link mr-2"></i>Копировать
        </button>
    </div>
</div>
























