@if(!request()->is('admin*'))
<div id="cookie-consent-mount" class="pointer-events-none fixed inset-x-0 bottom-0 z-[100] flex justify-center px-4 pb-4 sm:pb-6">
    <div id="cookie-consent-banner"
         class="pointer-events-auto hidden w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-600 dark:bg-gray-800 sm:p-5">
        <p class="text-sm text-gray-700 dark:text-gray-200">
            Мы используем файлы cookie и похожие технологии: обязательные — для входа и работы сайта;
            аналитические — только с вашего согласия (
            <a href="{{ route('legal.cookies') }}" class="text-purple-600 underline hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">политика cookie</a>).
        </p>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
            <button type="button"
                    id="cookie-btn-necessary"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-500 dark:text-gray-100 dark:hover:bg-gray-700">
                Только обязательные
            </button>
            <button type="button"
                    id="cookie-btn-accept-all"
                    class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600">
                Принять все
            </button>
            <button type="button"
                    id="cookie-btn-settings"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-purple-600 hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-900/30">
                Настройки
            </button>
        </div>
    </div>
</div>

<div id="cookie-consent-modal" class="fixed inset-0 z-[101] hidden items-center justify-center bg-black/50 p-4" aria-modal="true" role="dialog">
    <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-600 dark:bg-gray-800">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Настройки cookie</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Управляйте категориями. Обязательные cookie нужны для авторизации и безопасности и отключить их нельзя.
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
                    <p class="font-medium text-gray-900 dark:text-white">Аналитика и рекламные пиксели</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Яндекс.Метрика и код из раздела «Реклама» в настройках сайта.</p>
                </div>
                <input type="checkbox" id="cookie-opt-analytics"
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
