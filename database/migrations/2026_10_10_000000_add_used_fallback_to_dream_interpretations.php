<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->boolean('used_fallback')->nullable()->after('analysis_issue')
                ->comment('true — ответ получен через резервный LLM-провайдер');
        });
    }

    public function down(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->dropColumn('used_fallback');
        });
    }
};
