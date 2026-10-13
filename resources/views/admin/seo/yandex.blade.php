<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">SEO / Яндекс</h2>
            <a href="{{ route('admin.seo.index') }}">Управление SEO</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-[1600px] mx-auto px-6 text-gray-900 dark:text-gray-100" x-data="{
        panel: null, fields: {}, original: {}, error: '', busy: false, confirmed: false, suggestions: [], usedFallback: false,
        async suggestSeo() {
            this.error = ''; this.busy = true; this.suggestions = [];
            try {
                const response = await fetch(this.panel.endpoint + '/suggestions', {
                    method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'ИИ недоступен');
                this.suggestions = data.suggestions; this.usedFallback = data.used_fallback;
            } catch (e) { this.error = e.message; } finally { this.busy = false; }
        },
        async openPage(url) {
            this.error = ''; this.busy = true;
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Не удалось открыть страницу');
                this.panel = data; this.panel.endpoint = url; this.suggestions = []; this.usedFallback = false;
                this.fields = { ...data.fields }; this.original = { ...data.fields }; this.confirmed = false;
            } catch (e) { this.error = e.message; } finally { this.busy = false; }
        },
        async savePage() {
            this.error = ''; this.busy = true;
            try {
                const response = await fetch(this.panel.endpoint, {
                    method: 'PATCH', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ ...this.fields, confirmed: this.confirmed })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Не удалось сохранить SEO');
                const endpoint = this.panel.endpoint; this.panel = data; this.panel.endpoint = endpoint;
                this.fields = { ...data.fields }; this.original = { ...data.fields }; this.confirmed = false;
            } catch (e) { this.error = e.message; } finally { this.busy = false; }
        }
    }">
        <p x-show="error" x-text="error" role="alert" class="mb-4 text-red-600"></p>
        @if(session('success'))<div class="p-4 mb-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="p-4 mb-4 bg-red-100 text-red-800 rounded">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <nav class="flex flex-wrap gap-4 mb-6">
            @foreach(['pages' => 'Страницы', 'queries' => 'Запросы', 'overview' => 'Обзор', 'loading' => 'Загрузка данных'] as $key => $label)
                <a href="{{ route('admin.seo.yandex.index', ['tab' => $key]) }}" class="px-4 py-2 rounded {{ $tab === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <p class="mb-4 text-sm">{{ $from }} — {{ $to }} · последние 30 дней. Свежие данные предварительные; статистика накапливается по мере загрузки.</p>
        @if(in_array($tab, ['pages', 'queries']))
            <form method="GET" class="flex flex-wrap gap-3 mb-4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input name="search" value="{{ request('search') }}" placeholder="{{ $tab === 'pages' ? 'Поиск URL' : 'Поиск запроса' }}" class="rounded border-gray-300 dark:bg-gray-700">
                <select name="sort" class="rounded dark:bg-gray-700">@foreach(['impressions' => 'Показы', 'clicks' => 'Клики', 'ctr' => 'CTR'] as $key => $label)<option value="{{ $key }}" @selected(request('sort', 'impressions') === $key)>{{ $label }}</option>@endforeach</select>
                <select name="direction" class="rounded dark:bg-gray-700"><option value="desc">По убыванию</option><option value="asc" @selected(request('direction') === 'asc')>По возрастанию</option></select>
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Показать</button>
            </form>
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow">
                <table class="w-full text-left"><thead><tr class="border-b"><th class="p-4">{{ $tab === 'pages' ? 'URL' : 'Запрос' }}</th><th class="p-4">Показы</th><th class="p-4">Клики</th><th class="p-4">CTR</th></tr></thead>
                    <tbody>@forelse($rows as $row)<tr class="border-b"><td class="p-4 break-all">@if($tab === 'pages')<button type="button" class="text-blue-600 underline" @click="openPage(@js(route('admin.seo.yandex.detail', $row->id)))" :disabled="busy">{{ $row->path }}</button>@else{{ $row->query }}@endif</td><td class="p-4">{{ number_format($row->impressions, 0, ',', ' ') }}</td><td class="p-4">{{ number_format($row->clicks, 0, ',', ' ') }}</td><td class="p-4">{{ number_format($row->ctr, 2, ',', ' ') }}%</td></tr>@empty<tr><td colspan="4" class="p-6">Данные пока не загружены. Настройте подключение в разделе «Загрузка данных».</td></tr>@endforelse</tbody>
                </table>
            </div>
            <div class="mt-4">{{ $rows->links() }}</div>
            <p class="mt-4 text-sm">Список страниц основан на примерах Яндекса (до 50 000), а не на полном индексе. Нулевые показы не означают отсутствие страницы в поиске.</p>
        @elseif($tab === 'overview')
            <div class="bg-white dark:bg-gray-800 p-6 rounded space-y-3">
                <p>Показы: {{ number_format($totals->impressions, 0, ',', ' ') }}</p>
                <p>Клики: {{ number_format($totals->clicks, 0, ',', ' ') }}</p>
                <p>CTR: {{ number_format($totals->impressions > 0 ? 100 * $totals->clicks / $totals->impressions : 0, 2, ',', ' ') }}%</p>
                <p>Загружено URL-дней за период: {{ $coverage }}</p>
                <p>Бесплатная квота: до 100 URL-дней в сутки. Чанки не обходят ограничение; платная квота отключена.</p>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 p-6 rounded mb-6 space-y-4">
                <p>Последний цикл: {{ $settings['last_tick'] ?: 'ещё не выполнялся' }}. Остаток бесплатной квоты, сообщённый Яндексом: {{ $settings['free_remaining'] === null ? 'неизвестен' : (int) $settings['free_remaining'] }}.</p>
                @if($settings['last_error'])<p class="text-red-600">{{ $settings['last_error'] }}</p>@endif
                <form method="POST" action="{{ route('admin.seo.yandex.connect') }}" class="flex flex-wrap gap-3">@csrf
                    <input type="password" name="token" autocomplete="new-password" placeholder="{{ $settings['token_set'] ? 'Новый OAuth-токен (необязательно)' : 'OAuth-токен' }}" class="rounded dark:bg-gray-700" maxlength="4096">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded">Проверить подключение и получить сайты</button>
                </form>
                <form method="POST" action="{{ route('admin.seo.yandex.settings') }}" class="flex flex-wrap items-center gap-3">@csrf
                    <select name="host_id" required class="rounded dark:bg-gray-700"><option value="">Выберите сайт</option>@foreach(($settings['hosts'] ?: []) as $host)@if($host['verified'] ?? false)<option value="{{ $host['host_id'] }}" @selected($settings['host_id'] === $host['host_id'])>{{ $host['unicode_host_url'] ?? $host['ascii_host_url'] ?? $host['host_id'] }}</option>@endif
                    @endforeach</select>
                    <label><input type="checkbox" name="enabled" value="1" @checked($settings['enabled'])> Ежедневное накопление (проверка каждые 5 минут)</label>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded">Сохранить</button>
                </form>
                <p class="text-sm">OAuth-токен хранится зашифрованным и не выводится обратно. Для загрузки нужен работающий планировщик Laravel. Готовая выгрузка обычно появляется через 20 минут–2 часа, иногда до суток.</p>
            </div>
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded"><table class="w-full text-left"><thead><tr><th class="p-3">Задача</th><th class="p-3">Статус</th><th class="p-3">URL-дней</th><th class="p-3">Ошибка / восстановление</th></tr></thead><tbody>
                @forelse($tasks as $task)<tr class="border-t"><td class="p-3">#{{ $task->id }}<br>{{ $task->created_at }}</td><td class="p-3">{{ $task->status }}</td><td class="p-3">{{ $task->quota_used }}</td><td class="p-3">{{ $task->error }}
                    @if(in_array($task->status, ['failed', 'format_error', 'unknown', 'queued', 'submitting']))
                        <form method="POST" action="{{ route('admin.seo.yandex.recover', $task->id) }}" class="mt-2 space-y-2">@csrf
                            @if($task->status !== 'format_error')<label class="block"><input type="checkbox" name="new_export" value="1" required> Подтверждаю новую выгрузку и возможное повторное расходование квоты</label>@endif
                            <button class="bg-gray-200 dark:bg-gray-700 px-3 py-2 rounded">{{ $task->status === 'format_error' ? 'Повторно скачать без новой выгрузки' : 'Разрешить повторную загрузку' }}</button>
                        </form>
                    @endif
                </td></tr>@empty<tr><td colspan="4" class="p-4">Задач пока нет.</td></tr>@endforelse
            </tbody></table></div><div class="mt-4">{{ $tasks->appends(['tab' => 'loading'])->links() }}</div>
        @endif
        <template x-if="panel">
            <div class="fixed inset-0 z-50 bg-black/40" @click.self="panel = null" @keydown.escape.window="panel = null">
                <aside role="dialog" aria-modal="true" aria-label="SEO страницы" class="absolute right-0 top-0 h-full w-full max-w-2xl overflow-y-auto bg-white dark:bg-gray-800 p-6 shadow-xl">
                    <button type="button" class="float-right" @click="panel = null">Закрыть</button>
                    <h3 class="text-xl font-semibold mb-4">SEO страницы</h3>
                    <p class="break-all mb-4" x-text="panel.page.path"></p>
                    <p class="text-sm mb-4">Пустое поле возвращает стандартный шаблон. Заголовок в поиске Яндекса может отличаться от текущего title.</p>
                    <p class="mb-4" x-text="'Заголовок Яндекса: ' + (panel.page.search_title || 'не получен')"></p>
                    <h4 class="font-semibold mb-2">Текущие SEO-поля</h4>
                    <template x-for="field in ['title', 'description', 'h1']" :key="field"><p class="mb-2 break-all" x-text="field + ': ' + (panel.current?.[field] || panel.fields[field] || 'Не удалось определить')"></p></template>
                    <form @submit.prevent="savePage()" class="space-y-4">
                        <template x-for="field in ['title', 'description', 'h1']" :key="field">
                            <label class="block"><span x-text="field"></span>
                                <textarea x-model="fields[field]" :maxlength="field === 'description' ? 500 : 255" class="block w-full rounded dark:bg-gray-700"></textarea>
                                <span class="text-sm block break-all" x-text="(original[field] || panel.current?.[field] || 'Шаблон') + ' → ' + (fields[field] || 'Шаблон')"></span>
                            </label>
                        </template>
                        <label class="block"><input type="checkbox" x-model="confirmed" required> Подтверждаю ручное применение изменений</label>
                        <p x-show="error" x-text="error" class="text-red-600"></p>
                        <button :disabled="busy || !confirmed" class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50">Применить SEO</button>
                    </form>
                    <h4 class="font-semibold mt-6 mb-3">ИИ-рекомендации</h4>
                    <p class="text-sm mb-3">ИИ использует URL, заголовок Яндекса, ручные SEO-поля и загруженные запросы. Полный текст страницы не передаётся. Варианты не применяются автоматически.</p>
                    <button type="button" @click="suggestSeo()" :disabled="busy" class="bg-purple-600 text-white px-4 py-2 rounded disabled:opacity-50">Получить варианты</button>
                    <p x-show="suggestions.length" x-text="usedFallback ? 'Использован резервный канал' : 'Использован основной канал'" class="my-3 text-sm"></p>
                    <template x-for="(suggestion, index) in suggestions" :key="index">
                        <div class="border rounded p-4 my-3 space-y-2">
                            <template x-for="field in ['title', 'description', 'h1']" :key="field"><p class="break-all" x-text="field + ': ' + suggestion[field]"></p></template>
                            <p class="text-sm" x-text="suggestion.reason"></p>
                            <button type="button" @click="fields = { title: suggestion.title, description: suggestion.description, h1: suggestion.h1 }; confirmed = false" class="text-blue-600 underline">Перенести в черновик для проверки</button>
                        </div>
                    </template>
                    <h4 class="font-semibold mt-6 mb-3">Запросы за 30 дней</h4>
                    <template x-for="query in panel.queries" :key="query.query"><p class="mb-2" x-text="query.query + ' · показы: ' + query.impressions + ' · клики: ' + query.clicks"></p></template>
                    <h4 class="font-semibold mt-6 mb-3">История изменений</h4>
                    <template x-for="change in panel.history" :key="change.id">
                        <div class="border-t py-3"><p x-text="change.created_at"></p>
                            <template x-for="field in ['title', 'description', 'h1']" :key="field"><p class="break-all text-sm" x-text="field + ': ' + (change.before[field] || 'Шаблон') + ' → ' + (change.after[field] || 'Шаблон')"></p></template>
                        </div>
                    </template>
                </aside>
            </div>
        </template>
    </div>
</x-app-layout>
