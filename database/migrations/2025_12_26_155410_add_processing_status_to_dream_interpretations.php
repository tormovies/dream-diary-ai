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
            $table->string('processing_status', 20)->default('pending')->after('analysis_type')
                ->comment('Статус обработки: pending, processing, completed, failed');
            $table->timestamp('processing_started_at')->nullable()->after('processing_status')
                ->comment('Время начала обработки');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->dropColumn(['processing_status', 'processing_started_at']);
        });
    }
};
