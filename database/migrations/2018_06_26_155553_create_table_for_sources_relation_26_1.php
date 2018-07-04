<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableForSourcesRelation261 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_repositories', function (Blueprint $table) {
            $table->integer('source_id')->index();
            $table->integer('repository_id')->index();
        });

        Schema::create('source_notes', function (Blueprint $table) {
            $table->integer('source_id')->index();
            $table->integer('note_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('source_repositories');
        Schema::drop('source_notes');
    }
}
