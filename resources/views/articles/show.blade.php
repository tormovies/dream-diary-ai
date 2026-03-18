@extends('layouts.base')

@section('content')
    <!-- Основной контент -->
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="profile-grid w-full">
                <!-- Левая панель (только для авторизованных) -->
                @auth
                <aside class="space-y-6">
                    <!-- Приветственная карточка (как на главной, с кнопкой Толкование) -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            @if($article->type === 'guide')
                                Просмотр инструкции
                            @elseif($article->type === 'entity_group')
                                Толкование символа
                            @else
                                Просмотр статьи
                            @endif
                        </p>
                        <div class="flex flex-nowrap gap-2">
                            <a href="{{ route('reports.create') }}" class="flex-1 min-w-0 inline-flex items-center justify-center bg-white text-purple-600 px-3 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                                <i class="fas fa-plus mr-2 flex-shrink-0"></i><span class="truncate">Добавить сон</span>
                            </a>
                            <a href="https://www.snovidec.ru/tolkovanie-snov" class="flex-1 min-w-0 inline-flex items-center justify-center bg-white/90 text-purple-600 px-3 py-2 rounded-lg font-semibold hover:bg-white transition-colors text-sm">
                                <i class="fas fa-magic mr-2 flex-shrink-0"></i><span class="truncate">Толкование</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Быстрое меню -->
                    <x-auth-sidebar-menu />
                </aside>
                @else
                <!-- Левая панель для неавторизованных -->
                <aside class="space-y-6">
                    <!-- Приветственная карточка (как на главной, с кнопкой Толкование) -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Присоединяйтесь к сообществу людей, которые записывают и анализируют свои сновидения.
                        </p>
                        <div class="flex flex-nowrap gap-2">
                            <a href="{{ route('register') }}" class="flex-1 min-w-0 inline-flex items-center justify-center bg-white text-purple-600 px-3 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                                <i class="fas fa-user-plus mr-2 flex-shrink-0"></i><span class="truncate">Регистрация</span>
                            </a>
                            <a href="https://www.snovidec.ru/tolkovanie-snov" class="flex-1 min-w-0 inline-flex items-center justify-center bg-white/90 text-purple-600 px-3 py-2 rounded-lg font-semibold hover:bg-white transition-colors text-sm">
                                <i class="fas fa-magic mr-2 flex-shrink-0"></i><span class="truncate">Толкование</span>
                            </a>
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
                            @if(isset($breadcrumbs) && !empty($breadcrumbs))
                                <x-breadcrumbs :items="$breadcrumbs" />
                            @endif
                            <div class="mb-4">
                                <h1 class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $seo['h1'] ?? $article->title }}</h1>
                                @if(isset($seo['h1_description']) && !empty($seo['h1_description']))
                                    <p class="text-gray-600 dark:text-gray-300 mt-3 text-lg leading-relaxed">{{ $seo['h1_description'] }}</p>
                                @endif
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
                                        : ($article->type === 'guide' ? route('guide.index') : ($article->type === 'entity_group' ? route('symbol.index') : route('articles.index')));
                                @endphp
                                
                                <!-- Кнопка "Назад" -->
                                <a href="{{ $backUrl }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-arrow-left mr-2"></i>Назад
                                </a>
                                
                                <!-- Кнопка "Список инструкций/статей/символов" -->
                                <a href="{{ $article->type === 'guide' ? route('guide.index') : ($article->type === 'entity_group' ? route('symbol.index') : route('articles.index')) }}" 
                                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition-colors text-center w-full sm:w-auto sm:flex-1">
                                    <i class="fas fa-list mr-2"></i>{{ $article->type === 'guide' ? 'Все инструкции' : ($article->type === 'entity_group' ? 'Все символы' : 'Все статьи') }}
                                </a>
                            </div>
                        </div>
                    </div>


                    <!-- Содержимое статьи -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="prose dark:prose-invert max-w-none" style="min-height: 200px;">
                            {!! $article->content !!}
                        </div>
                    </div>

                    @if($article->type === 'entity_group' && isset($exampleInterpretations) && $exampleInterpretations->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Примеры толкований с символом {{ $article->title }}</h2>
                            <ul class="space-y-3">
                                @foreach($exampleInterpretations as $interp)
                                    <li>
                                        <a href="{{ route('dream-analyzer.show', $interp->hash) }}" class="text-purple-600 dark:text-purple-400 hover:underline font-medium">
                                            @if($interp->dream_description)
                                                {{ Str::limit(strip_tags($interp->dream_description), 80) }}
                                            @else
                                                Толкование от {{ $interp->created_at?->format('d.m.Y') ?? '—' }}
                                            @endif
                                        </a>
                                        @if($interp->created_at)
                                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $interp->created_at->format('d.m.Y') }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </main>
            </div>
        </div>
@endsection
