<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\YandexSeoImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class YandexSeoImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_all_yandex_tabs(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']));
        foreach (['pages', 'queries', 'overview', 'loading'] as $tab) {
            $this->get(route('admin.seo.yandex.index', ['tab' => $tab]))->assertOk()->assertSee('SEO / Яндекс');
        }
    }

    public function test_non_admin_cannot_access_yandex_settings(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'user', 'nickname' => 'seo-user']));
        $this->get(route('admin.seo.yandex.index'))->assertForbidden();
    }

    public function test_connection_encrypts_token_and_discovers_hosts(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']));
        \Illuminate\Support\Facades\Http::fake([
            'api.webmaster.yandex.net/v4/user' => \Illuminate\Support\Facades\Http::response(['user_id' => 123]),
            'api.webmaster.yandex.net/v4/user/123/hosts' => \Illuminate\Support\Facades\Http::response(['hosts' => [
                ['host_id' => 'example-host', 'ascii_host_url' => 'https://example.test', 'verified' => true],
            ]]),
        ]);
        $this->post(route('admin.seo.yandex.connect'), ['token' => 'test-oauth-secret'])->assertRedirect()->assertSessionHasNoErrors();
        $stored = Setting::getValue('yandex_seo_token');
        $this->assertNotSame('test-oauth-secret', $stored);
        $this->assertSame('test-oauth-secret', \Illuminate\Support\Facades\Crypt::decryptString($stored));
        $this->assertSame('example-host', Setting::getValue('yandex_seo_hosts')[0]['host_id']);
        $this->get(route('admin.seo.yandex.index', ['tab' => 'loading']))->assertOk()->assertDontSee('test-oauth-secret');
    }

    public function test_settings_reject_unverified_site(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']));
        Setting::setValue('yandex_seo_hosts', [['host_id' => 'unverified', 'verified' => false]]);
        $this->post(route('admin.seo.yandex.settings'), ['host_id' => 'unverified', 'enabled' => 1])->assertSessionHasErrors('host_id');
        $this->assertNull(Setting::getValue('yandex_seo_host_id'));
    }

    public function test_manual_seo_save_requires_confirmation_and_records_history(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']));
        $this->task();
        config(['app.url' => 'https://example.test', 'seo.base_url' => 'https://example.test']);
        $page = DB::table('yandex_seo_pages')->value('id');
        $endpoint = route('admin.seo.yandex.save', $page);
        $fields = ['title' => 'Manual title', 'description' => 'Manual description', 'h1' => 'Manual heading'];
        $this->patchJson($endpoint, $fields)->assertUnprocessable();
        $this->assertDatabaseCount('seo_url_changes', 0);
        $this->patchJson($endpoint, $fields + ['confirmed' => true])->assertOk()->assertJsonPath('fields.title', 'Manual title');
        $this->assertDatabaseCount('seo_url_changes', 1);
        $this->patchJson($endpoint, $fields + ['confirmed' => true])->assertOk();
        $this->assertDatabaseCount('seo_url_changes', 1);
        $this->getJson(route('admin.seo.yandex.detail', $page))->assertOk()->assertJsonPath('history.0.after.h1', 'Manual heading');
    }

    public function test_seo_override_is_applied_to_canonical_page_not_other_rendered_links(): void
    {
        $this->task();
        config(['seo.base_url' => 'https://example.test']);
        DB::table('seo_url_overrides')->insert([
            'path_hash' => hash('sha256', '/'), 'path' => '/', 'title' => 'Home override',
            'description' => 'Exact description', 'h1' => 'Home heading', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $seo = \App\Helpers\SeoHelper::get('home');
        $this->assertSame('Home override', $seo['title']);
        $this->assertSame('Exact description', $seo['description']);
        $this->assertSame('Home heading', $seo['h1']);
        $this->assertSame('Home override', $seo['og_title']);
        $this->assertNotSame('Home override', \App\Helpers\SeoHelper::get('search')['title']);
    }

    public function test_page_editor_rejects_other_hosts_and_non_admins(): void
    {
        $this->task();
        $page = DB::table('yandex_seo_pages')->value('id');
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']));
        config(['app.url' => 'https://other.test']);
        $this->getJson(route('admin.seo.yandex.detail', $page))->assertUnprocessable();
        config(['app.url' => 'https://example.test']);
        Setting::setValue('yandex_seo_host_id', 'another-host');
        $this->getJson(route('admin.seo.yandex.detail', $page))->assertNotFound();
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'user', 'nickname' => 'seo-user']));
        $this->patchJson(route('admin.seo.yandex.save', $page), [])->assertForbidden();
    }

    public function test_page_panel_exposes_current_static_seo(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']));
        $this->task();
        config(['app.url' => 'https://example.test', 'seo.base_url' => 'https://example.test']);
        $page = DB::table('yandex_seo_pages')->value('id');
        DB::table('yandex_seo_pages')->where('id', $page)->update(['path' => '/tolkovanie-snov']);
        $seo = \App\Helpers\SeoHelper::forDreamAnalyzer();
        $this->getJson(route('admin.seo.yandex.detail', $page))->assertOk()->assertJsonPath('current.title', $seo['title']);
        $this->get(route('admin.seo.yandex.index'))->assertOk()->assertSee('ИИ-рекомендации');
    }

    public function test_page_panel_exposes_current_published_article_seo(): void
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']);
        $this->actingAs($user);
        $this->task();
        config(['app.url' => 'https://example.test', 'seo.base_url' => 'https://example.test']);
        $article = \App\Models\Article::create(['title' => 'Dream article', 'slug' => 'dream-article', 'type' => 'article', 'status' => 'published', 'content' => 'Article text', 'author_id' => $user->id]);
        $page = DB::table('yandex_seo_pages')->value('id');
        DB::table('yandex_seo_pages')->where('id', $page)->update(['path' => '/articles/dream-article']);
        $seo = \App\Helpers\SeoHelper::get('article', $article->id, ['title' => $article->title, 'slug' => $article->slug, 'article' => $article]);
        $this->getJson(route('admin.seo.yandex.detail', $page))->assertOk()->assertJsonPath('current.title', $seo['title']);
    }

    public function test_ai_suggestions_do_not_apply_seo_automatically(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin', 'nickname' => 'seo-admin']));
        $this->task();
        config(['app.url' => 'https://example.test']);
        Setting::setValue('deepseek_api_key', 'test-key');
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response($this->aiResponse())]);
        $page = DB::table('yandex_seo_pages')->value('id');
        $this->postJson(route('admin.seo.yandex.suggestions', $page))->assertOk()
            ->assertJsonPath('suggestions.0.title', 'SEO title')->assertJsonPath('used_fallback', false);
        $this->assertDatabaseCount('seo_url_overrides', 0);
        $this->assertDatabaseCount('seo_url_changes', 0);
    }

    private function aiResponse(): array
    {
        return ['choices' => [['message' => ['content' => json_encode(['suggestions' => [[
            'title' => 'SEO title', 'description' => 'SEO description', 'h1' => 'SEO heading', 'reason' => 'Based on queries',
        ]]])], 'finish_reason' => 'stop']]];
    }

    public function test_seo_ai_uses_fallback_only_for_connection_failure(): void
    {
        Setting::setValue('deepseek_api_key', 'test-key');
        Setting::setValue('fallback_api_key', 'fallback-key');
        Setting::setValue('fallback_model', 'fallback-model');
        \Illuminate\Support\Facades\Http::fake(function ($request) {
            if (str_contains($request->url(), 'api.deepseek.com')) {
                throw new \Illuminate\Http\Client\ConnectionException('test timeout');
            }

            return \Illuminate\Support\Facades\Http::response($this->aiResponse());
        });
        $result = app(\App\Services\DeepSeekService::class)->suggestSeo(['path' => '/page']);
        $this->assertTrue($result['used_fallback']);
        \Illuminate\Support\Facades\Http::assertSent(fn ($request) => ($request['model'] ?? '') === 'fallback-model');
    }

    public function test_seo_ai_rejects_http_errors_without_fallback(): void
    {
        Setting::setValue('deepseek_api_key', 'test-key');
        Setting::setValue('fallback_api_key', 'fallback-key');
        Setting::setValue('fallback_model', 'fallback-model');
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response(['error' => ['message' => 'invalid key']], 401)]);
        try {
            app(\App\Services\DeepSeekService::class)->suggestSeo(['path' => '/page']);
            $this->fail('HTTP failure accepted');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ИИ не вернул', $e->getMessage());
        }
        \Illuminate\Support\Facades\Http::assertSentCount(1);
    }

    public function test_seo_ai_rejects_invalid_suggestion_fields(): void
    {
        Setting::setValue('deepseek_api_key', 'test-key');
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response(['choices' => [['message' => ['content' => '{"suggestions":[{"title":123}]}']]]])]);
        $this->expectException(\RuntimeException::class);
        app(\App\Services\DeepSeekService::class)->suggestSeo(['path' => '/page']);
    }

    private function task(string $status = 'submitted'): int
    {
        DB::table('yandex_seo_pages')->where('url_hash', hash('sha256', 'https://example.test/page'))->delete();
        Setting::setValue('yandex_seo_host_id', 'example-host');
        Setting::setValue('yandex_seo_host_url', 'https://example.test');
        $page = DB::table('yandex_seo_pages')->insertGetId([
            'host_id' => 'example-host', 'url_hash' => hash('sha256', 'https://example.test/page'),
            'url' => 'https://example.test/page', 'path' => '/page', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $task = DB::table('yandex_seo_tasks')->insertGetId([
            'host_id' => 'example-host', 'remote_id' => 'remote-task', 'status' => $status,
            'page_ids' => json_encode([$page]), 'dates' => json_encode(['2026-10-10']),
            'quota_used' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('yandex_seo_coverage')->insert(['page_id' => $page, 'date' => '2026-10-10', 'task_id' => $task, 'status' => 'queued']);

        return $task;
    }

    public function test_import_marks_only_validated_data_as_loaded(): void
    {
        $task = $this->task();
        app(YandexSeoImportService::class)->importBody($task, "date\turl\tquery\tregion\tclicks\timpressions\tposition\n2026-10-10\thttps://example.test/page\tdream\t213\t2\t20\t3.5\n");
        $this->assertDatabaseHas('yandex_seo_tasks', ['id' => $task, 'status' => 'loaded']);
        $this->assertDatabaseHas('yandex_seo_metrics', ['query' => 'dream', 'clicks' => 2, 'impressions' => 20]);
        $this->assertDatabaseHas('yandex_seo_coverage', ['task_id' => $task, 'status' => 'loaded']);
    }

    public function test_invalid_row_rolls_back_the_entire_import(): void
    {
        $task = $this->task();
        try {
            app(YandexSeoImportService::class)->importBody($task, "date,url,query,region,clicks,impressions\n2026-10-10,/page,dream,213,2,20\n2026-10-10,/other,dream,213,2,20\n");
            $this->fail('Invalid export was accepted.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Импорт отменён', $e->getMessage());
        }
        $this->assertDatabaseCount('yandex_seo_metrics', 0);
        $this->assertDatabaseHas('yandex_seo_coverage', ['task_id' => $task, 'status' => 'queued']);
    }

    public function test_format_error_retries_existing_export_without_spending_quota(): void
    {
        $task = $this->task('format_error');
        app(YandexSeoImportService::class)->recoverTask($task);
        $this->assertDatabaseHas('yandex_seo_tasks', ['id' => $task, 'status' => 'submitted', 'quota_used' => 1]);
        $this->assertDatabaseCount('yandex_seo_coverage', 1);
    }

    public function test_unknown_task_requires_explicit_permission_for_new_export(): void
    {
        $task = $this->task('unknown');
        $this->expectException(\RuntimeException::class);
        app(YandexSeoImportService::class)->recoverTask($task);
    }

    public function test_explicit_recovery_releases_coverage_but_preserves_quota_accounting(): void
    {
        $task = $this->task('unknown');
        app(YandexSeoImportService::class)->recoverTask($task, true);
        $this->assertDatabaseHas('yandex_seo_tasks', ['id' => $task, 'status' => 'abandoned', 'quota_used' => 1]);
        $this->assertDatabaseCount('yandex_seo_coverage', 0);
    }

    public function test_path_resolution_accepts_only_configured_host(): void
    {
        $service = app(YandexSeoImportService::class);
        Setting::setValue('yandex_seo_host_url', 'https://example.test');
        $this->assertSame('/page?x=1', $service->pathForUrl('https://example.test/page?x=1'));
        $this->assertNull($service->pathForUrl('https://other.test/page'));
        $this->assertNull($service->pathForUrl('ftp://example.test/page'));
    }

    public function test_scheduler_tick_requires_enabled_setting_and_reports_errors(): void
    {
        Setting::setValue('yandex_seo_enabled', false);
        $api = $this->createMock(\App\Services\YandexWebmasterService::class);
        $api->expects($this->never())->method('request');
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        Setting::setValue('yandex_seo_enabled', true);
        $api = $this->createMock(\App\Services\YandexWebmasterService::class);
        $api->method('request')->willThrowException(new \RuntimeException('API недоступен'));
        try {
            app(YandexSeoImportService::class, ['api' => $api])->tick();
            $this->fail('Tick swallowed API failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('API недоступен', Setting::getValue('yandex_seo_last_error'));
        }
        Setting::setValue('yandex_seo_enabled', false);
    }

    public function test_import_rejects_gzip_and_oversized_bodies(): void
    {
        $task = $this->task();
        $csv = "date\turl\tquery\tregion\tclicks\timpressions\n2026-10-10\thttps://example.test/page\tdream\t213\t2\t20\n";
        app(YandexSeoImportService::class)->importBody($task, gzencode($csv));
        $this->assertDatabaseHas('yandex_seo_tasks', ['id' => $task, 'status' => 'loaded']);
        $task = $this->task();
        $limit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');
        $body = str_repeat('x', 50 * 1024 * 1024 + 1);
        try {
            app(YandexSeoImportService::class)->importBody($task, $body);
            $this->fail('Oversized body accepted.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('50 МБ', $e->getMessage());
        }
        unset($body, $e);
        gc_collect_cycles();
        gc_mem_caches();
        ini_set('memory_limit', $limit);
    }

    public function test_import_rejects_unknown_headers_and_duplicate_rows(): void
    {
        $task = $this->task();
        try {
            app(YandexSeoImportService::class)->importBody($task, "day\tlink\tphrase\tarea\tclicks\tshows\n2026-10-10\thttps://example.test/page\tdream\t213\t2\t20\n");
            $this->fail('Unknown headers accepted.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('отсутствует столбец', $e->getMessage());
        }
        $task = $this->task();
        $body = "date\turl\tquery\tregion\tclicks\timpressions\n2026-10-10\thttps://example.test/page\tdream\t213\t2\t20\n2026-10-10\thttps://example.test/page\tdream\t213\t2\t20\n";
        try {
            app(YandexSeoImportService::class)->importBody($task, $body);
            $this->fail('Duplicate row accepted.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Повторная строка', $e->getMessage());
        }
        $this->assertDatabaseCount('yandex_seo_metrics', 0);
    }

    private function tickService(): \PHPUnit\Framework\MockObject\MockObject&\App\Services\YandexWebmasterService
    {
        return $this->createMock(\App\Services\YandexWebmasterService::class);
    }

    public function test_tick_syncs_index_snapshot_and_skips_fresh_completed_state(): void
    {
        Setting::setValue('yandex_seo_enabled', true);
        Setting::setValue('yandex_seo_host_id', 'example-host');
        Setting::setValue('yandex_seo_host_url', 'https://example.test');
        Setting::setValue('yandex_seo_index_state', ['host' => 'old-host', 'completed_at' => now()->toDateTimeString(), 'offset' => 999]);
        $samplesCalls = 0;
        $api = $this->tickService();
        $api->method('request')->willReturnCallback(function ($method, $endpoint, array $params = []) use (&$samplesCalls) {
            if ($method === 'GET' && array_key_exists('offset', $params)) {
                $samplesCalls++;

                return ['samples' => [['url' => 'https://example.test/other', 'title' => 'Yandex title', 'last_access' => '2026-10-10 12:00:00']], 'count' => 1];
            }

            return ['dates' => []];
        });
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        $state = Setting::getValue('yandex_seo_index_state');
        $this->assertSame('example-host', $state['host']);
        $this->assertSame(0, $state['offset']);
        $this->assertNotNull($state['completed_at']);
        $this->assertDatabaseHas('yandex_seo_pages', ['path' => '/other', 'search_title' => 'Yandex title']);
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        $this->assertSame(1, $samplesCalls);
        Setting::setValue('yandex_seo_enabled', false);
    }

    public function test_tick_enqueues_first_free_task_and_stores_quota_report(): void
    {
        Setting::setValue('yandex_seo_enabled', true);
        Setting::setValue('yandex_seo_host_id', 'example-host');
        Setting::setValue('yandex_seo_host_url', 'https://example.test');
        $page = DB::table('yandex_seo_pages')->insertGetId([
            'host_id' => 'example-host', 'url_hash' => hash('sha256', 'https://example.test/page'),
            'url' => 'https://example.test/page', 'path' => '/page', 'in_search_seen_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $api = $this->tickService();
        $gets = 0;
        $api->method('request')->willReturnCallback(function ($method, $endpoint, array $params = []) use (&$gets) {
            if ($method === 'GET' && array_key_exists('offset', $params)) {
                return ['samples' => [], 'count' => 0];
            }
            if ($method === 'GET') {
                return ++$gets === 1 ? ['dates' => ['2026-09-01', '2026-10-01', '2026-10-10']] : ['limits' => []];
            }

            return ['task_id' => 'remote-1', 'free_quota_remaining' => 98];
        });
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        $task = DB::table('yandex_seo_tasks')->where('remote_id', 'remote-1')->first();
        $this->assertNotNull($task);
        $this->assertSame('submitted', $task->status);
        $this->assertSame(2, $task->quota_used);
        $this->assertSame([$page], json_decode($task->page_ids));
        $this->assertSame(['2026-10-01', '2026-10-10'], json_decode($task->dates, true));
        $this->assertSame(2, DB::table('yandex_seo_coverage')->where('task_id', $task->id)->count());
        $this->assertSame(98, Setting::getValue('yandex_seo_free_remaining'));
        $this->assertSame(now('Europe/Moscow')->toDateString(), Setting::getValue('yandex_seo_quota_day'));
        $this->assertSame('example-host', Setting::getValue('yandex_seo_quota_host'));
        Setting::setValue('yandex_seo_enabled', false);
    }

    public function test_tick_skips_enqueue_when_reported_free_quota_is_exhausted(): void
    {
        Setting::setValue('yandex_seo_enabled', true);
        Setting::setValue('yandex_seo_host_id', 'example-host');
        Setting::setValue('yandex_seo_host_url', 'https://example.test');
        Setting::setValue('yandex_seo_quota_day', now('Europe/Moscow')->toDateString());
        Setting::setValue('yandex_seo_quota_host', 'example-host');
        Setting::setValue('yandex_seo_free_remaining', 0);
        DB::table('yandex_seo_pages')->insert([
            'host_id' => 'example-host', 'url_hash' => hash('sha256', 'https://example.test/page'),
            'url' => 'https://example.test/page', 'path' => '/page', 'in_search_seen_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $api = $this->tickService();
        $api->method('request')->willReturnCallback(fn ($method, $endpoint, array $params = []) => match (true) {
            $method === 'GET' && array_key_exists('offset', $params) => ['samples' => [], 'count' => 0],
            default => ['dates' => ['2026-10-10']],
        });
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        $this->assertDatabaseCount('yandex_seo_tasks', 0);
        Setting::setValue('yandex_seo_enabled', false);
    }

    public function test_tick_prunes_metrics_and_coverage_older_than_window(): void
    {
        Setting::setValue('yandex_seo_enabled', true);
        Setting::setValue('yandex_seo_host_id', 'example-host');
        Setting::setValue('yandex_seo_host_url', 'https://example.test');
        $page = DB::table('yandex_seo_pages')->insertGetId([
            'host_id' => 'example-host', 'url_hash' => hash('sha256', 'https://example.test/page'),
            'url' => 'https://example.test/page', 'path' => '/page', 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (['2026-01-01', '2026-10-10'] as $date) {
            DB::table('yandex_seo_metrics')->insert([
                'page_id' => $page, 'date' => $date, 'query_hash' => hash('sha256', 'dream'),
                'query' => 'dream', 'region' => '', 'clicks' => 1, 'impressions' => 5,
            ]);
            DB::table('yandex_seo_coverage')->insert(['page_id' => $page, 'date' => $date, 'status' => 'loaded', 'loaded_at' => now()]);
        }
        $api = $this->tickService();
        $api->method('request')->willReturnCallback(fn ($method, $endpoint, array $params = []) => match (true) {
            $method === 'GET' && array_key_exists('offset', $params) => ['samples' => [], 'count' => 0],
            default => ['dates' => []],
        });
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        $this->assertDatabaseMissing('yandex_seo_metrics', ['date' => '2026-01-01']);
        $this->assertDatabaseHas('yandex_seo_metrics', ['date' => '2026-10-10']);
        $this->assertDatabaseMissing('yandex_seo_coverage', ['date' => '2026-01-01']);
        $this->assertDatabaseHas('yandex_seo_coverage', ['date' => '2026-10-10']);
        $this->assertSame(now('Europe/Moscow')->toDateString(), Setting::getValue('yandex_seo_pruned_day'));
        Setting::setValue('yandex_seo_enabled', false);
    }

    public function test_poll_downloads_success_and_marks_failed_tasks(): void
    {
        $task = $this->task('submitted');
        Setting::setValue('yandex_seo_enabled', true);
        $api = $this->tickService();
        $gets = 0;
        $api->method('request')->willReturnCallback(function ($method, $endpoint, array $params = []) use (&$gets) {
            if ($method === 'GET' && array_key_exists('offset', $params)) {
                return ['samples' => [], 'count' => 0];
            }
            if ($method === 'GET') {
                return ++$gets === 1 ? ['download_status' => 'SUCCESS', 'url' => 'https://storage.mds.yandex.net/export'] : ['dates' => []];
            }

            return ['dates' => []];
        });
        $api->method('download')->willReturn("date\turl\tquery\tregion\tclicks\timpressions\n2026-10-10\thttps://example.test/page\tdream\t213\t2\t20\n");
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        $this->assertDatabaseHas('yandex_seo_tasks', ['id' => $task, 'status' => 'loaded']);
        $this->assertDatabaseHas('yandex_seo_coverage', ['task_id' => $task, 'status' => 'loaded']);
        $this->assertDatabaseHas('yandex_seo_metrics', ['query' => 'dream', 'clicks' => 2]);
        Setting::setValue('yandex_seo_enabled', false);
        $task = $this->task('submitted');
        Setting::setValue('yandex_seo_enabled', true);
        $api = $this->tickService();
        $gets = 0;
        $api->method('request')->willReturnCallback(function ($method, $endpoint, array $params = []) use (&$gets) {
            if ($method === 'GET' && array_key_exists('offset', $params)) {
                return ['samples' => [], 'count' => 0];
            }
            if ($method === 'GET') {
                return ++$gets === 1 ? ['download_status' => 'FAILED', 'error_code' => 'SERP_QUOTA_EXCEEDED'] : ['dates' => []];
            }

            return ['dates' => []];
        });
        app(YandexSeoImportService::class, ['api' => $api])->tick();
        $failed = DB::table('yandex_seo_tasks')->where('id', $task)->first();
        $this->assertSame('failed', $failed->status);
        $this->assertStringContainsString('SERP_QUOTA_EXCEEDED', $failed->error);
        Setting::setValue('yandex_seo_enabled', false);
    }
}
