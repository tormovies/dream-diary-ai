@props(['issue' => null])
@if($issue)
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ \App\Support\InterpretationQualityAnalyzer::badgeClass($issue) }}"
          title="{{ $issue }}">
        {{ \App\Support\InterpretationQualityAnalyzer::label($issue) }}
    </span>
@else
    <span class="text-gray-400 text-xs">—</span>
@endif
