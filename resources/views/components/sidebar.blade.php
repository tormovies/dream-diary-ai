@props(['user' => null, 'showWelcome' => true, 'showUserCard' => true, 'showMenu' => true])

@if($user)
<aside class="space-y-6">
    @if($showWelcome)
    <!-- Приветственная карточка -->
    <x-welcome-card :user="$user" />
    @endif
    
    @if($showUserCard)
    <!-- Карточка пользователя -->
    <x-user-card :user="$user" />
    @endif
    
    @if($showMenu && isset($menu))
    <!-- Меню -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
        {{ $menu }}
    </div>
    @endif
    
    @if(isset($slot) && $slot->isNotEmpty())
    <!-- Дополнительный контент -->
    {{ $slot }}
    @endif
</aside>
@endif



















