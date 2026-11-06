<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 20);
            $table->unsignedBigInteger('content_id');
            $table->timestamp('viewed_at');
            $table->index(['content_type', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};
