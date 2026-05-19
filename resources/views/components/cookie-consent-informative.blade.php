<div id="cookie-informative-banner"
     class="pointer-events-auto hidden fixed inset-x-0 bottom-0 z-[100] border-t border-gray-200 bg-white/95 px-4 py-3 shadow-lg backdrop-blur-sm dark:border-gray-700 dark:bg-gray-900/95">
    <div class="mx-auto flex max-w-[1600px] flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <p class="text-sm text-gray-700 dark:text-gray-200">
            Мы используем файлы cookie для улучшения работы сайта.
            <a href="{{ route('legal.cookies') }}" class="font-medium text-purple-600 underline hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">Подробнее</a>
        </p>
        <button type="button"
                id="cookie-notice-dismiss"
                class="shrink-0 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 sm:ml-4">
            Понятно
        </button>
    </div>
</div>
