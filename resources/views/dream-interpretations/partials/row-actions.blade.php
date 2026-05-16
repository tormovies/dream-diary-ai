@props([
    'hash',
    'status',
    'linkedReport' => null,
    'openLabel' => 'Открыть',
    'align' => 'start',
    'tooltips' => false,
])

@php
    $openActionLabel = match ($status) {
        'failed' => 'Повторить анализ',
        'pending', 'processing' => 'Продолжить',
        default => $openLabel,
    };
    $openTooltip = match (true) {
        ! $tooltips => null,
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
    <a href="{{ route('dream-analyzer.show', $hash) }}"
       @if($openTooltip) title="{{ $openTooltip }}" @endif
       class="inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-800 dark:text-gray-100 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors whitespace-nowrap">
        {{ $openActionLabel }}
    </a>
    @if($status === 'completed' && !$linkedReport)
        <a href="{{ route('dream-interpretations.transfer', ['hash' => $hash]) }}"
           @if($tooltips) title="Перенести текст сна в дневник как опубликованный отчёт" @endif
           class="inline-flex items-center justify-center rounded-lg border border-purple-500 dark:border-purple-400 bg-purple-50 dark:bg-purple-900/30 px-3 py-1.5 text-sm font-medium text-purple-700 dark:text-purple-300 shadow-sm hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors whitespace-nowrap">
            В дневник
        </a>
    @endif
</div>
