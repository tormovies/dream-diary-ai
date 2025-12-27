@props(['user'])

<div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
    <div class="text-center">
        <div class="flex justify-center">
            <x-avatar :user="$user" size="lg" />
        </div>
        <div class="mt-4">
            <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->nickname }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->getDiaryName() }}</p>
            <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $user->reports_count ?? 0 }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Отчётов</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $user->dreams_per_month ?? 0 }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Снов/месяц</div>
                </div>
            </div>
        </div>
    </div>
</div>





















