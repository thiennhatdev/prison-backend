<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ToPhamNhanEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visitation_schedules', function (Blueprint $table) {
            $table->enum(
                'toPhamNhan',
                array_column(ToPhamNhanEnum::cases(), 'value')
            )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitation_schedules', function (Blueprint $table) {
            $table->dropColumn('toPhamNhan');
        });
    }
};
