<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: 'light' }"
      x-bind:class="{ 'dark': theme === 'dark' }"
      x-init="
        const savedTheme = localStorage.getItem('theme') || 'light';
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
                if ($result) {
                    $isSeries = $result->type === 'series';
                } else {
                    $analysis = $interpretation->analysis_data ?? [];
                    $isSeries = isset($analysis['series_analysis']) && isset($analysis['dreams']);
                }
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
                            if ($result) {
                                $isSeries = $result->type === 'series';
                            } else {
                                $analysis = $interpretation->analysis_data ?? [];
                                $isSeries = isset($analysis['series_analysis']) && isset($analysis['dreams']);
                            }
                            $h1Text = $isSeries ? 'Расшифровка снов' : 'Расшифровка сна';
                        @endphp
                        <h1 class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-2">{{ $h1Text }}</h1>
                        <div class="flex flex-row items-center justify-between mb-4">
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Создано: {{ $interpretation->created_at->format('d.m.Y H:i') }}
                            </p>
                            <a href="{{ route('dream-analyzer.create') }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 whitespace-nowrap">
                                <i class="fas fa-plus mr-2"></i>Новое толкование
                            </a>
                        </div>
                        
                        <!-- Спойлер с исходным описанием сна и кнопка "Поделиться" -->
                        <div class="flex items-center justify-between gap-4 mt-4">
                            <details class="flex-1">
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
                            
                            <!-- Кнопка "Поделиться" (Вариант 1) -->
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
                        </div>
                    </div>

                @if($interpretation->api_error)
                    <!-- Ошибка API -->
                    <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-6 py-4 rounded-lg">
                        <h2 class="font-bold text-lg mb-2">Ошибка при анализе</h2>
                        <p>{{ $interpretation->api_error }}</p>
                        @if(isset($interpretation->raw_api_response) && $interpretation->raw_api_response)
                            <details class="mt-4">
                                <summary class="cursor-pointer font-semibold">Ответ API</summary>
                                <pre class="mt-2 text-xs overflow-auto bg-red-50 dark:bg-red-950 p-4 rounded">{{ $interpretation->raw_api_response }}</pre>
                            </details>
                        @endif
                    </div>
                @else
                    @php
                        // Приоритет: сначала нормализованные данные, потом старые
                        $result = $interpretation->result;
                        
                        // Проверяем, есть ли реальные данные в result
                        $hasResultData = $result && (
                            (is_array($result->general_interpretation) && count($result->general_interpretation) > 0) ||
                            (is_array($result->key_symbols) && count($result->key_symbols) > 0) ||
                            (is_array($result->emotional_state) && count($result->emotional_state) > 0) ||
                            (is_array($result->practical_recommendations) && count($result->practical_recommendations) > 0)
                        );
                        
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
                        @if($isSeries)
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
                                    @php
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
                                    @foreach($analysis['analysis']['traditions'] as $tradition)
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
                        @elseif(isset($analysis['analysis']['tradition']))
                            <div class="mb-4">
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
                                    $traditionKey = strtolower($analysis['analysis']['tradition']);
                                    $traditionName = $traditionNames[$traditionKey] ?? $analysis['analysis']['tradition'];
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
                                            $traditionNames = [
                                                'symbolic' => 'символической',
                                                'shamanic' => 'шаманской',
                                                'gestalt' => 'гештальта',
                                                'freudian' => 'фрейдистской',
                                                'jungian' => 'юнгианской',
                                                'cognitive' => 'когнитивной',
                                                'lucid_centered' => 'практики осознанных сновидений',
                                            ];
                                            
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
                                            {{ $analysis['metadata']['dream_type'] }}
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
                                    
                                    @if(isset($interpretation->raw_api_request) && $interpretation->raw_api_request)
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">JSON запрос к API:</h4>
                                            <details class="cursor-pointer">
                                                <summary class="text-purple-600 dark:text-purple-400 hover:underline mb-2">Показать/скрыть JSON</summary>
                                                <pre class="bg-gray-800 dark:bg-gray-950 text-blue-400 p-4 rounded-lg overflow-auto text-xs max-h-96">{{ $interpretation->raw_api_request }}</pre>
                                            </details>
                                        </div>
                                    @endif
                                    
                                    @if(isset($interpretation->raw_api_response) && $interpretation->raw_api_response)
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Raw JSON ответ от API:</h4>
                                            <details class="cursor-pointer">
                                                <summary class="text-purple-600 dark:text-purple-400 hover:underline mb-2">Показать/скрыть JSON</summary>
                                                <pre class="bg-gray-800 dark:bg-gray-950 text-green-400 p-4 rounded-lg overflow-auto text-xs max-h-96">{{ $interpretation->raw_api_response }}</pre>
                                            </details>
                                        </div>
                                    @endif
                                    
                                    @if(isset($interpretation->analysis_data) && $interpretation->analysis_data)
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Распарсенный JSON (analysis_data):</h4>
                                            <details class="cursor-pointer">
                                                <summary class="text-purple-600 dark:text-purple-400 hover:underline mb-2">Показать/скрыть JSON</summary>
                                                <pre class="bg-gray-800 dark:bg-gray-950 text-green-400 p-4 rounded-lg overflow-auto text-xs max-h-96">{{ json_encode($interpretation->analysis_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </details>
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



























