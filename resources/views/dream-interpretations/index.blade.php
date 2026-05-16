@extends('layouts.base')

@section('title', 'Мои толкования — '.config('app.name'))

@section('content')
<div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Мои толкования</h1>
            <a href="{{ route('dream-analyzer.create') }}" class="inline-flex w-full sm:w-auto items-center justify-center bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-all whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i>Новое толкование
            </a>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 sm:mt-4">Запросы из анализатора сновидений и их статус. Завершённые можно перенести в дневник как отчёт.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-400 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950/40 dark:text-green-200">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 rounded-lg border border-blue-400 bg-blue-50 px-4 py-3 text-blue-800 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-200">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-400 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl card-shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($interpretations->isEmpty())
            <div class="p-10 text-center text-gray-600 dark:text-gray-400">
                Пока нет толкований. Заполните форму на странице
                <a href="{{ route('dream-analyzer.create') }}" class="text-purple-600 underline dark:text-purple-400">«Толкование сновидений»</a>.
            </div>
        @else
            <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($interpretations as $row)
                    @php
                        $status = $row->processing_status ?? 'completed';
                        $snippet = \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', trim(strip_tags($row->dream_description))), 160);
                        $linkedReport = $row->report_id ? $row->report : null;
                        if ($linkedReport !== null && (int) $linkedReport->user_id !== (int) auth()->id()) {
                            $linkedReport = null;
                        }
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $row->created_at->format('d.m.Y H:i') }}</span>
                            <div>
                                @include('dream-interpretations.partials.status-badge', ['status' => $status])
                            </div>
                        </div>
                        <p class="text-sm text-gray-800 dark:text-gray-200 break-words">{{ $snippet ?: '—' }}</p>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                            @if($linkedReport)
                                <span class="text-gray-600 dark:text-gray-400">Дневник: <a href="{{ route('reports.show', $linkedReport) }}" class="text-purple-600 underline dark:text-purple-400 font-medium">запись</a></span>
                            @else
                                <span class="text-gray-500 dark:text-gray-500 text-sm">В дневнике пока нет</span>
                            @endif
                        </div>
                        @include('dream-interpretations.partials.row-actions', [
                            'hash' => $row->hash,
                            'status' => $status,
                            'linkedReport' => $linkedReport,
                            'openLabel' => 'Открыть толкование',
                        ])
                    </div>
                @endforeach
            </div>
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Дата запроса</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300 max-w-[16rem]">Фрагмент описания</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Статус</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Дневник</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($interpretations as $row)
                            @php
                                $status = $row->processing_status ?? 'completed';
                                $snippet = \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', trim(strip_tags($row->dream_description))), 72);
                                $linkedReport = $row->report_id ? $row->report : null;
                                if ($linkedReport !== null && (int) $linkedReport->user_id !== (int) auth()->id()) {
                                    $linkedReport = null;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $row->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-[16rem] min-w-0 break-words">{{ $snippet ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    @include('dream-interpretations.partials.status-badge', ['status' => $status])
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    @if($linkedReport)
                                        <a href="{{ route('reports.show', $linkedReport) }}"
                                           title="Открыть запись в дневнике, созданную из этого толкования"
                                           class="text-purple-600 underline dark:text-purple-400">Есть запись</a>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @include('dream-interpretations.partials.row-actions', [
                                        'hash' => $row->hash,
                                        'status' => $status,
                                        'linkedReport' => $linkedReport,
                                        'align' => 'end',
                                        'tooltips' => true,
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $interpretations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
