<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->string('analysis_issue', 32)->nullable()->after('processing_status')
                ->comment('Качество ответа: parse_failed, truncated_tokens, truncated_json');
            $table->index('analysis_issue');
        });

        Schema::table('dream_interpretation_stats', function (Blueprint $table) {
            $table->string('analysis_issue', 32)->nullable()->after('processing_status');
            $table->index('analysis_issue');
        });
    }

    public function down(): void
    {
        Schema::table('dream_interpretation_stats', function (Blueprint $table) {
            $table->dropIndex(['analysis_issue']);
            $table->dropColumn('analysis_issue');
        });

        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->dropIndex(['analysis_issue']);
            $table->dropColumn('analysis_issue');
        });
    }
};
