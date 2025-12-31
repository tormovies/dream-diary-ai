<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Управление пользователями') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Поиск -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.users') }}" class="flex gap-4">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Поиск по никнейму, имени или email..."
                               class="flex-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <select name="role" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Все роли</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Админы</option>
                            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Пользователи</option>
                        </select>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Найти
                        </button>
                    </form>
                </div>
            </div>

            <!-- Список пользователей -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Никнейм</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Роль</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Статус</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Отчетов</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($users as $user)
                                <tr class="{{ $user->is_banned ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            {{ $user->nickname }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded {{ $user->role === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ $user->role === 'admin' ? 'Админ' : 'Пользователь' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->is_banned)
                                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200" title="{{ $user->ban_reason ?? 'Причина не указана' }}">
                                                Заблокирован
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Активен
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->reports_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2 flex-wrap">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Редактировать</a>
                                            <a href="{{ route('users.profile', $user) }}" class="text-green-600 hover:text-green-800 dark:text-green-400">Профиль</a>
                                            
                                            @if(!$user->isAdmin() && $user->id !== auth()->id())
                                                @if($user->is_banned)
                                                    <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400" onclick="return confirm('Разблокировать пользователя {{ $user->nickname }}?')">
                                                            Разблокировать
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" onclick="showBanModal({{ $user->id }}, '{{ $user->nickname }}')" class="text-orange-600 hover:text-orange-800 dark:text-orange-400">
                                                        Заблокировать
                                                    </button>
                                                @endif
                                                
                                                <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400" onclick="return confirm('Удалить пользователя {{ $user->nickname }} и весь его контент? Это действие необратимо!')">
                                                        Удалить
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для блокировки пользователя -->
    <div id="banModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Блокировка пользователя</h3>
                <form id="banForm" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <label for="ban_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Причина блокировки (необязательно)
                        </label>
                        <textarea 
                            id="ban_reason" 
                            name="ban_reason" 
                            rows="3" 
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="Укажите причину блокировки..."
                        ></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button 
                            type="button" 
                            onclick="closeBanModal()" 
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-400 dark:hover:bg-gray-500"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                        >
                            Заблокировать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showBanModal(userId, nickname) {
            const modal = document.getElementById('banModal');
            const form = document.getElementById('banForm');
            form.action = `/admin/users/${userId}/ban`;
            modal.classList.remove('hidden');
        }

        function closeBanModal() {
            const modal = document.getElementById('banModal');
            const form = document.getElementById('banForm');
            form.reset();
            modal.classList.add('hidden');
        }

        // Закрытие модального окна при клике вне его
        document.getElementById('banModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBanModal();
            }
        });
    </script>
</x-app-layout>









