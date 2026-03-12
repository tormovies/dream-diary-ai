<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Сущности толкований') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Вернуться в админку
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <p class="mb-4 text-sm text-green-700 bg-green-100 px-4 py-2 rounded">{{ session('success') }}</p>
            @endif
            @if($search ?? null)
            <p class="text-sm font-medium text-gray-700 mb-2">Результаты поиска «{{ e($search) }}» — подстрока в названии (дом → домашний, придомовой и т.д.)</p>
            @elseif($date ?? null)
            <p class="text-sm font-medium text-gray-700 mb-2">Топ за {{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('D MMMM YYYY') }}</p>
            @endif

            <!-- Сводка -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalRows) }}/{{ number_format($totalUnique ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Всего записей / уникальных</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($countByType['symbol'] ?? 0) }}/<a href="{{ route('admin.entities.export', ['type' => 'symbol']) }}" class="text-blue-600 hover:underline" title="Скачать список уникальных символов (.txt)">{{ number_format($uniqueByType['symbol'] ?? 0) }}</a></div>
                        <div class="text-sm text-gray-600">Символов: всего / уникальных</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-green-600">{{ number_format($countByType['location'] ?? 0) }}/<a href="{{ route('admin.entities.export', ['type' => 'location']) }}" class="text-green-600 hover:underline" title="Скачать список уникальных локаций (.txt)">{{ number_format($uniqueByType['location'] ?? 0) }}</a></div>
                        <div class="text-sm text-gray-600">Локаций: всего / уникальных</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-purple-600">{{ number_format($countByType['tag'] ?? 0) }}/<a href="{{ route('admin.entities.export', ['type' => 'tag']) }}" class="text-purple-600 hover:underline" title="Скачать список уникальных тегов (.txt)">{{ number_format($uniqueByType['tag'] ?? 0) }}</a></div>
                        <div class="text-sm text-gray-600">Тегов: всего / уникальных</div>
                    </div>
                </div>
            </div>

            <!-- Фильтр: поиск по сущностям и дата -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-6 flex flex-wrap items-end gap-4">
                    <form action="{{ route('admin.entities') }}" method="get" class="flex flex-wrap items-end gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700">Поиск</label>
                            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="напр. дом"
                                class="rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 w-52 min-h-[2.5rem]">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700">Топ за дату</label>
                            <input type="date" name="date" value="{{ $date ?? '' }}"
                                class="rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 min-h-[2.5rem]">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700 invisible">Отправить</label>
                            <button type="submit" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-md shadow-sm min-h-[2.5rem]">Показать</button>
                        </div>
                    </form>
                    @if(($date ?? null) || ($search ?? null))
                        <a href="{{ route('admin.entities') }}" class="inline-block text-sm font-bold py-2 px-4 rounded border border-gray-400 bg-gray-100 text-gray-700 hover:bg-gray-200">Сбросить (общий топ)</a>
                    @endif
                    <a href="{{ route('admin.entities.compare') }}" class="ml-4 bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Сравнить два дня</a>
                    <a href="{{ route('admin.entities.groups.index') }}" class="ml-4 bg-amber-500 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded">Группы сущностей</a>
                </div>
            </div>

            <!-- Массовые действия: выбор группы + В группу / Создать группу (+) -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 flex flex-wrap items-center gap-4">
                <span class="text-sm font-medium text-gray-700">Выбрать сущности в таблицах ниже, затем:</span>
                <form id="form-bulk-add" action="{{ route('admin.entities.bulk-add-to-group') }}" method="post" class="inline flex flex-wrap items-center gap-2">
                    @csrf
                    <select name="entity_group_id" class="text-sm rounded border border-gray-300 py-1.5 px-2 bg-white min-w-[160px]" @if(($entityGroups ?? collect())->isEmpty()) disabled @endif>
                        <option value="">— Группа —</option>
                        @foreach($entityGroups ?? [] as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="text-sm px-3 py-1.5 rounded border border-teal-600 bg-teal-50 text-teal-700 hover:bg-teal-100 font-medium" @if(($entityGroups ?? collect())->isEmpty()) disabled @endif>В группу</button>
                </form>
                <form id="form-bulk-create" action="{{ route('admin.entities.bulk-create-group') }}" method="post" class="inline">
                    @csrf
                    <button type="submit" class="text-sm w-8 h-8 rounded border border-teal-600 bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold" title="Создать новую группу из выбранных сущностей">+</button>
                </form>
            </div>

            <!-- Три колонки: Символы | Локации | Теги -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 min-w-0">
                <!-- Символы -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg min-w-0">
                    <div class="p-6 min-w-0">
                        <h3 class="text-lg font-semibold mb-4">Символы</h3>
                        <div class="min-w-0 overflow-hidden">
                            <table class="w-full table-fixed divide-y divide-gray-200 entity-sortable-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-10">Выбор</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-[40%]" data-sort="name" title="Сортировка по названию">Название ↕</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-[12%]" data-sort="mentions" title="Сортировка по числу">С ↕</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-[30%]">Группа</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($symbols as $row)
                                        @php $gr = $slugToGroup[$row['slug'] ?? ''] ?? null; @endphp
                                        <tr data-name="{{ e($row['name'] ?? '') }}" data-mentions="{{ (int) ($row['mentions'] ?? 0) }}">
                                            <td class="px-2 py-2"><input type="checkbox" class="entity-checkbox rounded border-gray-300" name="entity_slugs[]" value="{{ $row['slug'] ?? '' }}" form="form-bulk-add"></td>
                                            <td class="px-2 py-2 text-sm min-w-0 {{ $gr ? 'bg-amber-100' : 'text-gray-900' }}">
                                                <a href="{{ route('admin.entities.dynamics', ['type' => 'symbol', 'slug' => $row['slug'] ?? '']) }}" class="{{ $gr ? 'text-amber-800' : 'text-teal-600' }} hover:underline break-words">{{ $row['name'] ?? '—' }}</a>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-gray-900 text-right">{{ $row['mentions'] ?? 0 }}</td>
                                            <td class="px-2 py-2 text-sm {{ $gr ? 'bg-amber-100 text-amber-800' : 'text-gray-500' }}">{{ $gr ? ($gr['name'] ?? '—') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-2 py-4 text-sm text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Локации -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg min-w-0">
                    <div class="p-6 min-w-0">
                        <h3 class="text-lg font-semibold mb-4">Локации</h3>
                        <div class="min-w-0 overflow-hidden">
                            <table class="w-full table-fixed divide-y divide-gray-200 entity-sortable-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-10">Выбор</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-[40%]" data-sort="name" title="Сортировка по названию">Название ↕</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-[12%]" data-sort="mentions" title="Сортировка по числу">С ↕</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-[30%]">Группа</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($locations as $row)
                                        @php $gr = $slugToGroup[$row['slug'] ?? ''] ?? null; @endphp
                                        <tr data-name="{{ e($row['name'] ?? '') }}" data-mentions="{{ (int) ($row['mentions'] ?? 0) }}">
                                            <td class="px-2 py-2"><input type="checkbox" class="entity-checkbox rounded border-gray-300" name="entity_slugs[]" value="{{ $row['slug'] ?? '' }}" form="form-bulk-add"></td>
                                            <td class="px-2 py-2 text-sm min-w-0 {{ $gr ? 'bg-amber-100' : 'text-gray-900' }}">
                                                <a href="{{ route('admin.entities.dynamics', ['type' => 'location', 'slug' => $row['slug'] ?? '']) }}" class="{{ $gr ? 'text-amber-800' : 'text-teal-600' }} hover:underline break-words">{{ $row['name'] ?? '—' }}</a>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-gray-900 text-right">{{ $row['mentions'] ?? 0 }}</td>
                                            <td class="px-2 py-2 text-sm {{ $gr ? 'bg-amber-100 text-amber-800' : 'text-gray-500' }}">{{ $gr ? ($gr['name'] ?? '—') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-2 py-4 text-sm text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Теги -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg min-w-0">
                    <div class="p-6 min-w-0">
                        <h3 class="text-lg font-semibold mb-4">Теги</h3>
                        <div class="min-w-0 overflow-hidden">
                            <table class="w-full table-fixed divide-y divide-gray-200 entity-sortable-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-10">Выбор</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-[40%]" data-sort="name" title="Сортировка по названию">Название ↕</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 select-none w-[12%]" data-sort="mentions" title="Сортировка по числу">С ↕</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-[30%]">Группа</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tags as $row)
                                        @php $gr = $slugToGroup[$row['slug'] ?? ''] ?? null; @endphp
                                        <tr data-name="{{ e($row['name'] ?? '') }}" data-mentions="{{ (int) ($row['mentions'] ?? 0) }}">
                                            <td class="px-2 py-2"><input type="checkbox" class="entity-checkbox rounded border-gray-300" name="entity_slugs[]" value="{{ $row['slug'] ?? '' }}" form="form-bulk-add"></td>
                                            <td class="px-2 py-2 text-sm min-w-0 {{ $gr ? 'bg-amber-100' : 'text-gray-900' }}">
                                                <a href="{{ route('admin.entities.dynamics', ['type' => 'tag', 'slug' => $row['slug'] ?? '']) }}" class="{{ $gr ? 'text-amber-800' : 'text-teal-600' }} hover:underline break-words">{{ $row['name'] ?? '—' }}</a>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-gray-900 text-right">{{ $row['mentions'] ?? 0 }}</td>
                                            <td class="px-2 py-2 text-sm {{ $gr ? 'bg-amber-100 text-amber-800' : 'text-gray-500' }}">{{ $gr ? ($gr['name'] ?? '—') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-2 py-4 text-sm text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var formAdd = document.getElementById('form-bulk-add');
        if (formAdd) {
            formAdd.addEventListener('submit', function(e) {
                if (!formAdd.entity_group_id.value) {
                    e.preventDefault();
                    alert('Выберите группу сущностей.');
                    return;
                }
                var checkedAdd = document.querySelectorAll('.entity-checkbox:checked');
                if (checkedAdd.length === 0) {
                    e.preventDefault();
                    alert('Выберите хотя бы одну сущность в таблицах.');
                    return;
                }
            });
        }
        var formCreate = document.getElementById('form-bulk-create');
        if (formCreate) {
            formCreate.addEventListener('submit', function(e) {
                var checked = document.querySelectorAll('.entity-checkbox:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Выберите хотя бы одну сущность.');
                    return;
                }
                formCreate.querySelectorAll('input[name="entity_slugs[]"]').forEach(function(inp) { inp.remove(); });
                checked.forEach(function(cb) {
                    var h = document.createElement('input');
                    h.type = 'hidden';
                    h.name = 'entity_slugs[]';
                    h.value = cb.value;
                    formCreate.appendChild(h);
                });
            });
        }
    })();
    document.querySelectorAll('.entity-sortable-table').forEach(function(table) {
        var headers = table.querySelectorAll('thead th[data-sort]');
        var sortState = { col: null, dir: 1 };
        headers.forEach(function(th) {
            th.addEventListener('click', function() {
                var col = th.getAttribute('data-sort');
                if (sortState.col === col) sortState.dir *= -1; else sortState.dir = 1;
                sortState.col = col;
                var tbody = table.querySelector('tbody');
                var allRows = Array.from(tbody.querySelectorAll('tr'));
                var dataRows = allRows.filter(function(r) { return r.hasAttribute('data-name'); });
                var emptyRows = allRows.filter(function(r) { return !r.hasAttribute('data-name'); });
                if (dataRows.length === 0) return;
                dataRows.sort(function(a, b) {
                    var va, vb;
                    if (col === 'name') {
                        va = (a.getAttribute('data-name') || '').toLowerCase();
                        vb = (b.getAttribute('data-name') || '').toLowerCase();
                        return sortState.dir * (va.localeCompare(vb, 'ru'));
                    } else {
                        va = parseInt(a.getAttribute('data-mentions') || '0', 10);
                        vb = parseInt(b.getAttribute('data-mentions') || '0', 10);
                        return sortState.dir * (va - vb);
                    }
                });
                dataRows.forEach(function(r) { tbody.appendChild(r); });
                emptyRows.forEach(function(r) { tbody.appendChild(r); });
            });
        });
    });
    </script>
</x-app-layout>
