<footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-center text-sm text-gray-600 dark:text-gray-400">
            <a href="{{ route('feedback.index') }}"
               class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 hover:underline transition-colors">
                <i class="fas fa-comment-dots" aria-hidden="true"></i>
                Обратная связь
            </a>
            <span class="hidden sm:inline text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
            <a href="{{ route('legal.personal-data') }}"
               class="hover:text-gray-900 hover:underline dark:hover:text-gray-200">
                Политика обработки персональных данных
            </a>
            <span class="hidden sm:inline text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
            <a href="{{ route('legal.cookies') }}"
               class="hover:text-gray-900 hover:underline dark:hover:text-gray-200">
                Политика cookie
            </a>
            @if(config('compliance.cookie_banner_enabled'))
            <span class="hidden sm:inline text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
            <button type="button"
                    class="cursor-pointer border-0 bg-transparent p-0 text-purple-600 underline hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300"
                    onclick="window.dispatchEvent(new CustomEvent('open-cookie-settings'))">
                Настройки cookie
            </button>
            @endif
        </div>
    </div>
</footer>
