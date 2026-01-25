@props(['user', 'size' => 'md'])

@php
    if (!$user) {
        $user = (object)['avatar' => null, 'nickname' => 'U'];
    }
    
    $sizeClasses = [
        'sm' => 'w-8 h-8',
        'md' => 'w-12 h-12',
        'lg' => 'w-24 h-24',
        'xl' => 'w-32 h-32',
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    
    $textSizes = [
        'sm' => 'text-sm',
        'md' => 'text-lg',
        'lg' => 'text-2xl',
        'xl' => 'text-3xl',
    ];
    $textSize = $textSizes[$size] ?? $textSizes['md'];
    
    if ($user && $user->avatar) {
        $emoji = \App\Helpers\AvatarHelper::getEmoji($user->avatar);
        $bgColor = \App\Helpers\AvatarHelper::getBackgroundColor($user->avatar);
        $darkBgColor = \App\Helpers\AvatarHelper::getDarkBackgroundColor($user->avatar);
    } else {
        $emoji = strtoupper(substr($user->nickname ?? 'U', 0, 1));
        $bgColor = 'bg-gray-200';
        $darkBgColor = 'dark:bg-gray-700';
    }
    
    // Формируем aria-label для доступности
    $userName = $user->nickname ?? $user->name ?? 'Пользователь';
    $ariaLabel = "Аватар пользователя {$userName}";
@endphp

<div class="{{ $sizeClass }} rounded-full {{ $bgColor }} {{ $darkBgColor }} flex items-center justify-center border-2 border-gray-300 dark:border-gray-600 flex-shrink-0" role="img" aria-label="{{ $ariaLabel }}">
    @if($user->avatar)
        <span class="{{ $textSize }}">{{ $emoji }}</span>
    @else
        <span class="font-bold text-gray-600 dark:text-gray-300 {{ $textSize === 'text-3xl' ? 'text-2xl' : ($textSize === 'text-2xl' ? 'text-xl' : ($textSize === 'text-lg' ? 'text-base' : 'text-sm')) }}">{{ $emoji }}</span>
    @endif
</div>
