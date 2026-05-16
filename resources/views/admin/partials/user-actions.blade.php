@props(['user', 'variant' => 'card'])

@php
    $isTable = $variant === 'table';
    $btn = $isTable
        ? 'inline-flex items-center rounded border px-1.5 py-0.5 text-xs font-medium leading-tight transition-colors whitespace-nowrap'
        : 'inline-flex items-center rounded border px-2 py-1 text-xs font-medium transition-colors whitespace-nowrap';
@endphp

<div @class([
    'flex min-w-0 flex-wrap',
    'gap-1' => $isTable,
    'gap-1.5' => ! $isTable,
])>
    <a href="{{ route('admin.users.edit', $user) }}" class="{{ $btn }} border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-200 dark:hover:bg-blue-900/50">
        {{ $isTable ? 'Редакт.' : 'Редактировать' }}
    </a>
    <a href="{{ route('users.profile', $user) }}" class="{{ $btn }} border-green-200 bg-green-50 text-green-800 hover:bg-green-100 dark:border-green-800 dark:bg-green-950/40 dark:text-green-200 dark:hover:bg-green-900/50">
        Профиль
    </a>
    @if(!$user->hasVerifiedEmail() && !$user->is_banned)
        <form method="POST" action="{{ route('admin.users.verify-email', $user) }}" class="inline-flex">
            @csrf
            <button type="submit" title="Подтвердить email" class="{{ $btn }} border-teal-200 bg-teal-50 text-teal-800 hover:bg-teal-100 dark:border-teal-800 dark:bg-teal-950/40 dark:text-teal-200 dark:hover:bg-teal-900/50" onclick="return confirm('Подтвердить email пользователя {{ $user->nickname }}? На его почту будет отправлено уведомление.')">
                {{ $isTable ? 'Почта' : 'Подтвердить почту' }}
            </button>
        </form>
    @endif
    @if(!$user->isAdmin() && $user->id !== auth()->id())
        @if($user->is_banned)
            <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline-flex">
                @csrf
                <button type="submit" title="Разблокировать" class="{{ $btn }} border-green-200 bg-green-50 text-green-800 hover:bg-green-100 dark:border-green-800 dark:bg-green-950/40 dark:text-green-200 dark:hover:bg-green-900/50" onclick="return confirm('Разблокировать пользователя {{ $user->nickname }}?')">
                    {{ $isTable ? 'Разблок.' : 'Разблокировать' }}
                </button>
            </form>
        @else
            <button type="button" title="Заблокировать" onclick="showBanModal({{ $user->id }}, '{{ $user->nickname }}')" class="{{ $btn }} border-orange-200 bg-orange-50 text-orange-800 hover:bg-orange-100 dark:border-orange-800 dark:bg-orange-950/40 dark:text-orange-200 dark:hover:bg-orange-900/50">
                {{ $isTable ? 'Блок' : 'Заблокировать' }}
            </button>
        @endif
        <button
            type="button"
            title="Удалить пользователя"
            class="{{ $btn }} border-red-200 bg-red-50 text-red-800 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200 dark:hover:bg-red-900/50"
            data-action-url="{{ route('admin.users.purge', $user) }}"
            data-nickname="{{ $user->nickname }}"
            onclick="showPurgeModal(this)"
        >
            Удалить…
        </button>
    @endif
</div>
