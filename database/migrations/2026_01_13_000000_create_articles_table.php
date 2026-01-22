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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content'); // HTML контент из WYSIWYG редактора
            $table->enum('type', ['guide', 'article']); // Тип: инструкция или статья
            $table->enum('status', ['draft', 'published'])->default('draft'); // Статус публикации
            $table->integer('order')->default(0); // Порядок сортировки (для drag-and-drop)
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade'); // Автор (админ)
            $table->string('image')->nullable(); // Заголовочное изображение для OG (опционально)
            $table->timestamp('published_at')->nullable(); // Дата публикации
            $table->timestamps();
            
            // Индексы для оптимизации
            $table->index('type');
            $table->index('status');
            $table->index('order');
            $table->index(['type', 'status', 'order']); // Составной индекс для фильтрации и сортировки
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
