@props(['report'])

@php
    $diaryName = $report->user ? $report->user->getDiaryName() : 'Дневник';
    $dreamsWithTitles = $report->dreams->filter(function($dream) {
        return !empty($dream->title);
    });
    $allTitles = $dreamsWithTitles->pluck('title')->implode(', ');
    if(empty($allTitles)) {
        $allTitles = 'Без названия';
    }
    // Обрезаем если больше 160 символов
    $titlesLength = mb_strlen($allTitles);
    if($titlesLength > 160) {
        $allTitles = mb_substr($allTitles, 0, 160) . '...';
    }
    $commentsCount = $report->comments->where('parent_id', null)->count();
    $whatHappened = 'Добавлен отчет';
    $whatHappenedLink = route('reports.show', $report);
    if($commentsCount > 0) {
        $whatHappened = 'Новый комментарий';
        $whatHappenedLink = route('reports.show', $report) . '#comments';
    }
@endphp

<tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
    <td class="py-4 px-4">
        <div class="flex items-start gap-4">
            <!-- Аватар -->
            <div class="flex-shrink-0">
                @if($report->user)
                    <x-avatar :user="$report->user" size="md" />
                @endif
            </div>
            
            <!-- Контент -->
            <div class="flex-1 min-w-0">
                <!-- Заголовок -->
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ $whatHappenedLink }}" class="text-sm font-semibold text-purple-600 dark:text-purple-400 hover:underline">
                        {{ $whatHappened }}
                    </a>
                    <span class="text-gray-400 dark:text-gray-500">|</span>
                    @if($report->user)
                        <a href="{{ route('users.profile', $report->user) }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400">
                            {{ $report->user->nickname }}
                        </a>
                    @endif
                </div>
                
                <!-- Название сна -->
                <a href="{{ route('reports.show', $report) }}" target="_blank" rel="noopener noreferrer" class="block text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 mb-1">
                    {{ $allTitles }}
                </a>
                
                <!-- Метаинформация -->
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>
                        <i class="far fa-calendar mr-1"></i>
                        {{ $report->report_date->format('d.m.Y') }}
                    </span>
                    <span>
                        <i class="far fa-moon mr-1"></i>
                        {{ $report->dreams->count() }} {{ $report->dreams->count() == 1 ? 'сон' : 'снов' }}
                    </span>
                    @if($commentsCount > 0)
                    <span>
                        <i class="far fa-comment mr-1"></i>
                        {{ $commentsCount }}
                    </span>
                    @endif
                    @if($report->tags->count() > 0)
                    <div class="flex flex-wrap gap-1">
                        @foreach($report->tags->take(3) as $tag)
                        <a href="{{ route('dashboard', ['tag' => $tag->name]) }}" 
                           class="px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-xs hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors">
                            #{{ $tag->name }}
                        </a>
                        @endforeach
                        @if($report->tags->count() > 3)
                        <span class="text-gray-400">+{{ $report->tags->count() - 3 }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </td>
</tr>



















