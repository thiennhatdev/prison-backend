<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Prisoner;
use App\Enums\PtEnum;

return new class extends Migration
{
    public function up()
    {
        Schema::create('visitation_schedules', function (Blueprint $table) {
            // this will create an id, a "published" column, and soft delete and timestamps columns
            createDefaultTableFields($table);
            
            $table->string('prisoner_name', 200)->nullable();
            $table->string('prisoner_sex', 200)->nullable();
            $table->string('prisoner_birthday', 200)->nullable();
            $table->string('prisoner_address', 200)->nullable();
            $table->integer('position')->unsigned()->nullable();
            $table->json('relatives')->nullable();
            $table->foreignIdFor(Prisoner::class)->nullable();
            $table->date('visitDate')->nullable();
            $table->time('visitTime')->nullable();
            $table->time('visitEndTime')->nullable();
            $table->string('qr_token', 200)->nullable();
            $table->string('status', 200)->default('NOT_YET');
            $table->enum(
                'pt',
                array_column(PtEnum::cases(), 'value')
            )->nullable();
            $table->enum('visitGroup', [
                'INDIVIDUAL',
                'ORGANIZATION',
            ])->nullable();
            $table->enum('childVisitGroup', [
                'INDIVIDUAL',
                'ORGANIZATION',
                'AGENCY'
            ])->nullable();
            $table->string('identification', 200)->nullable();
            $table->string('refuse', 200)->nullable();
            $table->integer('count')->unsigned()->default(1);
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->text('reason')->nullable();

            // add those 2 columns to enable publication timeframe fields (you can use publish_start_date only if you don't need to provide the ability to specify an end date)
            // $table->timestamp('publish_start_date')->nullable();
            // $table->timestamp('publish_end_date')->nullable();
        });

        Schema::create('visitation_schedule_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'visitation_schedule');
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('visitation_schedule_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'visitation_schedule');
        });

        Schema::create('visitation_schedule_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'visitation_schedule');
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitation_schedule_revisions');
        Schema::dropIfExists('visitation_schedule_translations');
        Schema::dropIfExists('visitation_schedule_slugs');
        Schema::dropIfExists('visitation_schedules');
    }
};
