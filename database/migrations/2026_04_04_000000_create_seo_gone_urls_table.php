<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_gone_urls', function (Blueprint $table) {
            $table->id();
            // Уникальный индекс MySQL utf8mb4: длина ключа ≤ ~768 символов; путей длиннее не бывает
            $table->string('path', 512)->unique()->comment('Канонический путь, как в redirects.from_path');
            $table->string('source', 32)->comment('manual, admin_purge, admin_delete, user_delete');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_gone_urls');
    }
};
