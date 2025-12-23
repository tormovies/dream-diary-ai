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
        Schema::create('dreams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('dream_type', [
                'Яркий сон',
                'Бледный сон',
                'Пограничное состояние',
                'Паралич',
                'ВТО',
                'Осознанное сновидение',
                'Глюк',
                'Транс / Гипноз'
            ]);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('report_id');
            $table->index('dream_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dreams');
    }
};
