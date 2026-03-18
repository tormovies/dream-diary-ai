<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Только для толкований с формы: участвовать ли в перелинковке (по умолчанию да).
     */
    public function up(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->boolean('allow_public_linking')->default(true)->after('api_error')
                ->comment('Разрешить показ в перелинковке (толкования с формы); для анализов отчётов не используется');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dream_interpretations', function (Blueprint $table) {
            $table->dropColumn('allow_public_linking');
        });
    }
};
