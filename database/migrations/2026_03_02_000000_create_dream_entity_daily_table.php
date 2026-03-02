<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dream_entity_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('type', 20);
            $table->string('slug', 255);
            $table->string('name', 500);
            $table->unsignedInteger('mentions')->default(0);
            $table->timestamps();
        });

        Schema::table('dream_entity_daily', function (Blueprint $table) {
            $table->unique(['date', 'type', 'slug'], 'ded_date_type_slug');
            $table->index(['type', 'date'], 'ded_type_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dream_entity_daily');
    }
};
