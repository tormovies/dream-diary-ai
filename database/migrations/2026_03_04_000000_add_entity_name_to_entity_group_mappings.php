<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_group_mappings', function (Blueprint $table) {
            $table->string('entity_name', 500)->nullable()->after('entity_slug')->comment('Отображаемое название сущности');
        });

        $names = DB::table('dream_interpretation_entities')
            ->selectRaw('slug, MAX(name) as name')
            ->groupBy('slug')
            ->pluck('name', 'slug');
        foreach ($names as $slug => $name) {
            DB::table('entity_group_mappings')->where('entity_slug', $slug)->whereNull('entity_name')->update(['entity_name' => $name]);
        }
    }

    public function down(): void
    {
        Schema::table('entity_group_mappings', function (Blueprint $table) {
            $table->dropColumn('entity_name');
        });
    }
};
