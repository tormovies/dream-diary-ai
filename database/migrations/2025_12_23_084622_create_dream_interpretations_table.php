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
        Schema::create('dream_interpretations', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 32)->unique()->comment('Уникальный хеш для приватного доступа');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('Пользователь (если зарегистрирован)');
            $table->string('ip_address', 45)->nullable()->comment('IP адрес для незарегистрированных');
            $table->text('dream_description')->comment('Описание сна');
            $table->text('context')->nullable()->comment('Контекст (ситуация, переживания)');
            $table->json('traditions')->nullable()->comment('Выбранные традиции анализа');
            $table->json('analysis_data')->nullable()->comment('Структурированные данные анализа');
            $table->text('raw_api_response')->nullable()->comment('Сырой ответ от API для отладки');
            $table->string('api_error')->nullable()->comment('Ошибка API, если была');
            $table->timestamps();
            
            $table->index('hash');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dream_interpretations');
    }
};
