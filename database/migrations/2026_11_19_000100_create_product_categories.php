<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->restrictOnDelete();
        });

        $categoryIds = [];
        foreach (DB::table('products')->get(['id', 'category']) as $product) {
            $name = trim((string) $product->category) ?: 'Chưa phân loại';
            if (! isset($categoryIds[$name])) {
                $categoryIds[$name] = DB::table('product_categories')->insertGetId([
                    'name' => $name, 'position' => 0, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('products')->where('id', $product->id)->update(['category_id' => $categoryIds[$name]]);
        }

        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('category'));
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->string('category', 100)->nullable());
        foreach (DB::table('product_categories')->get(['id', 'name']) as $category) {
            DB::table('products')->where('category_id', $category->id)->update(['category' => $category->name]);
        }
        Schema::table('products', fn (Blueprint $table) => $table->dropConstrainedForeignId('category_id'));
        Schema::dropIfExists('product_categories');
    }
};
