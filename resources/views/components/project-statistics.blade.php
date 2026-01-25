@props(['stats'])

@php
    // Убеждаемся, что все необходимые поля есть
    $stats = array_merge([
        'users' => 0,
        'reports' => 0,
        'dreams' => 0,
        'interpretations' => 0,
    ], $stats ?? []);
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
        <i class="fas fa-chart-bar"></i> Статистика проекта
    </h3>
    
    {{-- Grid вариант (2x2) --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['users'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Пользователей</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['reports'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Отчетов</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['dreams'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Снов</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">{{ number_format($stats['interpretations'] ?? 0, 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Толкований</div>
        </div>
    </div>
</div>
