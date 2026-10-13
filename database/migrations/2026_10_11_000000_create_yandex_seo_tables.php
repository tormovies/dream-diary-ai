<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('host_id');
            $table->string('url_hash', 64);
            $table->text('url');
            $table->text('path');
            $table->text('search_title')->nullable();
            $table->timestamp('in_search_seen_at')->nullable();
            $table->timestamp('search_version_at')->nullable();
            $table->timestamps();
            $table->unique(['host_id', 'url_hash']);
        });
        Schema::create('yandex_seo_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('host_id');
            $table->string('remote_id')->nullable();
            $table->string('status')->default('queued')->index();
            $table->json('page_ids');
            $table->json('dates');
            $table->boolean('use_pro_tariff')->default(false);
            $table->unsignedInteger('quota_used')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
        Schema::create('yandex_seo_coverage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('yandex_seo_pages')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('queued');
            $table->foreignId('task_id')->nullable()->constrained('yandex_seo_tasks')->nullOnDelete();
            $table->timestamp('loaded_at')->nullable();
            $table->unique(['page_id', 'date']);
        });
        Schema::create('yandex_seo_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('yandex_seo_pages')->cascadeOnDelete();
            $table->date('date')->index();
            $table->string('query_hash', 64);
            $table->text('query');
            $table->string('region', 100)->default('');
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->decimal('position', 12, 4)->nullable();
            $table->unique(['page_id', 'date', 'query_hash', 'region'], 'yandex_seo_metrics_unique');
        });
        Schema::create('seo_url_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('path_hash', 64)->unique();
            $table->text('path');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('h1')->nullable();
            $table->timestamps();
        });
        Schema::create('seo_url_changes', function (Blueprint $table) {
            $table->id();
            $table->string('path_hash', 64)->index();
            $table->text('path');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('before');
            $table->json('after');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        foreach (['seo_url_changes', 'seo_url_overrides', 'yandex_seo_metrics', 'yandex_seo_coverage', 'yandex_seo_tasks', 'yandex_seo_pages'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
