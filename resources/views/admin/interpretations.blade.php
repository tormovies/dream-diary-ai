<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('admin.interpretations') }}" class="hover:text-gray-600">
                    {{ __('Статистика толкований снов') }}
                </a>
                <span class="text-sm font-normal text-gray-500 ml-2">(Часовой пояс: {{ $timezone ?? 'UTC' }})</span>
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Вернуться в админку
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(!$selectedDate)
            <!-- Общая статистика -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $totalCreated }}</div>
                        <div class="text-sm text-gray-600">Всего создано</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-green-600">{{ $totalCompleted }}</div>
                        <div class="text-sm text-gray-600">Завершено</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-yellow-600">{{ $totalPending }}</div>
                        <div class="text-sm text-gray-600">В процессе</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-red-600">{{ $totalFailed }}</div>
                        <div class="text-sm text-gray-600">Ошибки</div>
                    </div>
                </div>
            </div>
            @endif

            @if(!$selectedDate)
            <!-- Статистика за период -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Статистика за период ({{ $startDate }} - {{ $endDate }})</h3>
                        <span class="text-xs text-gray-500">Часовой пояс: {{ $timezone ?? 'UTC' }}</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $periodCreated }}</div>
                            <div class="text-sm text-gray-600">Создано</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-green-600">{{ $periodCompleted }}</div>
                            <div class="text-sm text-gray-600">Завершено</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-yellow-600">{{ $periodPending }}</div>
                            <div class="text-sm text-gray-600">В процессе</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-red-600">{{ $periodFailed }}</div>
                            <div class="text-sm text-gray-600">Ошибки</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(!$selectedDate)
            <!-- Фильтры -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.interpretations') }}">
                        <div class="flex flex-col md:flex-row gap-4 flex-wrap">
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Начало периода</label>
                                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-md border-gray-300">
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Конец периода</label>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-md border-gray-300">
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                                <select name="status" class="w-full rounded-md border-gray-300">
                                    <option value="">Все</option>
                                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Завершено</option>
                                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>В процессе</option>
                                    <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Ошибки</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Традиция</label>
                                <select name="tradition" class="w-full rounded-md border-gray-300">
                                    <option value="">Все</option>
                                    @foreach($traditionsConfig as $key => $tradition)
                                        @if($tradition['enabled'] ?? true)
                                            <option value="{{ $key }}" {{ $traditionFilter === $key ? 'selected' : '' }}>
                                                {{ $tradition['name_short'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Поиск по описанию</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск..." class="w-full rounded-md border-gray-300">
                            </div>
                            <div class="flex items-end min-w-[120px]">
                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Применить
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if(!$selectedDate)
            <!-- Статистика по традициям -->
            @if($traditionsStats->isNotEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Статистика по традициям (за период)</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        @foreach($traditionsStats as $traditionKey => $count)
                            @php
                                $traditionName = $traditionsConfig[$traditionKey]['name_short'] ?? $traditionKey;
                            @endphp
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $count }}</div>
                                <div class="text-sm text-gray-600">{{ $traditionName }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @endif

            <!-- Таблица по датам или детализация за день -->
            @if($selectedDate)
                <!-- Детализация за выбранную дату -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Толкования за {{ \Carbon\Carbon::parse($selectedDate)->format('d.m.Y') }}</h3>
                            <a href="{{ route('admin.interpretations', array_merge(request()->except('date'), ['start_date' => $startDate, 'end_date' => $endDate])) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                ← Вернуться к таблице по датам
                            </a>
                        </div>
                        @if($dayInterpretations->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Хеш</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Традиции</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP адрес</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($dayInterpretations as $interpretation)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @php
                                                        $timezone = $timezone ?? 'UTC';
                                                        $localTime = \Carbon\Carbon::parse($interpretation->created_at)->setTimezone($timezone);
                                                    @endphp
                                                    {{ $localTime->format('H:i:s') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                                    {{ substr($interpretation->hash, 0, 12) }}...
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($interpretation->processing_status === 'completed')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Завершено</span>
                                                    @elseif($interpretation->processing_status === 'pending')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">В процессе</span>
                                                    @elseif($interpretation->processing_status === 'failed')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ошибка</span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $interpretation->processing_status ?? 'Неизвестно' }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    @if($interpretation->traditions && count($interpretation->traditions) > 0)
                                                        @foreach($interpretation->traditions as $traditionKey)
                                                            @php
                                                                $traditionName = $traditionsConfig[$traditionKey]['name_short'] ?? $traditionKey;
                                                            @endphp
                                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">{{ $traditionName }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">Комплексная</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    {{ $interpretation->ip_address ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex items-center gap-3">
                                                        <a href="{{ route('dream-analyzer.show', $interpretation->hash) }}" 
                                                           target="_blank"
                                                           class="text-blue-600 hover:text-blue-900">Открыть</a>
                                                        <span class="text-gray-300">|</span>
                                                        <form action="{{ route('admin.interpretations.delete', $interpretation) }}" 
                                                              method="POST" 
                                                              class="inline-block"
                                                              onsubmit="return confirm('Вы уверены, что хотите удалить это толкование? Это действие нельзя отменить.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                                                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                                                            <input type="hidden" name="date" value="{{ request('date') }}">
                                                            <input type="hidden" name="status" value="{{ request('status') }}">
                                                            <input type="hidden" name="tradition" value="{{ request('tradition') }}">
                                                            <input type="hidden" name="page" value="{{ request('page') }}">
                                                            <button type="submit" 
                                                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm"
                                                                    title="Удалить толкование">
                                                                <i class="fas fa-trash mr-1"></i>Удалить
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $dayInterpretations->links() }}
                            </div>
                        @else
                            <p class="text-gray-500">Нет толкований за эту дату</p>
                        @endif
                    </div>
                </div>
            @else
                <!-- Таблица по датам -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Статистика по дням</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Всего</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Завершено</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">В процессе</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ошибки</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($dailyStats as $day)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ \Carbon\Carbon::parse($day->date)->format('d.m.Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $day->total }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                                {{ $day->completed }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                                {{ $day->pending }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                {{ $day->failed }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.interpretations', array_merge(request()->except('date'), ['date' => $day->date, 'start_date' => $startDate, 'end_date' => $endDate])) }}" 
                                                   class="text-blue-600 hover:text-blue-900">Детали</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Нет данных за выбранный период</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
