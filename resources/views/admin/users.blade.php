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
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
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
                    <form method="GET" action="{{ route('admin.users') }}" class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4">
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
                        <select name="order" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" title="Сортировка по дате регистрации">
                            <option value="desc" {{ ($order ?? 'desc') === 'desc' ? 'selected' : '' }}>Сначала новые</option>
                            <option value="asc" {{ ($order ?? 'desc') === 'asc' ? 'selected' : '' }}>Сначала старые</option>
                        </select>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Найти
                        </button>
                    </form>
                </div>
            </div>

            @php
                $currentOrder = $order ?? request('order', 'desc');
                $toggleOrder = $currentOrder === 'desc' ? 'asc' : 'desc';
            @endphp

            <!-- Панель массовых действий -->
            <div id="bulkToolbar" class="hidden mb-4 sticky top-2 z-40 rounded-lg border border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/40 px-4 py-3 shadow-sm">
                <div class="flex flex-wrap items-center gap-3 justify-between">
                    <p class="text-sm text-red-900 dark:text-red-100">
                        Выбрано: <span id="bulkSelectedCount" class="font-semibold">0</span>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="clearUserSelection()" class="px-3 py-1.5 text-sm rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                            Снять выбор
                        </button>
                        <button type="button" onclick="showBulkPurgeModal()" class="px-3 py-1.5 text-sm rounded bg-red-600 text-white hover:bg-red-700">
                            Удалить выбранных…
                        </button>
                    </div>
                </div>
            </div>

            <!-- Список пользователей -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="lg:hidden divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($users as $user)
                            @php $canPurge = !$user->isAdmin() && $user->id !== auth()->id(); @endphp
                            <div @class(['p-4 space-y-3', 'bg-red-50 dark:bg-red-900/20' => $user->is_banned])>
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="flex items-start gap-3 min-w-0 flex-1">
                                        <input
                                            type="checkbox"
                                            class="user-select-cb mt-1 rounded border-gray-300 dark:border-gray-600"
                                            value="{{ $user->id }}"
                                            data-nickname="{{ $user->nickname }}"
                                            @disabled(! $canPurge)
                                            @if($canPurge) onchange="onUserCheckboxChange(this)" @endif
                                            title="{{ $canPurge ? 'Выбрать' : 'Нельзя выбрать' }}"
                                        >
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                {{ $user->nickname }}
                                            </a>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 truncate" title="{{ $user->email }}">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $user->created_at?->format('d.m.Y') ?? '—' }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="px-2 py-0.5 text-xs rounded {{ $user->role === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                        {{ $user->role === 'admin' ? 'Админ' : 'Пользователь' }}
                                    </span>
                                    @include('admin.partials.user-status-badge', ['user' => $user])
                                    <span class="text-gray-500 dark:text-gray-400">Отчётов: {{ $user->reports_count }}</span>
                                </div>
                                @include('admin.partials.user-actions', ['user' => $user])
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden lg:block">
                    <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="w-[3%] px-2 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        id="selectAllUsers"
                                        class="rounded border-gray-300 dark:border-gray-600"
                                        title="Выбрать всех на странице"
                                        onchange="toggleSelectAllUsers(this.checked)"
                                    >
                                </th>
                                <th class="w-[12%] px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Никнейм</th>
                                <th class="w-[20%] px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                                <th class="w-[5%] px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Роль</th>
                                <th class="w-[14%] px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Статус</th>
                                <th class="w-[9%] px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase whitespace-nowrap">
                                    <a href="{{ route('admin.users', array_merge(request()->only(['search', 'role']), ['order' => $toggleOrder])) }}"
                                       class="inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200"
                                       title="Сортировка по дате регистрации">
                                        Регистрация
                                        @if($currentOrder === 'desc')
                                            <i class="fas fa-sort-down text-indigo-600 dark:text-indigo-400" aria-hidden="true"></i>
                                        @else
                                            <i class="fas fa-sort-up text-indigo-600 dark:text-indigo-400" aria-hidden="true"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="w-[4%] px-1 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Отч.</th>
                                <th class="w-[33%] px-2 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($users as $user)
                                @php $canPurge = !$user->isAdmin() && $user->id !== auth()->id(); @endphp
                                <tr class="{{ $user->is_banned ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                    <td class="px-2 py-3 align-top text-center">
                                        <input
                                            type="checkbox"
                                            class="user-select-cb rounded border-gray-300 dark:border-gray-600"
                                            value="{{ $user->id }}"
                                            data-nickname="{{ $user->nickname }}"
                                            @disabled(! $canPurge)
                                            @if($canPurge) onchange="onUserCheckboxChange(this)" @endif
                                            title="{{ $canPurge ? 'Выбрать' : 'Нельзя выбрать' }}"
                                        >
                                    </td>
                                    <td class="px-2 py-3 align-top">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 break-words">
                                            {{ $user->nickname }}
                                        </a>
                                    </td>
                                    <td class="px-2 py-3 align-top text-sm text-gray-500 dark:text-gray-400 break-all">{{ $user->email }}</td>
                                    <td class="px-1 py-3 align-top text-center whitespace-nowrap">
                                        <span class="inline-block px-1.5 py-0.5 text-xs rounded {{ $user->role === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ $user->role === 'admin' ? 'Админ' : 'Польз.' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-3 align-top">
                                        @include('admin.partials.user-status-badge', ['user' => $user])
                                    </td>
                                    <td class="px-2 py-3 align-top text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ $user->created_at?->format('d.m.Y') ?? '—' }}
                                    </td>
                                    <td class="px-1 py-3 align-top text-sm text-gray-500 dark:text-gray-400 text-center tabular-nums">{{ $user->reports_count }}</td>
                                    <td class="px-2 py-3 align-top overflow-visible">
                                        @include('admin.partials.user-actions', ['user' => $user, 'variant' => 'table'])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

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
                                <span class="block text-sm text-gray-600 dark:text-gray-400 mt-1">Полное удаление учётной записи из базы. Email попадёт в постоянный чёрный список.</span>
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

    <!-- Модальное окно: массовое удаление -->
    <div id="bulkPurgeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-12 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-1">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Массовое удаление: <span id="bulkPurgeCount" class="font-semibold">0</span>
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    Выберите, что удалить. Это действие нельзя отменить.
                </p>
                <p id="bulkPurgeNicknames" class="text-xs text-gray-500 dark:text-gray-400 mb-4 break-words"></p>
                <form id="bulkPurgeForm" method="POST" action="{{ route('admin.users.purge-bulk') }}">
                    @csrf
                    <div id="bulkPurgeIds"></div>
                    <div class="space-y-3 mb-4">
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <input type="radio" name="purge_mode" value="content_only" class="mt-1" required>
                            <span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">Только материалы и активность</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400 mt-1">Отчёты, сны, толкования, комментарии, друзья, уведомления. Аккаунты останутся.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded border border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <input type="radio" name="purge_mode" value="full" class="mt-1">
                            <span>
                                <span class="font-medium text-red-800 dark:text-red-200">Пользователей и весь контент</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400 mt-1">Полное удаление учётных записей. Email попадут в постоянный чёрный список.</span>
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
                        <button type="button" onclick="closeBulkPurgeModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
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
        function getSelectableCheckboxes() {
            return Array.from(document.querySelectorAll('.user-select-cb:not(:disabled)'));
        }

        function syncCheckboxesById(userId, checked) {
            getSelectableCheckboxes()
                .filter((cb) => cb.value === String(userId))
                .forEach((cb) => {
                    cb.checked = checked;
                });
        }

        function getSelectedUsers() {
            const map = new Map();
            getSelectableCheckboxes().forEach((cb) => {
                if (cb.checked && !map.has(cb.value)) {
                    map.set(cb.value, cb.getAttribute('data-nickname') || cb.value);
                }
            });
            return Array.from(map.entries()).map(([id, nickname]) => ({ id, nickname }));
        }

        function getUniqueSelectableIds() {
            return Array.from(new Set(getSelectableCheckboxes().map((cb) => cb.value)));
        }

        function onUserCheckboxChange(cb) {
            syncCheckboxesById(cb.value, cb.checked);
            updateBulkToolbar();
        }

        function updateBulkToolbar() {
            const selected = getSelectedUsers();
            const toolbar = document.getElementById('bulkToolbar');
            const countEl = document.getElementById('bulkSelectedCount');
            const selectAll = document.getElementById('selectAllUsers');
            const selectableIds = getUniqueSelectableIds();

            countEl.textContent = String(selected.length);
            toolbar.classList.toggle('hidden', selected.length === 0);

            if (selectAll) {
                selectAll.checked = selectableIds.length > 0 && selected.length === selectableIds.length;
                selectAll.indeterminate = selected.length > 0 && selected.length < selectableIds.length;
            }
        }

        function toggleSelectAllUsers(checked) {
            getUniqueSelectableIds().forEach((id) => syncCheckboxesById(id, checked));
            updateBulkToolbar();
        }

        function clearUserSelection() {
            getUniqueSelectableIds().forEach((id) => syncCheckboxesById(id, false));
            updateBulkToolbar();
        }

        function showBulkPurgeModal() {
            const selected = getSelectedUsers();
            if (selected.length === 0) {
                return;
            }

            const modal = document.getElementById('bulkPurgeModal');
            const form = document.getElementById('bulkPurgeForm');
            const idsContainer = document.getElementById('bulkPurgeIds');
            const nicknames = selected.map((u) => u.nickname);

            form.reset();
            idsContainer.innerHTML = '';
            selected.forEach((u) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = u.id;
                idsContainer.appendChild(input);
            });

            document.getElementById('bulkPurgeCount').textContent =
                selected.length === 1 ? '1 пользователь' : selected.length + ' пользователей';

            const preview = nicknames.slice(0, 12).join(', ');
            const more = nicknames.length > 12 ? ' и ещё ' + (nicknames.length - 12) : '';
            document.getElementById('bulkPurgeNicknames').textContent = preview + more;

            modal.classList.remove('hidden');
        }

        function closeBulkPurgeModal() {
            document.getElementById('bulkPurgeModal').classList.add('hidden');
            document.getElementById('bulkPurgeForm').reset();
            document.getElementById('bulkPurgeIds').innerHTML = '';
        }

        document.getElementById('bulkPurgeModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeBulkPurgeModal();
            }
        });

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

        document.getElementById('banModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBanModal();
            }
        });

        updateBulkToolbar();
    </script>
</x-app-layout>
