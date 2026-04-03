<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Админ-панель') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['users_count'] }}</div>
                        <div class="text-sm text-gray-600">Пользователей</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['reports_count'] }}</div>
                        <div class="text-sm text-gray-600">Отчетов</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['comments_count'] }}</div>
                        <div class="text-sm text-gray-600">Комментариев</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['reports_today'] }}</div>
                        <div class="text-sm text-gray-600">Отчетов сегодня</div>
                    </div>
                </div>
            </div>

            <!-- Быстрые ссылки -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Быстрые ссылки</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('admin.users') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            Пользователи
                        </a>
                        <a href="{{ route('admin.reports') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            Отчеты
                        </a>
                        <a href="{{ route('admin.comments') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            Комментарии
                        </a>
                        <a href="{{ route('admin.interpretations') }}" style="background-color: #f97316;" class="hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            Толкования
                        </a>
                        <a href="{{ route('admin.entities') }}" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            Сущности
                        </a>
                        <a href="{{ route('admin.seo.index') }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            SEO
                        </a>
                        <a href="{{ route('admin.articles.index') }}" style="background-color: #1e40af;" class="hover:opacity-90 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px] border-2 border-blue-700">
                            Статьи
                        </a>
                        <a href="{{ route('admin.settings') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            Настройки
                        </a>
                        <a href="{{ route('admin.ad') }}" class="bg-amber-500 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded text-center flex-1 min-w-[120px]">
                            Реклама
                        </a>
                    </div>
                </div>
            </div>

            <!-- Последние отчеты -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Последние отчеты</h3>
                    <div>
                        @foreach($recentReports as $report)
                            @php($preview = $report->adminDashboardPreview())
                            <div class="flex items-center gap-2 sm:gap-3 w-full min-h-[2.25rem] px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                <a href="{{ route('reports.show', $report) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 shrink-0 whitespace-nowrap text-sm font-medium tabular-nums">
                                    {{ $report->report_date->format('d.m.Y') }}
                                </a>
                                <span class="text-gray-300 dark:text-gray-600 shrink-0" aria-hidden="true">|</span>
                                <span class="text-gray-800 dark:text-gray-200 shrink-0 max-w-[10rem] sm:max-w-xs truncate text-sm" title="{{ $report->user->nickname }}">
                                    {{ $report->user->nickname }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 shrink-0 text-sm whitespace-nowrap tabular-nums">Снов: {{ $report->dreams->count() }}</span>
                                @if($preview !== '—')
                                    <span class="text-gray-400 dark:text-gray-500 shrink-0" aria-hidden="true">·</span>
                                    <span class="min-w-0 flex-1 text-sm text-gray-600 dark:text-gray-300 truncate" title="{{ $preview }}">{{ $preview }}</span>
                                @else
                                    <span class="min-w-0 flex-1"></span>
                                @endif
                                <time class="text-xs text-gray-400 dark:text-gray-500 shrink-0 whitespace-nowrap text-right" datetime="{{ $report->created_at->toIso8601String() }}">
                                    {{ $report->created_at->diffForHumans() }}
                                </time>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Последние пользователи -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Последние пользователи</h3>
                    <div>
                        @foreach($recentUsers as $user)
                            <div class="flex items-center gap-2 sm:gap-3 w-full min-h-[2.25rem] px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 shrink-0 text-sm font-medium max-w-[12rem] sm:max-w-[16rem] truncate" title="{{ $user->nickname }}">
                                    {{ $user->nickname }}
                                </a>
                                <span class="text-gray-300 dark:text-gray-600 shrink-0" aria-hidden="true">|</span>
                                <span class="text-gray-600 dark:text-gray-300 shrink-0 text-sm max-w-[10rem] truncate hidden sm:inline" title="{{ $user->name }}">{{ $user->name }}</span>
                                <span class="min-w-0 flex-1 text-sm text-gray-500 dark:text-gray-400 truncate" title="{{ $user->email }}">{{ $user->email }}</span>
                                <time class="text-xs text-gray-400 dark:text-gray-500 shrink-0 whitespace-nowrap" datetime="{{ $user->created_at->toIso8601String() }}">
                                    {{ $user->created_at->diffForHumans() }}
                                </time>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>









