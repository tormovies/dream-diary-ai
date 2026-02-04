<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Управление SEO') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.seo.sitemap') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-sitemap mr-2"></i>Управление Sitemap
                </a>
                <a href="{{ route('admin.seo.redirects.index') }}" class="inline-block bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded no-underline" style="background-color:#2563eb;color:#fff;">
                    <i class="fas fa-exchange-alt mr-2"></i>301 редиректы
                </a>
                <a href="{{ route('admin.seo.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Создать
                </a>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Назад
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Поиск и фильтры -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.seo.index') }}" class="flex gap-4 flex-wrap">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Поиск по title, description, h1..."
                               class="flex-1 min-w-[200px] block border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <select name="page_type" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Все типы страниц</option>
                            @foreach($pageTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('page_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Найти
                        </button>
                    </form>
                </div>
            </div>

            <!-- Список SEO-записей -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    <a href="{{ route('admin.seo.index', array_merge(request()->query(), ['sort_by' => 'id', 'sort_order' => request('sort_by') === 'id' && request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                        ID
                                        @if(request('sort_by') === 'id')
                                            @if(request('sort_order') === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort text-gray-400"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Тип</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Приоритет</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Статус</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($seoMetas as $seo)
                                @php
                                    // Формируем ссылку для каждого типа страницы
                                    $pageLink = null;
                                    
                                    switch ($seo->page_type) {
                                        case 'home':
                                            $pageLink = route('home');
                                            break;
                                            
                                        case 'report':
                                            if ($seo->page_id && isset($reports[$seo->page_id])) {
                                                $pageLink = route('reports.show', $seo->page_id);
                                            }
                                            break;
                                            
                                        case 'profile':
                                            if ($seo->page_id && isset($users[$seo->page_id])) {
                                                $user = $users[$seo->page_id];
                                                $pageLink = route('users.profile', $seo->page_id);
                                            }
                                            break;
                                            
                                        case 'diary':
                                            if ($seo->page_id && isset($users[$seo->page_id])) {
                                                $user = $users[$seo->page_id];
                                                if ($user->public_link) {
                                                    $pageLink = route('diary.public', $user->public_link);
                                                }
                                            }
                                            break;
                                            
                                        case 'search':
                                            $pageLink = route('reports.search');
                                            break;
                                            
                                        case 'activity':
                                            $pageLink = route('activity.index');
                                            break;
                                            
                                        case 'users':
                                            $pageLink = route('users.search');
                                            break;
                                            
                                        case 'dashboard':
                                            $pageLink = route('dashboard');
                                            break;
                                            
                                        case 'statistics':
                                            $pageLink = route('statistics.index');
                                            break;
                                            
                                        case 'notifications':
                                            $pageLink = route('notifications.index');
                                            break;
                                            
                                        case 'dream-analyzer':
                                            $pageLink = route('dream-analyzer.create');
                                            break;
                                            
                                        case 'dream-analyzer-result':
                                            if ($seo->page_id && isset($interpretations[$seo->page_id])) {
                                                $interpretation = $interpretations[$seo->page_id];
                                                if ($interpretation && $interpretation->hash) {
                                                    $pageLink = route('dream-analyzer.show', ['hash' => $interpretation->hash]);
                                                }
                                            }
                                            break;
                                            
                                        case 'report-analysis':
                                            if ($seo->page_id) {
                                                // Получаем интерпретацию (работает для коллекций и массивов)
                                                try {
                                                    $interpretation = $interpretations[$seo->page_id] ?? null;
                                                    if ($interpretation && isset($interpretation->report_id) && $interpretation->report_id) {
                                                        // Формируем ссылку напрямую по report_id
                                                        $pageLink = route('reports.analysis', $interpretation->report_id);
                                                    }
                                                } catch (\Exception $e) {
                                                    // Если возникла ошибка, просто не создаем ссылку
                                                }
                                            }
                                            break;
                                            
                                        case 'guide-index':
                                            $pageLink = route('guide.index');
                                            break;
                                            
                                        case 'articles-index':
                                            $pageLink = route('articles.index');
                                            break;
                                            
                                        case 'guide':
                                            if ($seo->page_id && isset($articles[$seo->page_id])) {
                                                $article = $articles[$seo->page_id];
                                                if ($article && $article->slug) {
                                                    $pageLink = route('guide.show', $article->slug);
                                                }
                                            }
                                            break;
                                            
                                        case 'article':
                                            if ($seo->page_id && isset($articles[$seo->page_id])) {
                                                $article = $articles[$seo->page_id];
                                                if ($article && $article->slug) {
                                                    $pageLink = route('articles.show', $article->slug);
                                                }
                                            }
                                            break;
                                    }
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $seo->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $pageTypes[$seo->page_type] ?? $seo->page_type }}
                                        </div>
                                        @if($seo->page_id)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $seo->page_id }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($pageLink)
                                            <a href="{{ $pageLink }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                                                {{ Str::limit($seo->title ?? '—', 50) }}
                                                <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                                            </a>
                                        @else
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ Str::limit($seo->title ?? '—', 50) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $seo->priority }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded {{ $seo->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $seo->is_active ? 'Активна' : 'Неактивна' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.seo.edit', $seo) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 mr-3">Редактировать</a>
                                        <form method="POST" action="{{ route('admin.seo.destroy', $seo) }}" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту SEO-запись?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        SEO-записи не найдены. <a href="{{ route('admin.seo.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Создать первую запись</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $seoMetas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



























