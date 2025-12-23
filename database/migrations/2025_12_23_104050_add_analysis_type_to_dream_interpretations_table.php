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
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->string('analysis_type', 20)->nullable()->default('single')->comment('Тип анализа: single/integrated/comparative');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            //
        });
    }
};
