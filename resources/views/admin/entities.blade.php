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
            <p class="text-sm text-gray-500 mb-4">
                Данные из таблицы <code>dream_interpretation_entities</code> (символы, локации, теги из толкований). Обновляются командой <code>interpretations:index-entities</code>. Агрегация по дням — <code>interpretations:aggregate-entity-daily</code>.
            </p>

            <!-- Фильтр по дате и ссылка на сравнение -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 flex flex-wrap items-end gap-4">
                    <form action="{{ route('admin.entities') }}" method="get" class="flex items-end gap-2">
                        <label class="text-sm text-gray-600">Показать топ за дату:</label>
                        <input type="date" name="date" value="{{ $date ?? '' }}" class="rounded border-gray-300">
                        <button type="submit" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded">Показать</button>
                    </form>
                    @if($date ?? null)
                        <a href="{{ route('admin.entities') }}" class="text-gray-500 hover:text-gray-700 text-sm">Сбросить (общий топ)</a>
                    @endif
                    <a href="{{ route('admin.entities.compare') }}" class="ml-4 bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Сравнить два дня</a>
                </div>
            </div>

            @if($date ?? null)
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
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($countByType['symbol'] ?? 0) }}/{{ number_format($uniqueByType['symbol'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Символов: всего / уникальных</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-green-600">{{ number_format($countByType['location'] ?? 0) }}/{{ number_format($uniqueByType['location'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Локаций: всего / уникальных</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-purple-600">{{ number_format($countByType['tag'] ?? 0) }}/{{ number_format($uniqueByType['tag'] ?? 0) }}</div>
                        <div class="text-sm text-gray-600">Тегов: всего / уникальных</div>
                    </div>
                </div>
            </div>

            <!-- Три колонки: Символы | Локации | Теги -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Символы -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Символы</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Число</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-20">Динамика</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($symbols as $row)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $row['name'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ $row['mentions'] ?? 0 }}</td>
                                            <td class="px-3 py-2 text-right"><a href="{{ route('admin.entities.dynamics', ['type' => 'symbol', 'slug' => $row['slug'] ?? '']) }}" class="text-teal-600 hover:underline text-xs">Динамика</a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-3 py-4 text-sm text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Локации -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Локации</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Число</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-20">Динамика</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($locations as $row)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $row['name'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ $row['mentions'] ?? 0 }}</td>
                                            <td class="px-3 py-2 text-right"><a href="{{ route('admin.entities.dynamics', ['type' => 'location', 'slug' => $row['slug'] ?? '']) }}" class="text-teal-600 hover:underline text-xs">Динамика</a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-3 py-4 text-sm text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Теги -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Теги</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Число</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-20">Динамика</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tags as $row)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $row['name'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ $row['mentions'] ?? 0 }}</td>
                                            <td class="px-3 py-2 text-right"><a href="{{ route('admin.entities.dynamics', ['type' => 'tag', 'slug' => $row['slug'] ?? '']) }}" class="text-teal-600 hover:underline text-xs">Динамика</a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-3 py-4 text-sm text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
