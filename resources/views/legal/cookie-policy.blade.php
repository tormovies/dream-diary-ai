@extends('layouts.base')

@section('title', 'Политика использования cookie — '.config('app.name'))

@section('content')
<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 min-w-0">
    <article class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-8 card-shadow border border-gray-200 dark:border-gray-700 prose prose-gray dark:prose-invert max-w-none">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">Политика использования файлов cookie</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            Редакция {{ $policyVersion }} от {{ $effectiveDate }}
        </p>

        <p>
            Настоящая Политика применяется к сайту Сервиса «{{ config('app.name') }}» и поясняет, какие технологии мы используем,
            для каких целей и как вы можете управлять настройками.
        </p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">1. Оператор</h2>
        @include('legal.partials.operator')

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">2. Что такое cookie</h2>
        <p>
            Cookie — небольшие фрагменты данных, которые браузер сохраняет на устройстве пользователя. Похожие технологии
            (local storage и др.) могут использоваться для хранения вашего выбора настроек cookie и идентификатора клиента
            для журнала согласий на сервере.
        </p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">3. Категории</h2>
        <p><strong>Обязательные (строго необходимые).</strong> Нужны для работы Сервиса: например, поддержание сессии,
            защита форм (CSRF), вход в аккаунт. Они не отключаются через настройки cookie.</p>
        <p><strong>Статистика посещений (аналитика).</strong> При вашем согласии подключается Яндекс.Метрика для обезличенной
            статистики: просмотры страниц, источники переходов, клики. Запись экрана (вебвизор) на сайте отключена.
            Опционально может подключаться дополнительный код в разделе «Вставки на сайте» админ-панели, если оператор его указал.
            Аналитика не включается без согласия в баннере cookie.</p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">4. Как изменить выбор</h2>
        <p>
            Нажмите «Принять и продолжить», «Без аналитики» или откройте «Настройки cookie» в футере сайта.
            При отказе от аналитики мы по возможности удаляем типичные cookie аналитики на вашем устройстве для текущего сайта.
            Полное удаление может потребовать очистки данных браузера вручную.
        </p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">5. Журнал согласий</h2>
        <p>
            При изменении выбора Сервис может сохранять минимальную запись на сервере (идентификатор клиента, версия политики,
            категории согласия, время, обезличенный хеш IP-адреса, укороченный User-Agent) для подтверждения факта выбора.
            Подробнее о персональных данных см. <a href="{{ route('legal.personal-data') }}">политику обработки персональных данных</a>.
        </p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">6. Обновления</h2>
        <p>Политика может обновляться. Номер редакции и дата указаны в начале документа. После существенных изменений
            Сервис может запросить повторный выбор; ранее данное согласие на аналитику сохраняется, если вы его не отзывали.</p>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-8">
            Вопросы: <a href="mailto:{{ $operator['email'] }}">{{ $operator['email'] }}</a>
        </p>
    </article>
</div>
@endsection
