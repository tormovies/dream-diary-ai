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
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->string('page_type'); // 'home', 'report', 'profile', 'diary', 'search', 'activity', etc.
            $table->unsignedBigInteger('page_id')->nullable(); // ID конкретной страницы
            $table->string('route_name')->nullable(); // Название роута для точной привязки
            
            // Основные SEO поля
            $table->text('title')->nullable(); // Шаблон title
            $table->text('description')->nullable(); // Шаблон description
            $table->string('h1')->nullable(); // Шаблон H1
            $table->text('h1_description')->nullable(); // Описание под H1
            $table->text('keywords')->nullable(); // Meta keywords
            
            // Open Graph поля
            $table->text('og_title')->nullable(); // OG title (если null - берется из title)
            $table->text('og_description')->nullable(); // OG description
            $table->string('og_image')->nullable(); // URL или путь к изображению
            
            // Управление
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Приоритет (выше = приоритетнее)
            
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['page_type', 'page_id']);
            $table->index('page_type');
            $table->index('route_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
