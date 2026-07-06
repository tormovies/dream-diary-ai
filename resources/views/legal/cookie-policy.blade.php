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
            (localStorage и др.) используются для хранения вашего выбора настроек cookie и идентификатора клиента
            для журнала согласий на сервере.
        </p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">3. Категории</h2>
        <p><strong>Обязательные (строго необходимые).</strong> Нужны для работы Сервиса: сессия авторизации,
            защита форм (CSRF), вход в аккаунт. Они не отключаются через настройки cookie.</p>
        <p><strong>Статистика посещений (аналитика).</strong> При вашем согласии (в режиме полного баннера) или после
            информирования (в информационном режиме) подключается Яндекс.Метрика: просмотры страниц, источники переходов,
            клики. Запись экрана (вебвизор) на сайте отключена.
            Опционально может подключаться код из раздела «Вставки на сайте» админ-панели, если оператор его указал.</p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">4. Режимы уведомления на сайте</h2>
        <p>Сервис может работать в одном из режимов (настраивается оператором):</p>
        <ul>
            <li><strong>informative</strong> (по умолчанию) — информационная полоска внизу экрана; аналитика может подключаться сразу;</li>
            <li><strong>consent</strong> — полный баннер: аналитика только после нажатия «Принять» или включения в настройках;</li>
            <li><strong>off</strong> — без баннера (аналитика подключается сразу, если настроена).</li>
        </ul>
        <p>Актуальный режим отражается в поведении сайта при первом посещении.</p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">5. Как изменить выбор</h2>
        <p>
            В режиме <strong>consent</strong>: нажмите «Принять и продолжить», «Без аналитики» или откройте
            «Настройки cookie» в футере сайта.
            В режиме <strong>informative</strong>: можно закрыть информационную полоску; для отключения аналитики
            используйте настройки браузера или режим consent, если он включён оператором.
            При отказе от аналитики мы по возможности удаляем типичные cookie Метрики на вашем устройстве.
        </p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">6. Журнал согласий</h2>
        <p>
            При изменении выбора (в режиме consent) Сервис сохраняет запись на сервере: идентификатор клиента,
            версия политики, категории согласия, время, обезличенный хеш IP-адреса, укороченный User-Agent.
            Записи хранятся до 730 дней, затем удаляются автоматически.
            Подробнее см. <a href="{{ route('legal.personal-data') }}">политику обработки персональных данных</a>.
        </p>

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-8">7. Обновления</h2>
        <p>Политика может обновляться. Номер редакции и дата указаны в начале документа. После существенных изменений
            Сервис может запросить повторный выбор; ранее данное согласие на аналитику сохраняется, если вы его не отзывали.</p>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-8">
            Вопросы: <a href="mailto:{{ $operator['email'] }}">{{ $operator['email'] }}</a>
        </p>
    </article>
</div>
@endsection
