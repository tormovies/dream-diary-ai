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
        Schema::create('dream_interpretation_series_dreams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dream_interpretation_result_id')
                  ->constrained('dream_interpretation_results')
                  ->onDelete('cascade')
                  ->comment('Связь с результатом анализа серии');
            
            $table->integer('dream_number')->comment('Номер сна в серии (1, 2, 3...)');
            
            // Те же поля что и для одиночного сна
            $table->string('dream_title')->nullable();
            $table->text('dream_detailed')->nullable();
            $table->string('dream_type')->nullable();
            $table->json('key_symbols')->nullable();
            $table->json('unified_locations')->nullable();
            $table->json('key_tags')->nullable();
            $table->text('summary_insight')->nullable();
            $table->string('emotional_tone')->nullable();
            
            $table->integer('order')->default(0)->comment('Порядок сортировки');
            $table->timestamps();
            
            // Индексы
            $table->index('dream_interpretation_result_id');
            $table->index('dream_number');
            $table->index(['dream_interpretation_result_id', 'dream_number']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dream_interpretation_series_dreams');
    }
};
