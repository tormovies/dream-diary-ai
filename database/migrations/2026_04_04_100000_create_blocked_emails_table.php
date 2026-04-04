<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_emails', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique()->comment('Нормализованный email (нижний регистр, trim)');
            $table->boolean('is_permanent')->default(false)->comment('true — после полного удаления аккаунта; снятие только вручную в БД');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_emails');
    }
};
