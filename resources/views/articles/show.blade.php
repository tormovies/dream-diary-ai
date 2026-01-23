<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Resource Hints для оптимизации загрузки -->
        <link rel="preconnect" href="https://top-fwz1.mail.ru" crossorigin>
        <link rel="dns-prefetch" href="https://top-fwz1.mail.ru">
        
        <!-- Preload критических ресурсов -->
        <x-preload-assets />
        
        @if(isset($seo))
            <x-seo-head :seo="$seo" />
        @else
            <title>{{ $article->title }} - {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="profile-grid w-full">
                <!-- Левая панель (только для авторизованных) -->
                @auth
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            {{ $article->type === 'guide' ? 'Просмотр инструкции' : 'Просмотр статьи' }}
                        </p>
                        <a href="{{ route('reports.create') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>Добавить сон
                        </a>
                    </div>
                    
                    <!-- Карточка пользователя -->
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
                </aside>
                @else
                <!-- Левая панель для неавторизованных -->
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Присоединяйтесь к сообществу людей, которые записывают и анализируют свои сновидения.
                        </p>
                        <a href="{{ route('register') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                            <i class="fas fa-user-plus mr-2"></i>Регистрация
                        </a>
                    </div>
                    
                    <!-- Статистика проекта -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-chart-bar"></i> Статистика проекта
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Пользователей</span>
                                <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ number_format($globalStats['users'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Отчетов</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($globalStats['reports'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Снов</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($globalStats['dreams'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Комментариев</span>
                                <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($globalStats['comments'], 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Тегов</span>
                                <span class="text-lg font-bold text-pink-600 dark:text-pink-400">{{ number_format($globalStats['tags'], 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Быстрые действия -->
                    <x-guest-quick-actions />
                </aside>
                @endauth
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок и кнопки действий -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="mb-4">
                            <div class="mb-4">
                                <h1 class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $seo['h1'] ?? $article->title }}</h1>
                                @if(isset($seo['h1_description']) && !empty($seo['h1_description']))
                                    <p class="text-gray-600 dark:text-gray-300 mt-3 text-lg leading-relaxed">{{ $seo['h1_description'] }}</p>
                                @endif
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="text-xs px-2 py-1 rounded {{ $article->type === 'guide' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300' }}">
                                        {{ $article->type === 'guide' ? 'Инструкция' : 'Статья' }}
                                    </span>
                                    <span class="text-xs px-2 py-1 rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">
                                        Опубликовано
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2 sm:items-stretch">
                                @php
                                    // Получаем предыдущий URL, если он с нашего сайта
                                    $previousUrl = url()->previous();
                                    $currentHost = parse_url(url('/'), PHP_URL_HOST);
                                    $previousHost = parse_url($previousUrl, PHP_URL_HOST);
                                    
                                    // Если предыдущий URL с нашего сайта, используем его, иначе соответствующая стартовая страница
                                    $backUrl = ($previousHost === $currentHost && $previousUrl !== url()->current()) 
                                        ? $previousUrl 
                                        : ($article->type === 'guide' ? route('guide.index') : route('articles.index'));
                                @endphp
                                
                                <!-- Кнопка "Назад" -->
                                <a href="{{ $backUrl }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-arrow-left mr-2"></i>Назад
                                </a>
                                
                                <!-- Кнопка "Список инструкций/статей" -->
                                <a href="{{ $article->type === 'guide' ? route('guide.index') : route('articles.index') }}" 
                                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-list mr-2"></i>{{ $article->type === 'guide' ? 'Все инструкции' : 'Все статьи' }}
                                </a>
                            </div>
                        </div>
                    </div>


                    <!-- Содержимое статьи -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <style>
                            /* Улучшенные стили для светлой темы - темные цвета для читабельности */
                            .prose {
                                color: #1f2937 !important;
                            }
                            .prose ul,
                            .prose ol {
                                color: #1f2937 !important;
                            }
                            .prose li {
                                color: #1f2937 !important;
                            }
                            .prose p {
                                color: #1f2937 !important;
                            }
                            .prose strong {
                                color: #111827 !important;
                            }
                            .prose a {
                                color: #7c3aed !important;
                            }
                            .prose a:hover {
                                color: #6d28d9 !important;
                            }
                            /* Списки в светлой теме - темные цвета */
                            .prose ul li,
                            .prose ol li {
                                color: #1f2937 !important;
                            }
                            .prose ul li::marker,
                            .prose ol li::marker {
                                color: #4b5563 !important;
                            }
                            /* Все текстовые элементы в светлой теме - темные */
                            .prose h1,
                            .prose h2,
                            .prose h3,
                            .prose h4,
                            .prose h5,
                            .prose h6 {
                                color: #111827 !important;
                            }
                            /* Ровные отступы для заголовков h2 */
                            .prose h2 {
                                margin-top: 2.5rem !important;
                                margin-bottom: 1rem !important;
                            }
                            .prose h2:first-child {
                                margin-top: 0 !important;
                            }
                            /* Ровные отступы для заголовков вопросов (div с классом question-header) */
                            .prose .question-header {
                                margin-top: 1.5rem !important;
                                margin-bottom: 1.5rem !important;
                            }
                            .prose .question-header:first-child {
                                margin-top: 0 !important;
                            }
                            /* Переопределяем mt-10, если он остался в старых записях */
                            .prose div.mt-10 {
                                margin-top: 1.5rem !important;
                                margin-bottom: 1.5rem !important;
                            }
                            .prose div.mt-10:first-child {
                                margin-top: 0 !important;
                            }
                            .prose blockquote {
                                color: #374151 !important;
                            }
                            .prose code {
                                color: #1f2937 !important;
                                background-color: #f3f4f6 !important;
                            }
                            /* Убираем светлые серые цвета в светлой теме */
                            .prose .text-gray-300,
                            .prose .text-gray-400,
                            .prose .text-gray-500 {
                                color: #1f2937 !important;
                            }
                            /* Специально для элементов с классами Tailwind в контенте */
                            .prose [class*="text-gray-300"],
                            .prose [class*="text-gray-400"],
                            .prose [class*="text-gray-500"] {
                                color: #1f2937 !important;
                            }
                            
                            /* Улучшенные стили для темной темы в статьях */
                            .prose.dark\:prose-invert {
                                color: #e5e7eb;
                            }
                            .prose.dark\:prose-invert ul,
                            .prose.dark\:prose-invert ol {
                                color: #d1d5db;
                            }
                            .prose.dark\:prose-invert li {
                                color: #d1d5db;
                            }
                            .prose.dark\:prose-invert a {
                                color: #a78bfa;
                            }
                            .prose.dark\:prose-invert a:hover {
                                color: #c4b5fd;
                            }
                            .prose.dark\:prose-invert strong {
                                color: #e5e7eb;
                            }
                            .prose.dark\:prose-invert code {
                                background-color: #374151;
                                color: #f3f4f6;
                            }
                            /* Улучшение цветов для списков в темной теме - более мягкие */
                            .dark .prose ul li,
                            .dark .prose ol li {
                                color: #d1d5db !important;
                            }
                            .dark .prose ul li::marker,
                            .dark .prose ol li::marker {
                                color: #9ca3af;
                            }
                            /* Улучшение цветов для карточек в темной теме */
                            .dark .bg-white {
                                background-color: #1f2937 !important;
                            }
                            .dark .text-gray-700 {
                                color: #d1d5db !important;
                            }
                            .dark .text-gray-300 {
                                color: #d1d5db !important;
                            }
                            /* Улучшение цветов для градиентов в темной теме - более темные и мягкие */
                            .dark .from-purple-50,
                            .dark .to-blue-50,
                            .dark .from-purple-900\/30,
                            .dark .to-blue-900\/30,
                            .dark .from-gray-700\/50,
                            .dark .to-gray-700\/50 {
                                background-color: #374151 !important;
                            }
                            .dark .from-purple-100,
                            .dark .to-blue-100,
                            .dark .from-purple-800\/40,
                            .dark .to-blue-800\/40,
                            .dark .from-gray-600\/60,
                            .dark .to-gray-600\/60 {
                                background-color: #4b5563 !important;
                            }
                            .dark .from-gray-800,
                            .dark .via-gray-800,
                            .dark .to-gray-800,
                            .dark .from-gray-800\/80,
                            .dark .via-gray-800\/80,
                            .dark .to-gray-800\/80 {
                                background-color: #374151 !important;
                            }
                            /* Специально для списка вопросов - очень темный фон, убираем ядерные цвета */
                            .dark .bg-gradient-to-r.from-purple-50,
                            .dark .bg-gradient-to-r.to-blue-50,
                            .dark .bg-gradient-to-r.from-gray-700\/50,
                            .dark .bg-gradient-to-r.to-gray-700\/50,
                            .dark .bg-gradient-to-r.from-gray-700,
                            .dark .bg-gradient-to-r.to-gray-700 {
                                background: #374151 !important;
                                background-image: none !important;
                            }
                            .dark .bg-gradient-to-br.from-purple-50,
                            .dark .bg-gradient-to-br.from-blue-50,
                            .dark .bg-gradient-to-br.via-blue-50,
                            .dark .dark .bg-gradient-to-br.to-indigo-50,
                            .dark .bg-gradient-to-br.from-gray-800\/80,
                            .dark .bg-gradient-to-br.via-gray-800\/80,
                            .dark .bg-gradient-to-br.to-gray-800\/80,
                            .dark .bg-gradient-to-br.from-gray-800,
                            .dark .bg-gradient-to-br.via-gray-800,
                            .dark .bg-gradient-to-br.to-gray-800 {
                                background: #374151 !important;
                                background-image: none !important;
                            }
                            /* Переопределяем все градиенты в темной теме на темно-серый - агрессивно */
                            .dark [class*="bg-gradient"] {
                                background-image: none !important;
                            }
                            /* Убираем все градиенты и заменяем на темный фон */
                            .dark .bg-gradient-to-r,
                            .dark .bg-gradient-to-br {
                                background: #374151 !important;
                                background-image: none !important;
                            }
                            .dark [class*="from-purple-50"],
                            .dark [class*="to-blue-50"],
                            .dark [class*="via-blue-50"],
                            .dark [class*="to-indigo-50"],
                            .dark [class*="from-gray-700"],
                            .dark [class*="to-gray-700"],
                            .dark [class*="from-gray-800"],
                            .dark [class*="to-gray-800"] {
                                background-color: #374151 !important;
                                background-image: none !important;
                            }
                            .dark [class*="from-purple-100"],
                            .dark [class*="to-blue-100"],
                            .dark [class*="from-gray-600"],
                            .dark [class*="to-gray-600"] {
                                background-color: #4b5563 !important;
                                background-image: none !important;
                            }
                            /* Специально для ссылок в списке вопросов */
                            .dark a[class*="bg-gradient"] {
                                background: #374151 !important;
                                background-image: none !important;
                            }
                            .dark a[class*="bg-gradient"]:hover {
                                background: #4b5563 !important;
                                background-image: none !important;
                            }
                            /* Мягкие цвета для текста в списке вопросов */
                            .dark a[href^="#question-"] {
                                color: #d1d5db !important;
                            }
                            .dark a[href^="#question-"]:hover {
                                color: #f3f4f6 !important;
                            }
                            /* Мягкие цвета для иконок в списке вопросов */
                            .dark a[href^="#question-"] .fa-arrow-right {
                                color: #9ca3af !important;
                            }
                            /* Мягкие цвета для заголовка "Содержание" */
                            .dark h2.text-purple-700 {
                                color: #d1d5db !important;
                            }
                            .dark h2 .fa-list-ul {
                                color: #9ca3af !important;
                            }
                            /* Улучшение цветов для списков вопросов в темной теме */
                            .dark .list-disc li,
                            .dark .list-decimal li {
                                color: #d1d5db !important;
                            }
                            /* Улучшение цветов для выделенных элементов в темной теме - более мягкие */
                            .dark .text-purple-600 {
                                color: #a78bfa !important;
                            }
                            .dark .text-purple-400 {
                                color: #c4b5fd !important;
                            }
                            .dark .text-purple-700 {
                                color: #c4b5fd !important;
                            }
                            .dark .text-purple-300 {
                                color: #c4b5fd !important;
                            }
                            .dark .text-purple-900 {
                                color: #e9d5ff !important;
                            }
                            .dark .text-purple-100 {
                                color: #e9d5ff !important;
                            }
                            /* Улучшение цветов для границ в темной теме - более мягкие */
                            .dark .border-purple-500 {
                                border-color: #6d28d9 !important;
                            }
                            .dark .border-purple-400 {
                                border-color: #8b5cf6 !important;
                            }
                            .dark .border-purple-800 {
                                border-color: #5b21b6 !important;
                            }
                            .dark .border-purple-700 {
                                border-color: #6d28d9 !important;
                            }
                            /* Улучшение цветов для информационных блоков в темной теме - более мягкие */
                            .dark .bg-yellow-50,
                            .dark .bg-yellow-900\/20 {
                                background-color: #78350f !important;
                            }
                            .dark .bg-blue-50,
                            .dark .bg-blue-900\/20 {
                                background-color: #1e3a8a !important;
                            }
                            .dark .bg-green-50,
                            .dark .bg-green-900\/20 {
                                background-color: #14532d !important;
                            }
                            .dark .bg-red-50,
                            .dark .bg-red-900\/20 {
                                background-color: #7f1d1d !important;
                            }
                            .dark .text-yellow-800,
                            .dark .text-yellow-300 {
                                color: #fbbf24 !important;
                            }
                            .dark .text-blue-800,
                            .dark .text-blue-300 {
                                color: #93c5fd !important;
                            }
                            .dark .text-green-800,
                            .dark .text-green-300 {
                                color: #86efac !important;
                            }
                            .dark .text-red-800,
                            .dark .text-red-300 {
                                color: #fca5a5 !important;
                            }
                            /* Улучшение цветов для иконок в темной теме */
                            .dark .text-purple-500 {
                                color: #a78bfa !important;
                            }
                            
                            /* Ровные отступы для заголовков h2 в темной теме */
                            .dark .prose h2 {
                                margin-top: 2.5rem !important;
                                margin-bottom: 1rem !important;
                            }
                            .dark .prose h2:first-child {
                                margin-top: 0 !important;
                            }
                            /* Для h2 внутри div - не добавляем дополнительный отступ сверху */
                            .dark .prose > div > h2 {
                                margin-top: 0 !important;
                            }
                            /* Но для обычных h2 (не в div) сохраняем отступы */
                            .dark .prose > h2 {
                                margin-top: 2.5rem !important;
                            }
                            .dark .prose > h2:first-child {
                                margin-top: 0 !important;
                            }
                            /* Ровные отступы для заголовков вопросов в темной теме */
                            .dark .prose .question-header {
                                margin-top: 1.5rem !important;
                                margin-bottom: 1.5rem !important;
                            }
                            .dark .prose .question-header:first-child {
                                margin-top: 0 !important;
                            }
                            /* Переопределяем mt-10 в темной теме, если он остался в старых записях */
                            .dark .prose div.mt-10 {
                                margin-top: 1.5rem !important;
                                margin-bottom: 1.5rem !important;
                            }
                            .dark .prose div.mt-10:first-child {
                                margin-top: 0 !important;
                            }
                            
                            /* Плавная прокрутка для всех якорей */
                            html {
                                scroll-behavior: smooth;
                            }
                            
                            /* Правильное позиционирование заголовков вопросов при переходе по якорям */
                            /* Учитываем высоту фиксированного хедера (примерно 80-100px) */
                            h2[id^="question-"] {
                                scroll-margin-top: 120px;
                                position: relative;
                                transition: all 0.3s ease;
                            }
                            
                            /* Визуальное выделение заголовка при переходе по якорю */
                            h2[id^="question-"]:target {
                                animation: highlightQuestion 2s ease-in-out;
                            }
                            
                            @keyframes highlightQuestion {
                                0% {
                                    background-color: rgba(139, 92, 246, 0.2);
                                    padding-left: 1rem;
                                    padding-right: 1rem;
                                    margin-left: -1rem;
                                    margin-right: -1rem;
                                    border-radius: 0.5rem;
                                }
                                50% {
                                    background-color: rgba(139, 92, 246, 0.3);
                                }
                                100% {
                                    background-color: transparent;
                                    padding-left: 0;
                                    padding-right: 0;
                                    margin-left: 0;
                                    margin-right: 0;
                                }
                            }
                            
                            /* Для темной темы - более мягкое выделение */
                            .dark h2[id^="question-"]:target {
                                animation: highlightQuestionDark 2s ease-in-out;
                            }
                            
                            @keyframes highlightQuestionDark {
                                0% {
                                    background-color: rgba(139, 92, 246, 0.15);
                                    padding-left: 1rem;
                                    padding-right: 1rem;
                                    margin-left: -1rem;
                                    margin-right: -1rem;
                                    border-radius: 0.5rem;
                                }
                                50% {
                                    background-color: rgba(139, 92, 246, 0.25);
                                }
                                100% {
                                    background-color: transparent;
                                    padding-left: 0;
                                    padding-right: 0;
                                    margin-left: 0;
                                    margin-right: 0;
                                }
                            }
                        </style>
                        <div class="prose dark:prose-invert max-w-none">
                            {!! $article->content !!}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
