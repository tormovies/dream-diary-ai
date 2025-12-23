<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Мои отчеты') }}
            </h2>
            <a href="{{ route('reports.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Создать отчет') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Форма поиска и фильтров -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" 
                 x-data="{ open: {{ request()->hasAny(['search', 'tags', 'dream_type', 'date_from', 'date_to', 'sort_by', 'sort_order', 'per_page']) ? 'true' : 'false' }} }">
                <div class="p-4 border-b border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors"
                     @click="open = !open">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            🔍 Поиск и фильтры
                        </h3>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" 
                             :class="{ 'rotate-180': open }"
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="p-6">
                    <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                        <!-- Поиск по тексту -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Поиск по тексту</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Поиск по названию или описанию снов..."
                                   class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Фильтр по тегам -->
                            <div>
                                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Теги</label>
                                <select id="tags" 
                                        name="tags[]" 
                                        multiple
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        size="5">
                                    @foreach($allTags as $tag)
                                        <option value="{{ $tag->id }}" 
                                                {{ in_array($tag->id, (array)request('tags', [])) ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Удерживайте Ctrl для выбора нескольких</p>
                            </div>

                            <!-- Фильтр по типу сна -->
                            <div>
                                <label for="dream_type" class="block text-sm font-medium text-gray-700 mb-1">Тип сна</label>
                                <select id="dream_type" 
                                        name="dream_type" 
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Все типы</option>
                                    @foreach($dreamTypes as $type)
                                        <option value="{{ $type }}" {{ request('dream_type') === $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Фильтр по дате (от) -->
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Дата от</label>
                                <input type="date" 
                                       id="date_from" 
                                       name="date_from" 
                                       value="{{ request('date_from') }}"
                                       class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>

                            <!-- Фильтр по дате (до) -->
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Дата до</label>
                                <input type="date" 
                                       id="date_to" 
                                       name="date_to" 
                                       value="{{ request('date_to') }}"
                                       class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Фильтр по статусу -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                                <select id="status" 
                                        name="status" 
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Все</option>
                                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Опубликованные</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Черновики</option>
                                </select>
                            </div>

                            <!-- Сортировка -->
                            <div>
                                <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Сортировать по</label>
                                <select id="sort_by" 
                                        name="sort_by" 
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="report_date" {{ request('sort_by', 'report_date') === 'report_date' ? 'selected' : '' }}>Дате отчета</option>
                                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Дате создания</option>
                                </select>
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Порядок</label>
                                <select id="sort_order" 
                                        name="sort_order" 
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>По убыванию</option>
                                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                                </select>
                            </div>

                            <div>
                                <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">На странице</label>
                                <select id="per_page" 
                                        name="per_page" 
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Применить фильтры
                            </button>
                            <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Сбросить
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($reports->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($reports as $report)
                        <div class="bg-white shadow-sm sm:rounded-lg relative">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $report->report_date->format('d.m.Y') }}
                                        </h3>
                                        <span class="text-xs px-2 py-1 rounded mt-1 inline-block
                                            @if($report->status === 'published') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($report->status === 'published') Опубликован
                                            @else Черновик
                                            @endif
                                        </span>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded 
                                        @if($report->access_level === 'all') bg-green-100 text-green-800
                                        @elseif($report->access_level === 'friends') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @if($report->access_level === 'all') Всем
                                        @elseif($report->access_level === 'friends') Друзьям
                                        @else Никому
                                        @endif
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-3">
                                    Снов: {{ $report->dreams->count() }}
                                </p>
                                
                                <!-- Названия снов -->
                                @if($report->dreams->count() > 0)
                                    <div class="mb-4 space-y-2">
                                        @php
                                            $dreamsWithTitles = $report->dreams->filter(function($dream) {
                                                return !empty($dream->title);
                                            })->take(4);
                                        @endphp
                                        @foreach($dreamsWithTitles as $index => $dream)
                                            <div class="flex items-center p-2 bg-gray-50 rounded border-l-2 border-blue-400 gap-2">
                                                <span class="text-xs font-bold text-blue-600 min-w-[28px] flex-shrink-0 text-center">#{{ $index + 1 }}</span>
                                                <span class="text-sm text-gray-900 flex-1">{{ $dream->title }}</span>
                                            </div>
                                        @endforeach
                                        @if($report->dreams->count() > $dreamsWithTitles->count())
                                            <p class="text-xs text-gray-500 italic pl-2">
                                                ... и еще {{ $report->dreams->count() - $dreamsWithTitles->count() }} {{ ($report->dreams->count() - $dreamsWithTitles->count()) == 1 ? 'сон' : 'снов' }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                                
                                @if($report->tags->count() > 0)
                                    <div class="flex flex-wrap gap-1 mb-4">
                                        @foreach($report->tags as $tag)
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <!-- Кнопки публикации/снятия с публикации -->
                                    <div class="flex gap-2">
                                        @if($report->status === 'draft')
                                            <form action="{{ route('reports.publish', $report) }}" 
                                                  method="POST" 
                                                  class="inline flex-1">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded transition-colors">
                                                    <i class="fas fa-eye mr-1"></i>Опубликовать
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('reports.unpublish', $report) }}" 
                                                  method="POST" 
                                                  class="inline flex-1">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full bg-gray-500 hover:bg-gray-700 text-white text-sm font-medium py-2 px-3 rounded transition-colors">
                                                    <i class="fas fa-eye-slash mr-1"></i>Снять с публикации
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    
                                    <!-- Остальные действия -->
                                    <div class="flex gap-2 items-center justify-between pt-2 border-t border-gray-200">
                                        <div class="flex gap-2">
                                            <a href="{{ route('reports.show', $report) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                Просмотр
                                            </a>
                                            <a href="{{ route('reports.edit', $report) }}" 
                                               class="text-green-600 hover:text-green-800 text-sm">
                                                Редактировать
                                            </a>
                                        </div>
                                        
                                        <!-- Кнопка удаления -->
                                        <form action="{{ route('reports.destroy', $report) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Вы уверены, что хотите удалить этот отчет?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $reports->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="mb-4">У вас пока нет отчетов.</p>
                        <a href="{{ route('reports.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Создать первый отчет
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

