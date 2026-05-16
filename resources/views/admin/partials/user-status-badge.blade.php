@props(['user'])

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
