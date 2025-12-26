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
        // Добавляем analysis_id в таблицу reports
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('analysis_id')->nullable()->after('status')->constrained('dream_interpretations')->onDelete('set null');
            $table->timestamp('analyzed_at')->nullable()->after('analysis_id');
        });

        // Добавляем report_id в таблицу dream_interpretations
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->foreignId('report_id')->nullable()->after('user_id')->constrained('reports')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['analysis_id']);
            $table->dropColumn(['analysis_id', 'analyzed_at']);
        });

        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->dropForeign(['report_id']);
            $table->dropColumn('report_id');
        });
    }
};
