@props([
    'status',
    'analysisIssue' => null,
])

@if($analysisIssue)
    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ \App\Support\InterpretationQualityAnalyzer::badgeClass($analysisIssue) }}">
        {{ \App\Support\InterpretationQualityAnalyzer::label($analysisIssue) }}
    </span>
@elseif($status === 'completed')
    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-200">Готово</span>
@elseif($status === 'pending')
    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">Ожидание</span>
@elseif($status === 'processing')
    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">Обработка</span>
@elseif($status === 'failed')
    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">Ошибка</span>
@else
    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ $status }}</span>
@endif
