<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dream_interpretation_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dream_interpretation_id')
                ->constrained('dream_interpretations')
                ->onDelete('cascade');
            $table->string('type', 20)->comment('symbol|location|tag');
            $table->string('slug', 255)->comment('URL-ключ сущности');
            $table->string('name', 500)->comment('Отображаемое название');
            $table->text('meaning')->nullable()->comment('Значение символа (только для type=symbol)');
            $table->string('source', 20)->comment('result|series_dream');
            $table->unsignedBigInteger('source_id')->comment('id result или series_dream');
            $table->dateTime('interpretation_created_at')->comment('Дата толкования для статистики по дням');
            $table->timestamps();
        });

        Schema::table('dream_interpretation_entities', function (Blueprint $table) {
            $table->index(['type', 'slug'], 'di_ent_type_slug');
            $table->index(['dream_interpretation_id', 'type'], 'di_ent_interp');
            $table->index(['type', 'interpretation_created_at'], 'di_ent_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dream_interpretation_entities');
    }
};
