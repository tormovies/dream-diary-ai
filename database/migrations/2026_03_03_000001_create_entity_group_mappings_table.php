<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_group_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_group_id')
                ->constrained('entity_groups')
                ->onDelete('cascade');
            $table->string('entity_slug', 255)->comment('slug сущности (без типа — одна запись на все типы)');
            $table->timestamps();
        });

        Schema::table('entity_group_mappings', function (Blueprint $table) {
            $table->unique('entity_slug', 'egm_entity_slug_unique');
            $table->index('entity_group_id', 'egm_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_group_mappings');
    }
};
