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
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <a href="{{ route('admin.users') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                            Пользователи
                        </a>
                        <a href="{{ route('admin.reports') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Отчеты
                        </a>
                        <a href="{{ route('admin.comments') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            Комментарии
                        </a>
                        <a href="{{ route('admin.seo.index') }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center">
                            SEO
                        </a>
                        <a href="{{ route('admin.settings') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                            Настройки
                        </a>
                    </div>
                </div>
            </div>

            <!-- Последние отчеты -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Последние отчеты</h3>
                    <div class="space-y-2">
                        @foreach($recentReports as $report)
                            <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                <div>
                                    <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $report->report_date->format('d.m.Y') }} - {{ $report->user->nickname }}
                                    </a>
                                    <span class="text-sm text-gray-500 ml-2">Снов: {{ $report->dreams->count() }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $report->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Последние пользователи -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Последние пользователи</h3>
                    <div class="space-y-2">
                        @foreach($recentUsers as $user)
                            <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                <div>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $user->nickname }} ({{ $user->name }})
                                    </a>
                                    <span class="text-sm text-gray-500 ml-2">{{ $user->email }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>









