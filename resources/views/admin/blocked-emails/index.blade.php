<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Чёрный список email') }}
            </h2>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.users') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    К пользователям
                </a>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    В админку
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm text-amber-900 dark:text-amber-100">
                <p class="font-medium mb-1">Как попадает в список</p>
                <ul class="list-disc list-inside space-y-1 text-amber-800 dark:text-amber-200">
                    <li><strong>Временная запись</strong> — при блокировке пользователя; снимается при разблокировке.</li>
                    <li><strong>Постоянная</strong> — после полного удаления аккаунта; снятие только вручную в базе данных.</li>
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.blocked-emails.index') }}" class="flex gap-4 flex-wrap items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поиск</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Фрагмент email..."
                                   class="w-full block border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="permanent" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип</label>
                            <select id="permanent" name="permanent" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm min-w-[11rem]">
                                <option value="">Все</option>
                                <option value="1" {{ request('permanent') === '1' ? 'selected' : '' }}>Постоянные</option>
                                <option value="0" {{ request('permanent') === '0' ? 'selected' : '' }}>Временные (бан)</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Найти
                        </button>
                        @if(request()->hasAny(['search', 'permanent']))
                            <a href="{{ route('admin.blocked-emails.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline py-2">Сбросить</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Тип</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Добавлен</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($blockedEmails as $row)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row->id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 break-all font-mono">{{ $row->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($row->is_permanent)
                                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">Постоянный</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">Бан</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $row->created_at?->format('d.m.Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                        Записей нет.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($blockedEmails->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $blockedEmails->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
