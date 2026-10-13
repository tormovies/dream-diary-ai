<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class YandexSeoImportService
{
    public function __construct(private YandexWebmasterService $api) {}

    public function tick(): void
    {
        $lock = Cache::lock('yandex-seo-import', 900);
        if (! $lock->get()) {
            return;
        }
        try {
            if (! Setting::getValue('yandex_seo_enabled', false)) {
                return;
            }
            $host = (string) Setting::getValue('yandex_seo_host_id', '');
            $this->poll($host);
            $this->syncPages($host);
            $this->enqueue($host);
            $this->prune();
            Setting::setValue('yandex_seo_last_error', null);
            Setting::setValue('yandex_seo_last_tick', now()->toIso8601String());
        } catch (\Throwable $e) {
            Setting::setValue('yandex_seo_last_error', $e->getMessage());
            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function syncPages(string $host): void
    {
        $state = Setting::getValue('yandex_seo_index_state', []);
        if (($state['host'] ?? null) !== $host) {
            $state = [];
        }
        if (isset($state['completed_at']) && Carbon::parse($state['completed_at'])->gt(now()->subDay())) {
            return;
        }
        $offset = (int) ($state['offset'] ?? 0);
        $started = $state['started_at'] ?? now()->toDateTimeString();
        $data = $this->api->request('GET', $this->api->hostPath('search-urls/in-search/samples'), ['offset' => $offset, 'limit' => 100]);
        foreach ($data['samples'] ?? [] as $sample) {
            $url = (string) ($sample['url'] ?? '');
            $path = $this->pathForUrl($url);
            if ($path === null) {
                continue;
            }
            DB::table('yandex_seo_pages')->upsert([[
                'host_id' => $host, 'url_hash' => hash('sha256', $url),
                'url' => $url, 'path' => $path, 'search_title' => $sample['title'] ?? null,
                'in_search_seen_at' => now(), 'search_version_at' => isset($sample['last_access']) ? Carbon::parse(str_replace(',', '.', $sample['last_access'])) : null,
                'created_at' => now(), 'updated_at' => now(),
            ]], ['host_id', 'url_hash'], ['url', 'path', 'search_title', 'in_search_seen_at', 'search_version_at', 'updated_at']);
        }
        $offset += count($data['samples'] ?? []);
        $complete = $offset >= min(50000, (int) ($data['count'] ?? 0)) || empty($data['samples']);
        Setting::setValue('yandex_seo_index_state', [
            'host' => $host, 'offset' => $complete ? 0 : $offset, 'started_at' => $complete ? null : $started,
            'completed_at' => $complete ? now()->toDateTimeString() : null,
            'count' => (int) ($data['count'] ?? 0), 'snapshot_started_at' => $started,
        ]);
    }

    public function pathForUrl(string $url): ?string
    {
        $base = (string) Setting::getValue('yandex_seo_host_url', '');
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || strtolower($parts['host'] ?? '') !== strtolower((string) parse_url($base, PHP_URL_HOST))) {
            return null;
        }

        return ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    private function enqueue(string $host): void
    {
        if (DB::table('yandex_seo_tasks')->where('host_id', $host)->whereIn('status', ['queued', 'submitted', 'submitting', 'unknown'])->exists()) {
            return;
        }
        $dates = $this->api->request('GET', $this->api->hostPath('pro/serp/dates'))['dates'] ?? [];
        $from = now('Europe/Moscow')->subDays(30)->toDateString();
        $to = now('Europe/Moscow')->subDay()->toDateString();
        $dates = array_values(array_filter($dates, fn ($date) => $date >= $from && $date <= $to));
        sort($dates);
        Setting::setValue('yandex_seo_available_dates', $dates);
        if (! $dates) {
            return;
        }
        $limits = $this->api->request('GET', $this->api->hostPath('pro/limits'));
        Setting::setValue('yandex_seo_limits', $limits);
        $day = now('Europe/Moscow')->toDateString();
        $used = DB::table('yandex_seo_tasks')->where('host_id', $host)->where('use_pro_tariff', false)
            ->where('created_at', '>=', Carbon::parse($day, 'Europe/Moscow')->utc())->sum('quota_used');
        $remaining = max(0, 100 - $used);
        if (Setting::getValue('yandex_seo_quota_day') === $day && Setting::getValue('yandex_seo_quota_host') === $host) {
            $reported = Setting::getValue('yandex_seo_free_remaining');
            if (is_numeric($reported) || is_bool($reported)) {
                $remaining = min($remaining, max(0, (int) $reported));
            }
        }
        if ($remaining < 1) {
            return;
        }
        $state = Setting::getValue('yandex_seo_index_state', []);
        $pages = DB::table('yandex_seo_pages')->where('host_id', $host)->whereNotNull('in_search_seen_at');
        if (! empty($state['snapshot_started_at'])) {
            $pages->where('in_search_seen_at', '>=', $state['snapshot_started_at']);
        }
        foreach ($pages->orderBy('id')->cursor() as $page) {
            $covered = DB::table('yandex_seo_coverage')->where('page_id', $page->id)->whereIn('date', $dates)->pluck('date')->all();
            $missing = array_values(array_diff($dates, $covered));
            if (! $missing) {
                continue;
            }
            $chunk = array_slice($missing, 0, min($remaining, 30));
            $id = DB::transaction(function () use ($host, $page, $chunk) {
                $id = DB::table('yandex_seo_tasks')->insertGetId([
                    'host_id' => $host, 'status' => 'queued', 'page_ids' => json_encode([$page->id]), 'dates' => json_encode($chunk),
                    'quota_used' => count($chunk), 'created_at' => now(), 'updated_at' => now(),
                ]);
                foreach ($chunk as $date) {
                    DB::table('yandex_seo_coverage')->insert(['page_id' => $page->id, 'date' => $date, 'task_id' => $id, 'status' => 'queued']);
                }

                return $id;
            });
            DB::table('yandex_seo_tasks')->where('id', $id)->update(['status' => 'submitting', 'updated_at' => now()]);
            try {
                $response = $this->api->request('POST', $this->api->hostPath('pro/serp/queries/download/'), [
                    'dates' => $chunk, 'paths' => [$page->path], 'region_ids' => [], 'use_pro_tariff' => 'false',
                ]);
                if (empty($response['task_id'])) {
                    throw new RuntimeException('Яндекс не вернул task_id.');
                }
                DB::table('yandex_seo_tasks')->where('id', $id)->update(['status' => 'submitted', 'remote_id' => $response['task_id'], 'updated_at' => now()]);
                Setting::setValue('yandex_seo_free_remaining', $response['free_quota_remaining'] ?? null);
                Setting::setValue('yandex_seo_quota_day', $day);
                Setting::setValue('yandex_seo_quota_host', $host);
            } catch (\Throwable $e) {
                DB::table('yandex_seo_tasks')->where('id', $id)->update(['status' => 'unknown', 'error' => 'Не удалось подтвердить создание выгрузки. Автоповтор отключён, чтобы не расходовать квоту повторно.', 'updated_at' => now()]);
                throw $e;
            }

            return;
        }
    }

    private function prune(): void
    {
        $day = now('Europe/Moscow')->toDateString();
        if (Setting::getValue('yandex_seo_pruned_day') === $day) {
            return;
        }
        $cutoff = now('Europe/Moscow')->subDays(35)->toDateString();
        DB::table('yandex_seo_metrics')->where('date', '<', $cutoff)->delete();
        DB::table('yandex_seo_coverage')->where('date', '<', $cutoff)->delete();
        Setting::setValue('yandex_seo_pruned_day', $day);
    }

    private function poll(string $host): void
    {
        $tasks = DB::table('yandex_seo_tasks')->where('host_id', $host)->where('status', 'submitted')->limit(5)->get();
        foreach ($tasks as $task) {
            $data = $this->api->request('GET', $this->api->hostPath('pro/serp/queries/download/'.rawurlencode($task->remote_id)));
            DB::table('yandex_seo_tasks')->where('id', $task->id)->update(['checked_at' => now()]);
            if (($data['download_status'] ?? '') === 'FAILED') {
                DB::table('yandex_seo_tasks')->where('id', $task->id)->update(['status' => 'failed', 'error' => 'Яндекс отклонил выгрузку: '.($data['error_code'] ?? 'FAILED')]);

                continue;
            }
            if (($data['download_status'] ?? '') !== 'SUCCESS' || empty($data['url'])) {
                continue;
            }
            try {
                $this->importBody($task->id, $this->api->download($data['url']));
            } catch (\Throwable $e) {
                DB::table('yandex_seo_tasks')->where('id', $task->id)->update(['status' => 'format_error', 'error' => $e->getMessage(), 'updated_at' => now()]);
            }
        }
    }

    public function recoverTask(int $taskId, bool $allowNewExport = false): void
    {
        $lock = Cache::lock('yandex-seo-import', 900);
        if (! $lock->get()) {
            throw new RuntimeException('Импорт уже выполняется. Повторите позже.');
        }
        try {
            DB::transaction(function () use ($taskId, $allowNewExport) {
                $task = DB::table('yandex_seo_tasks')->where('id', $taskId)->lockForUpdate()->first();
                if (! $task || $task->host_id !== (string) Setting::getValue('yandex_seo_host_id', '')) {
                    throw new RuntimeException('Задача выбранного сайта не найдена.');
                }
                if ($task->status === 'format_error' && $task->remote_id && ! $allowNewExport) {
                    DB::table('yandex_seo_tasks')->where('id', $taskId)->update(['status' => 'submitted', 'error' => null, 'updated_at' => now()]);

                    return;
                }
                if (! $allowNewExport || ! in_array($task->status, ['failed', 'format_error', 'unknown', 'queued', 'submitting'], true)) {
                    throw new RuntimeException('Для новой выгрузки требуется явное подтверждение повторного расходования квоты.');
                }
                DB::table('yandex_seo_coverage')->where('task_id', $taskId)->where('status', '!=', 'loaded')->delete();
                DB::table('yandex_seo_tasks')->where('id', $taskId)->update(['status' => 'abandoned', 'updated_at' => now()]);
            });
        } finally {
            $lock->release();
        }
    }

    public function importBody(int $taskId, string $body): void
    {
        if (strlen($body) > 50 * 1024 * 1024) {
            throw new RuntimeException('Выгрузка превышает допустимый размер 50 МБ.');
        }
        if (str_starts_with($body, "\x1f\x8b")) {
            $body = gzdecode($body, 50 * 1024 * 1024);
            if ($body === false) {
                throw new RuntimeException('Не удалось распаковать gzip-выгрузку.');
            }
        }
        if (str_starts_with($body, 'PK')) {
            throw new RuntimeException('ZIP-формат пока не подтверждён. Требуется пример файла; задача сохранена без отметки о загрузке.');
        }
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $body);
        rewind($stream);
        $first = fgets($stream);
        if ($first === false) {
            fclose($stream);
            throw new RuntimeException('Пустой файл выгрузки: нельзя подтвердить полноту загрузки.');
        }
        $delimiter = "\t";
        if (! str_contains($first, "\t")) {
            $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        }
        $headers = str_getcsv(trim($first), $delimiter, '"', '');
        $aliases = [
            'date' => ['date', 'дата'], 'url' => ['url', 'URL', 'page_url'], 'query' => ['query', 'запрос', 'search_query'],
            'region' => ['region', 'регион', 'region_id'], 'clicks' => ['clicks', 'клики'],
            'impressions' => ['impressions', 'показы', 'shows'], 'position' => ['position', 'позиция'],
        ];
        $columns = [];
        foreach ($headers as $index => $header) {
            foreach ($aliases as $key => $names) {
                if (in_array(mb_strtolower(trim($header)), array_map('mb_strtolower', $names), true)) {
                    $columns[$key] = $index;
                }
            }
        }
        foreach (['date', 'url', 'query', 'region', 'clicks', 'impressions'] as $required) {
            if (! isset($columns[$required])) {
                fclose($stream);
                throw new RuntimeException('Неизвестный формат выгрузки: отсутствует столбец '.$required.'. Требуется пример файла.');
            }
        }
        try {
            DB::transaction(function () use ($taskId, $stream, $delimiter, $columns) {
                $task = DB::table('yandex_seo_tasks')->where('id', $taskId)->lockForUpdate()->first();
                if (! $task) {
                    throw new RuntimeException('Задача импорта не найдена.');
                }
                $ids = json_decode($task->page_ids, true);
                $dates = json_decode($task->dates, true);
                $pages = DB::table('yandex_seo_pages')->whereIn('id', $ids)->get()->keyBy('path');
                DB::table('yandex_seo_metrics')->whereIn('page_id', $ids)->whereIn('date', $dates)->delete();
                while (($row = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
                    if ($row === [null]) {
                        continue;
                    }
                    $get = fn ($key) => isset($columns[$key]) ? ($row[$columns[$key]] ?? '') : '';
                    $path = $this->pathForUrl($get('url'));
                    if ($path === null && str_starts_with($get('url'), '/') && ! str_starts_with($get('url'), '//')) {
                        $path = $get('url');
                    }
                    $page = $pages->get($path);
                    $date = $get('date');
                    if (! $page || ! in_array($date, $dates, true) || ! ctype_digit($get('clicks')) || ! ctype_digit($get('impressions')) || mb_strlen($get('region')) > 100) {
                        throw new RuntimeException('Строка выгрузки не соответствует запрошенным URL, датам или числовым метрикам. Импорт отменён.');
                    }
                    $query = $get('query');
                    $key = ['page_id' => $page->id, 'date' => $date, 'query_hash' => hash('sha256', $query), 'region' => $get('region')];
                    if (DB::table('yandex_seo_metrics')->where($key)->exists()) {
                        throw new RuntimeException('Повторная строка URL/дата/запрос/регион: импорт отменён во избежание искажения метрик.');
                    }
                    $position = str_replace(',', '.', $get('position'));
                    if ($position !== '' && ! is_numeric($position)) {
                        throw new RuntimeException('Некорректная позиция в выгрузке.');
                    }
                    DB::table('yandex_seo_metrics')->insert($key + ['query' => $query, 'clicks' => (int) $get('clicks'), 'impressions' => (int) $get('impressions'), 'position' => $position === '' ? null : (float) $position]);
                }
                DB::table('yandex_seo_coverage')->where('task_id', $taskId)->update(['status' => 'loaded', 'loaded_at' => now()]);
                DB::table('yandex_seo_tasks')->where('id', $taskId)->update(['status' => 'loaded', 'error' => null, 'updated_at' => now()]);
            });
        } finally {
            fclose($stream);
        }
    }
}
