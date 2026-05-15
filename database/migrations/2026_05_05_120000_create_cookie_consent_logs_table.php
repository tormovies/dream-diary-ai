<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_consent_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_id')->index();
            $table->string('policy_version', 32);
            $table->boolean('necessary')->default(true);
            $table->boolean('analytics')->default(false);
            $table->timestamp('consent_at')->useCurrent();
            $table->string('ip_hash', 64);
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consent_logs');
    }
};
