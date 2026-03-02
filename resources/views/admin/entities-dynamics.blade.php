<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $entityName ? "Динамика: {$entityName}" : 'Динамика по сущности' }}
            </h2>
            <a href="{{ route('admin.entities') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад к сущностям
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                <h3 class="text-lg font-semibold mb-4">Выбор сущности и периода</h3>
                <form action="{{ route('admin.entities.dynamics') }}" method="get" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Тип</label>
                        <select name="type" class="rounded border-gray-300">
                            <option value="symbol" {{ $type === 'symbol' ? 'selected' : '' }}>Символ</option>
                            <option value="location" {{ $type === 'location' ? 'selected' : '' }}>Локация</option>
                            <option value="tag" {{ $type === 'tag' ? 'selected' : '' }}>Тег</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Slug сущности</label>
                        <input type="text" name="slug" value="{{ $slug }}" placeholder="напр. dom, integratsiya" class="rounded border-gray-300 min-w-[180px]">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">С</label>
                        <input type="date" name="from" value="{{ $from }}" class="rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">По</label>
                        <input type="date" name="to" value="{{ $to }}" class="rounded border-gray-300">
                    </div>
                    <button type="submit" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded">Показать</button>
                </form>
                @if($slug === '')
                    <p class="text-sm text-gray-500 mt-3">Укажите тип и slug сущности. Slug можно взять из списка сущностей (ссылка «Динамика» в строке) или ввести вручную.</p>
                @endif
            </div>

            @if($slug !== '' && $entityName)
                <p class="text-sm text-gray-500 mb-2">Период: {{ $from }} — {{ $to }}. Всего дней с данными: {{ count($daily) }}.</p>

                @if(empty($daily))
                    <p class="text-gray-500">За выбранный период записей по этой сущности нет.</p>
                @else
                    @php
                        $maxMentions = collect($daily)->max('mentions') ?: 1;
                    @endphp
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-40">График</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Упоминаний</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($daily as $row)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ \Carbon\Carbon::parse($row['date'])->locale('ru')->isoFormat('D MMM YYYY') }}</td>
                                            <td class="px-4 py-2 align-middle">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-28 h-4 bg-gray-100 rounded overflow-hidden" title="{{ $row['mentions'] }}">
                                                        <div class="h-full bg-teal-500 rounded min-w-[2px] transition-all" style="width: {{ ($row['mentions'] / $maxMentions) * 100 }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ $row['mentions'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
