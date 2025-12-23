<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Добавляем настройку минимальной длины текста для показа спойлера в дневнике
        Setting::setValue('diary_spoiler_min_length', 1000);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем настройку
        Setting::where('key', 'diary_spoiler_min_length')->delete();
    }
};
