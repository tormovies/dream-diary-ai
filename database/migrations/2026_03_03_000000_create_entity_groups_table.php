<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_groups', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 255)->unique()->comment('URL-ключ группы');
            $table->string('name', 500)->comment('Название группы');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_groups');
    }
};
