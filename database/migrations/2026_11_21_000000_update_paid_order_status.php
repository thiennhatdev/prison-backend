<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->where('status', 'PAID')->update(['status' => 'PAYMENT_RECEIVED']);
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'PAYMENT_RECEIVED')->update(['status' => 'PAID']);
    }
};
