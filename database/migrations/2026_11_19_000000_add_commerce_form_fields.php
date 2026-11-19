<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->string('image_url', 2048)->nullable();
        });

        foreach (['orders', 'deposit_requests'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->json('sender_details')->nullable();
                $table->json('recipient_details')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['orders', 'deposit_requests'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['sender_details', 'recipient_details']));
        }

        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['category', 'image_url']));
    }
};
