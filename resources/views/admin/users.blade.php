<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Управление пользователями') }}
            </h2>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.blocked-emails.index') }}"
                   class="inline-block font-bold py-2 px-4 rounded no-underline hover:opacity-90"
                   style="background-color:#d97706;color:#fff;">
                    Чёрный список email
                </a>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-block font-bold py-2 px-4 rounded no-underline hover:opacity-90"
                   style="background-color:#6b7280;color:#fff;">
                    Назад
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="mb-4 p-4 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">{{ session('info') }}</div>
            @endif
            @if(session('warning'))
                <div class="mb-4 p-4 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">{{ session('warning') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
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
                                        @elseif(!$user->hasVerifiedEmail())
                                            <span class="px-2 py-1 text-xs rounded bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200" title="Email не подтверждён">
                                                Почта не подтверждена
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
                                            @if(!$user->hasVerifiedEmail() && !$user->is_banned)
                                                <form method="POST" action="{{ route('admin.users.verify-email', $user) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-teal-600 hover:text-teal-800 dark:text-teal-400" onclick="return confirm('Подтвердить email пользователя {{ $user->nickname }}? На его почту будет отправлено уведомление.')">
                                                        Подтвердить почту
                                                    </button>
                                                </form>
                                            @endif
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
                                                
                                                <button
                                                    type="button"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400"
                                                    data-action-url="{{ route('admin.users.purge', $user) }}"
                                                    data-nickname="{{ $user->nickname }}"
                                                    onclick="showPurgeModal(this)"
                                                >
                                                    Удалить…
                                                </button>
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

    <!-- Модальное окно: удаление контента и/или пользователя -->
    <div id="purgeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-12 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-1">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Удаление: пользователь <span id="purgeModalNickname" class="font-semibold"></span></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Выберите режим. Это действие нельзя отменить.
                </p>
                <form id="purgeForm" method="POST" action="">
                    @csrf
                    <div class="space-y-3 mb-4">
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <input type="radio" name="purge_mode" value="content_only" class="mt-1" required>
                            <span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">Только материалы и активность</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400 mt-1">Отчёты, сны, толкования, комментарии, друзья, уведомления. Аккаунт останется: email, ник, блокировка (если была).</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded border border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <input type="radio" name="purge_mode" value="full" class="mt-1">
                            <span>
                                <span class="font-medium text-red-800 dark:text-red-200">Пользователя и весь контент</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400 mt-1">Полное удаление учётной записи из базы. Email сможет зарегистрироваться снова.</span>
                            </span>
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-start gap-2 cursor-pointer text-sm">
                            <input type="checkbox" name="purge_confirm" value="1" class="rounded border-gray-300 dark:border-gray-600" required>
                            <span class="text-gray-700 dark:text-gray-300">Я понимаю, что это действие необратимо.</span>
                        </label>
                    </div>
                    <div class="flex gap-3 justify-end flex-wrap">
                        <button type="button" onclick="closePurgeModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
                            Отмена
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Выполнить
                        </button>
                    </div>
                </form>
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
        function showPurgeModal(btn) {
            const modal = document.getElementById('purgeModal');
            const form = document.getElementById('purgeForm');
            document.getElementById('purgeModalNickname').textContent = btn.getAttribute('data-nickname');
            form.reset();
            form.action = btn.getAttribute('data-action-url');
            modal.classList.remove('hidden');
        }

        function closePurgeModal() {
            document.getElementById('purgeModal').classList.add('hidden');
            document.getElementById('purgeForm').reset();
        }

        document.getElementById('purgeModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closePurgeModal();
            }
        });

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









