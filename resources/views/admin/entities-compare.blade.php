<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Сравнение сущностей за два дня') }}
            </h2>
            <a href="{{ route('admin.entities') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад к сущностям
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.entities.compare') }}" method="get" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 flex flex-wrap items-end gap-4">
                <div class="flex items-end gap-2">
                    <label class="text-sm text-gray-600">День 1:</label>
                    <input type="date" name="date1" value="{{ $date1 }}" class="rounded border-gray-300">
                </div>
                <div class="flex items-end gap-2">
                    <label class="text-sm text-gray-600">День 2:</label>
                    <input type="date" name="date2" value="{{ $date2 }}" class="rounded border-gray-300">
                </div>
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Сравнить</button>
            </form>

            <p class="text-sm text-gray-500 mb-4">
                Сравнение: {{ $date1 }} и {{ $date2 }}. Разница = День 2 − День 1 (положительное — рост).
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Символы</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $date1 }}</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $date2 }}</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Разница</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($symbols as $row)
                                        <tr>
                                            <td class="px-2 py-1 text-gray-900">{{ $row['name'] ?? '—' }}</td>
                                            <td class="px-2 py-1 text-right text-gray-600">{{ $row['mentions1'] }}</td>
                                            <td class="px-2 py-1 text-right text-gray-600">{{ $row['mentions2'] }}</td>
                                            <td class="px-2 py-1 text-right {{ ($row['diff'] ?? 0) > 0 ? 'text-green-600' : (($row['diff'] ?? 0) < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                                {{ $row['diff'] > 0 ? '+' : '' }}{{ $row['diff'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-2 py-4 text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Локации</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $date1 }}</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $date2 }}</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Разница</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($locations as $row)
                                        <tr>
                                            <td class="px-2 py-1 text-gray-900">{{ $row['name'] ?? '—' }}</td>
                                            <td class="px-2 py-1 text-right text-gray-600">{{ $row['mentions1'] }}</td>
                                            <td class="px-2 py-1 text-right text-gray-600">{{ $row['mentions2'] }}</td>
                                            <td class="px-2 py-1 text-right {{ ($row['diff'] ?? 0) > 0 ? 'text-green-600' : (($row['diff'] ?? 0) < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                                {{ $row['diff'] > 0 ? '+' : '' }}{{ $row['diff'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-2 py-4 text-gray-500">Нет данных</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Теги</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $date1 }}</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $date2 }}</th>
                                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Разница</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tags as $row)
                                        <tr>
                                            <td class="px-2 py-1 text-gray-900">{{ $row['name'] ?? '—' }}</td>
                                            <td class="px-2 py-1 text-right text-gray-600">{{ $row['mentions1'] }}</td>
                                            <td class="px-2 py-1 text-right text-gray-600">{{ $row['mentions2'] }}</td>
                                            <td class="px-2 py-1 text-right {{ ($row['diff'] ?? 0) > 0 ? 'text-green-600' : (($row['diff'] ?? 0) < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                                {{ $row['diff'] > 0 ? '+' : '' }}{{ $row['diff'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-2 py-4 text-gray-500">Нет данных</td></tr>
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
