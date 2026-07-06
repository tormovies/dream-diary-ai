@props([
    'hash',
    'status',
    'linkedReport' => null,
    'analysisIssue' => null,
    'openLabel' => 'Открыть',
    'align' => 'start',
    'tooltips' => false,
])

@php
    $needsRetry = $status === 'failed' || ! empty($analysisIssue);
    $canTransfer = $status === 'completed' && ! $linkedReport && empty($analysisIssue);

    $openActionLabel = match (true) {
        $needsRetry => 'Подробнее',
        $status === 'pending', $status === 'processing' => 'Продолжить',
        default => $openLabel,
    };
    $openTooltip = match (true) {
        ! $tooltips => null,
        $needsRetry => 'Открыть страницу толкования с описанием ошибки',
        $status === 'failed' => 'Открыть страницу толкования и запустить повторный анализ сна',
        $status === 'pending' => 'Открыть страницу толкования — анализ ещё не начался или в очереди',
        $status === 'processing' => 'Открыть страницу толкования и следить за ходом анализа',
        default => 'Посмотреть толкование и результат анализа сна',
    };
@endphp

<div @class([
    'flex flex-wrap gap-2',
    'justify-end' => $align === 'end',
    'justify-start' => $align === 'start',
])>
    @if($needsRetry)
        <form method="POST" action="{{ route('dream-analyzer.retry', $hash) }}" class="inline">
            @csrf
            <button type="submit"
                    @if($tooltips) title="Запустить анализ этого сна заново" @endif
                    class="inline-flex items-center justify-center rounded-lg bg-purple-600 hover:bg-purple-700 px-3 py-1.5 text-sm font-medium text-white shadow-sm transition-colors whitespace-nowrap">
                <i class="fas fa-redo mr-1.5 text-xs"></i>
                Повторить анализ
            </button>
        </form>
    @endif
    <a href="{{ route('dream-analyzer.show', $hash) }}"
       @if($openTooltip) title="{{ $openTooltip }}" @endif
       class="inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-800 dark:text-gray-100 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
        {{ $openActionLabel }}
    </a>
    @if($canTransfer)
        <a href="{{ route('dream-interpretations.transfer', ['hash' => $hash]) }}"
           @if($tooltips) title="Перенести текст сна в дневник как опубликованный отчёт" @endif
           class="inline-flex items-center justify-center rounded-lg border border-purple-500 dark:border-purple-400 bg-purple-50 dark:bg-purple-900/30 px-3 py-1.5 text-sm font-medium text-purple-700 dark:text-purple-300 shadow-sm hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors whitespace-nowrap">
            В дневник
        </a>
    @endif
</div>
