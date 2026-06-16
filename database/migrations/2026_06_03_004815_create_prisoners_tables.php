<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prisoners', function (Blueprint $table) {
            // this will create an id, a "published" column, and soft delete and timestamps columns
            createDefaultTableFields($table);
            
            $table->integer('position')->unsigned()->nullable();
            $table->string('code', 200)->nullable();
            $table->string('username', 200)->nullable();
            $table->boolean('is_allow_visit')->default(true);
            // add those 2 columns to enable publication timeframe fields (you can use publish_start_date only if you don't need to provide the ability to specify an end date)
            // $table->timestamp('publish_start_date')->nullable();
            // $table->timestamp('publish_end_date')->nullable();
        });

        Schema::create('prisoner_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'prisoner');
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('prisoner_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'prisoner');
        });

        Schema::create('prisoner_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'prisoner');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prisoner_revisions');
        Schema::dropIfExists('prisoner_translations');
        Schema::dropIfExists('prisoner_slugs');
        Schema::dropIfExists('prisoners');
    }
};
