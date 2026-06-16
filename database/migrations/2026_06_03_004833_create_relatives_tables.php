<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('relatives', function (Blueprint $table) {
            // this will create an id, a "published" column, and soft delete and timestamps columns
            createDefaultTableFields($table);
            
            $table->integer('position')->unsigned()->nullable();
            
            // add those 2 columns to enable publication timeframe fields (you can use publish_start_date only if you don't need to provide the ability to specify an end date)
            // $table->timestamp('publish_start_date')->nullable();
            // $table->timestamp('publish_end_date')->nullable();
        });

        Schema::create('relative_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'relative');
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('relative_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'relative');
        });

        Schema::create('relative_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'relative');
        });
    }

    public function down()
    {
        Schema::dropIfExists('relative_revisions');
        Schema::dropIfExists('relative_translations');
        Schema::dropIfExists('relative_slugs');
        Schema::dropIfExists('relatives');
    }
};
