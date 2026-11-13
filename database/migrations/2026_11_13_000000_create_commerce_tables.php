<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedBigInteger('price');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained();
            $t->foreignId('prisoner_id')->constrained();
            $t->unsignedBigInteger('total');
            $t->string('status')->default('AWAITING_PAYMENT');
            $t->text('note')->nullable();
            $t->timestamps();
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->string('name');
            $t->unsignedBigInteger('price');
            $t->unsignedInteger('quantity');
            $t->timestamps();
        });
        Schema::create('deposit_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained();
            $t->foreignId('prisoner_id')->constrained();
            $t->unsignedBigInteger('amount');
            $t->string('status')->default('AWAITING_PAYMENT');
            $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained();
            $t->foreignId('order_id')->nullable()->unique()->constrained();
            $t->foreignId('deposit_request_id')->nullable()->unique()->constrained();
            $t->string('code')->unique();
            $t->unsignedBigInteger('amount');
            $t->string('bank');
            $t->string('account_number');
            $t->string('account_name');
            $t->string('status')->default('PENDING');
            $t->timestamp('expires_at');
            $t->timestamp('paid_at')->nullable();
            $t->string('idempotency_key', 100);
            $t->string('request_hash', 64);
            $t->unique(['customer_id', 'idempotency_key']);
            $t->timestamps();
        });
        Schema::create('bank_transactions', function (Blueprint $t) {
            $t->id();
            $t->string('sepay_id')->unique();
            $t->foreignId('payment_id')->nullable()->constrained();
            $t->unsignedBigInteger('amount');
            $t->string('status');
            $t->string('reason')->nullable();
            $t->json('payload');
            $t->timestamps();
        });
        Schema::create('prisoner_ledger_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prisoner_id')->constrained();
            $t->foreignId('deposit_request_id')->unique()->constrained();
            $t->foreignId('bank_transaction_id')->unique()->constrained();
            $t->unsignedBigInteger('amount');
            $t->string('type')->default('CREDIT');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['prisoner_ledger_entries', 'bank_transactions', 'payments', 'deposit_requests', 'order_items', 'orders', 'products'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
