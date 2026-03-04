<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Группы сущностей</h2>
            <a href="{{ route('admin.entities') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">К сущностям</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <p class="mb-4 text-sm text-green-700 bg-green-100 px-4 py-2 rounded">{{ session('success') }}</p>
            @endif
            @if(session('error'))
                <p class="mb-4 text-sm text-red-700 bg-red-100 px-4 py-2 rounded">{{ session('error') }}</p>
            @endif

            <details class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <summary class="p-4 cursor-pointer list-none flex items-center justify-between hover:bg-gray-50 rounded-lg [&::-webkit-details-marker]:hidden">
                    <span class="text-lg font-semibold text-gray-800">Добавить группы</span>
                    <span class="text-gray-400 text-sm">развернуть</span>
                </summary>
                <div class="p-6 pt-0 border-t border-gray-100">
                    <p class="text-sm text-gray-600 mb-3">Одна строка — одна группа. Первое слово до запятой — название группы и первый элемент, остальное через запятую — дополнительные сущности. Регистр и пробелы нормализуются.</p>
                    <form action="{{ route('admin.entities.groups.store') }}" method="post">
                        @csrf
                        <textarea name="lines" rows="12" class="w-full rounded-md border-2 border-gray-300 px-3 py-2 text-gray-900 focus:border-teal-500 focus:ring-1 focus:ring-teal-500" placeholder="Дом, личный дом, дом семейный, спальня, кухня&#10;Вода, река, море, озеро"></textarea>
                        <button type="submit" class="mt-3 bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-md">Сохранить группы</button>
                    </form>
                </div>
            </details>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-4">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold">Список групп ({{ $groups->count() }})</h3>
                        <a href="{{ route('admin.entities.groups.export') }}" class="text-sm bg-teal-500 hover:bg-teal-700 text-white font-bold py-1.5 px-3 rounded" download>Экспорт .txt</a>
                    </div>
                    @php
                        $nameOrder = ($sort ?? 'name') === 'name' && ($order ?? 'asc') === 'asc' ? 'desc' : 'asc';
                        $countOrder = ($sort ?? 'name') === 'count' && ($order ?? 'asc') === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <div class="flex items-center gap-4 py-1 border-b border-gray-200 text-xs font-medium text-gray-500 uppercase">
                        <a href="{{ route('admin.entities.groups.index', ['sort' => 'name', 'order' => $nameOrder]) }}" class="shrink-0 w-40 hover:text-teal-600 {{ ($sort ?? 'name') === 'name' ? 'text-teal-600' : '' }}">Название ↕</a>
                        <div class="flex-1 min-w-0">Сущности</div>
                        <a href="{{ route('admin.entities.groups.index', ['sort' => 'count', 'order' => $countOrder]) }}" class="shrink-0 w-24 text-right hover:text-teal-600 {{ ($sort ?? 'name') === 'count' ? 'text-teal-600' : '' }}">Число ↕</a>
                        <span class="shrink-0 w-40">Страница</span>
                        <span class="shrink-0 w-10"></span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse($groups as $group)
                            <li class="py-1.5 flex items-center gap-4">
                                <a href="{{ route('admin.entities.groups.edit', $group) }}" class="text-teal-600 hover:underline font-medium shrink-0 w-40">{{ $group->name }}</a>
                                @php
                                    $slugToName = $slugToName ?? [];
                                    $displayNames = $group->mappings->map(fn($m) => trim((string)($m->entity_name ?? '')) !== '' ? $m->entity_name : ($slugToName[$m->entity_slug] ?? $m->entity_slug));
                                @endphp
                                <div class="flex-1 min-w-0 text-sm text-gray-600 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $displayNames->implode(', ') }}">
                                    {{ $displayNames->implode(', ') ?: '—' }}
                                </div>
                                <span class="text-sm text-gray-500 shrink-0 w-24 text-right">{{ $group->mappings_count }} сущностей</span>
                                <div class="shrink-0 w-40 flex items-center gap-1 flex-wrap">
                                    @if($group->symbolPage)
                                        <a href="{{ route('symbol.show', $group->symbolPage->slug) }}" class="text-green-600 hover:text-green-800 font-medium" target="_blank" rel="noopener">{{ $group->slug }}</a>
                                        <a href="{{ route('admin.articles.edit', $group->symbolPage) }}" class="text-gray-500 hover:text-teal-600" title="Редактировать страницу">✎</a>
                                        <form action="{{ route('admin.entities.groups.request-symbol-page', $group) }}" method="post" class="inline" onsubmit="return confirm('Перезапросить DeepSeek и перезаписать контент страницы? Текущий текст будет заменён. Продолжить?');">
                                            @csrf
                                            <button type="submit" class="text-amber-600 hover:text-amber-800 text-sm" title="Перезапросить страницу через DeepSeek">↻</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.entities.groups.request-symbol-page', $group) }}" method="post" class="inline" onsubmit="return confirm('Отправить запрос в DeepSeek для генерации страницы? Это может занять до минуты.');">
                                            @csrf
                                            <button type="submit" class="text-teal-600 hover:text-teal-800 font-medium">Запрос</button>
                                        </form>
                                    @endif
                                </div>
                                <form action="{{ route('admin.entities.groups.destroy', $group) }}" method="post" class="shrink-0" onsubmit="return confirm('Удалить группу «{{ addslashes($group->name) }}»? Все сущности будут отвязаны и станут свободными.');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Удалить группу">🗑</button>
                                </form>
                            </li>
                        @empty
                            <li class="py-3 text-gray-500 text-sm">Групп пока нет. Добавьте их через форму выше.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
