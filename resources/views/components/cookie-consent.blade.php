@if(config('compliance.cookie_banner_enabled') && !request()->is('admin*'))
<div id="cookie-consent-mount" class="pointer-events-none fixed inset-x-0 bottom-0 z-[100] flex justify-center px-4 pb-4 sm:pb-6">
    <div id="cookie-consent-banner"
         class="pointer-events-auto hidden w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-600 dark:bg-gray-800 sm:p-5">
        <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
            Мы используем cookie для входа и работы сайта. С вашего согласия подключаем
            <strong class="font-medium">Яндекс.Метрику</strong> — обезличенная статистика, чтобы улучшать сервис
            (без записи экрана).
            <a href="{{ route('legal.cookies') }}" class="text-purple-600 underline hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">Политика cookie</a>.
        </p>
        <div class="mt-4 flex flex-col gap-2">
            <button type="button"
                    id="cookie-btn-accept"
                    class="w-full rounded-lg bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600">
                Принять и продолжить
            </button>
            <button type="button"
                    id="cookie-btn-settings"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-500 dark:text-gray-100 dark:hover:bg-gray-700">
                Настроить
            </button>
            <button type="button"
                    id="cookie-btn-reject-analytics"
                    class="w-full py-1 text-xs text-gray-500 underline hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Без аналитики — только обязательные cookie
            </button>
        </div>
    </div>
</div>

<div id="cookie-consent-modal" class="fixed inset-0 z-[101] hidden items-center justify-center bg-black/50 p-4" aria-modal="true" role="dialog">
    <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-600 dark:bg-gray-800">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Настройки cookie</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Обязательные cookie нужны для авторизации и безопасности. Аналитику можно включить или отключить.
        </p>
        <ul class="mt-4 space-y-4">
            <li class="flex items-start justify-between gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/40">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Обязательные</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Сессия, CSRF, вход в аккаунт.</p>
                </div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Всегда вкл.</span>
            </li>
            <li class="flex items-start justify-between gap-3 rounded-lg border border-gray-100 p-3 dark:border-gray-700">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Статистика посещений</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Яндекс.Метрика: страницы, источники переходов, клики. Запись экрана (вебвизор) отключена.</p>
                </div>
                <input type="checkbox" id="cookie-opt-analytics" checked
                       class="mt-1 h-5 w-5 shrink-0 rounded border-gray-300 text-purple-600 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800">
            </li>
        </ul>
        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="cookie-modal-cancel"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-600 dark:text-gray-200">
                Отмена
            </button>
            <button type="button" id="cookie-modal-save"
                    class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                Сохранить
            </button>
        </div>
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
            Подробнее: <a href="{{ route('legal.cookies') }}" class="text-purple-600 underline dark:text-purple-400">политика cookie</a>,
            <a href="{{ route('legal.personal-data') }}" class="text-purple-600 underline dark:text-purple-400">политика ПДн</a>.
        </p>
    </div>
</div>
@endif
