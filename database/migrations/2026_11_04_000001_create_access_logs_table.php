<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->nullable()->index();
            $table->timestamp('accessed_at')->index();
            $table->string('method', 20);
            $table->text('path');
            $table->unsignedSmallInteger('status')->index();
            $table->unsignedInteger('duration_ms');
            $table->text('user_agent')->nullable();
            $table->string('account_type', 30)->nullable();
            $table->string('account_id', 64)->nullable();
            $table->string('account_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
