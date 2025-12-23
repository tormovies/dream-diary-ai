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
            $table->text('raw_api_request')->nullable()->comment('JSON запрос, отправленный в API');
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
