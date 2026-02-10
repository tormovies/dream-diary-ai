<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dream_interpretation_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dream_interpretation_id')->unique();
            $table->dateTime('interpretation_created_at')->comment('Дата создания толкования для группировки по дням');
            $table->string('processing_status', 20)->default('pending');
            $table->json('traditions')->nullable();
            $table->timestamps();
        });

        Schema::table('dream_interpretation_stats', function (Blueprint $table) {
            $table->foreign('dream_interpretation_id')
                ->references('id')->on('dream_interpretations')->onDelete('cascade');
            $table->index(['interpretation_created_at', 'processing_status'], 'di_stats_created_status');
            $table->index('processing_status', 'di_stats_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dream_interpretation_stats');
    }
};
