<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Управление статьями и инструкциями') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.articles.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Создать статью
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
                    <form method="GET" action="{{ route('admin.articles.index') }}" class="flex gap-4 flex-wrap">
                        <input type="text" 
                               name="search" 
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="Поиск по заголовку или содержимому..."
                               class="flex-1 min-w-[200px] block border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <select name="type" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Все типы</option>
                            <option value="guide" {{ ($filters['type'] ?? '') === 'guide' ? 'selected' : '' }}>Инструкции</option>
                            <option value="article" {{ ($filters['type'] ?? '') === 'article' ? 'selected' : '' }}>Статьи</option>
                        </select>
                        <select name="status" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Все статусы</option>
                            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Опубликовано</option>
                            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Черновик</option>
                        </select>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Найти
                        </button>
                    </form>
                </div>
            </div>

            <!-- Список статей -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Порядок</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Заголовок</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Тип</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Статус</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Автор</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Дата</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Действия</th>
                            </tr>
                        </thead>
                        <tbody id="articles-list" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($articles as $article)
                                <tr data-id="{{ $article->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 drag-handle cursor-move">
                                        <i class="fas fa-grip-vertical text-gray-400"></i> {{ $article->order }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $article->title }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            /{{ $article->type }}/{{ $article->slug }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded {{ $article->type === 'guide' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' }}">
                                            {{ $article->type === 'guide' ? 'Инструкция' : 'Статья' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded {{ $article->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $article->status === 'published' ? 'Опубликовано' : 'Черновик' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $article->author->nickname ?? $article->author->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $article->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-3 items-center">
                                            <a href="{{ $article->type === 'guide' ? route('guide.show', $article->slug) : route('articles.show', $article->slug) }}" 
                                               target="_blank"
                                               class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" 
                                               title="Просмотр">
                                                <i class="fas fa-eye fa-2x"></i>
                                            </a>
                                            <a href="{{ route('admin.articles.edit', $article) }}" 
                                               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300" 
                                               title="Редактировать">
                                                <i class="fas fa-edit fa-2x"></i>
                                            </a>
                                            @if($article->status === 'published')
                                                <form method="POST" action="{{ route('admin.articles.unpublish', $article) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300" title="В черновик">
                                                        <i class="fas fa-file-alt fa-2x"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.articles.publish', $article) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300" title="Опубликовать">
                                                        <i class="fas fa-check fa-2x"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту статью? Это действие нельзя отменить.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Удалить">
                                                    <i class="fas fa-trash fa-2x"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        Статьи не найдены. <a href="{{ route('admin.articles.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Создать первую статью</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $articles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .drag-handle {
            cursor: grab;
            user-select: none;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .drag-handle:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .dark .drag-handle:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .sortable-ghost {
            opacity: 0.4;
            background-color: #e5e7eb;
        }
        .dark .sortable-ghost {
            background-color: #374151;
        }
        .sortable-chosen {
            background-color: #dbeafe;
        }
        .dark .sortable-chosen {
            background-color: #1e3a5f;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/sortablejs/Sortable.min.js') }}" onload="console.log('SortableJS loaded')" onerror="console.error('Failed to load SortableJS')"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing SortableJS...');
            console.log('Sortable available:', typeof Sortable !== 'undefined');
            
            const tbody = document.getElementById('articles-list');
            console.log('tbody found:', !!tbody);
            
            if (tbody) {
                const dragHandles = tbody.querySelectorAll('.drag-handle');
                console.log('Drag handles found:', dragHandles.length);
                
                if (typeof Sortable === 'undefined') {
                    console.error('SortableJS is not loaded!');
                    return;
                }
                
                const sortable = Sortable.create(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    draggable: 'tr[data-id]',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onStart: function(evt) {
                        console.log('Drag started');
                    },
                    onEnd: function(evt) {
                        console.log('Drag ended, old index:', evt.oldIndex, 'new index:', evt.newIndex);
                        const items = Array.from(tbody.querySelectorAll('tr[data-id]')).map((row, index) => ({
                            id: parseInt(row.getAttribute('data-id')),
                            order: index + 1
                        }));

                        fetch('{{ route("admin.articles.update-order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ items: items })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Обновляем порядок в таблице
                                items.forEach((item, index) => {
                                    const row = tbody.querySelector(`tr[data-id="${item.id}"]`);
                                    if (row) {
                                        const orderCell = row.querySelector('td:first-child');
                                        if (orderCell) {
                                            orderCell.innerHTML = `<i class="fas fa-grip-vertical text-gray-400"></i> ${item.order}`;
                                        }
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка при обновлении порядка:', error);
                            location.reload(); // Перезагружаем страницу при ошибке
                        });
                    }
                });
                
                console.log('SortableJS initialized successfully');
            } else {
                console.error('tbody element not found!');
            }
        });
    </script>
    @endpush
</x-app-layout>
