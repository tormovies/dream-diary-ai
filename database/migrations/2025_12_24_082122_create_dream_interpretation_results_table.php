<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dream_interpretation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dream_interpretation_id')
                  ->constrained('dream_interpretations')
                  ->onDelete('cascade')
                  ->unique()
                  ->comment('Связь с основной таблицей интерпретаций');
            
            // Тип и версия
            $table->string('type', 20)->comment('single|series');
            $table->string('format_version', 10)->default('1.0')->comment('Версия формата данных');
            
            // ОБЩИЕ поля (для обоих типов)
            $table->json('traditions')->nullable()->comment('Массив традиций анализа');
            $table->string('analysis_type', 50)->nullable()->comment('single|integrated|comparative|series_integrated');
            $table->json('recommendations')->nullable()->comment('Массив рекомендаций');
            
            // ПОЛЯ ДЛЯ ОДИНОЧНОГО СНА (заполняются если type=\'single\')
            $table->string('dream_title')->nullable()->comment('Название сна');
            $table->text('dream_detailed')->nullable()->comment('Детальный анализ сна');
            $table->string('dream_type')->nullable()->comment('Тип сна (архетипический/бытовой/...)');
            $table->json('key_symbols')->nullable()->comment('Ключевые символы [{symbol, meaning}]');
            $table->json('unified_locations')->nullable()->comment('Локации ["Дом", "Метро", ...]');
            $table->json('key_tags')->nullable()->comment('Теги ["интеграция", "сила", ...]');
            $table->text('summary_insight')->nullable()->comment('Ключевая мысль');
            $table->string('emotional_tone')->nullable()->comment('Эмоциональный тон');
            
            // ПОЛЯ ДЛЯ СЕРИИ СНОВ (заполняются если type=\'series\')
            $table->string('series_title')->nullable()->comment('Название серии');
            $table->text('overall_theme')->nullable()->comment('Общая тема серии');
            $table->text('emotional_arc')->nullable()->comment('Эмоциональная дуга');
            $table->json('key_connections')->nullable()->comment('Ключевые связи ["связь1", "связь2"]');
            
            $table->timestamps();
            
            // Индексы
            $table->index('type');
            $table->index('dream_interpretation_id');
            $table->index('format_version');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dream_interpretation_results');
    }
};
