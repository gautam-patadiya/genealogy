<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCitationsTable271 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('citations', function(Blueprint $table){
            $table->string('name')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->integer('is_active')->nullable()->change();
            $table->integer('volume_id')->nullable()->change();
            $table->integer('page_id')->nullable()->change();
            $table->integer('confidence')->nullable()->change();
            $table->integer('source_id')->nullable()->change();
            $table->string('hlink', 50)->nullable()->comment('To Hold XML hLink Reference')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('citations', function(Blueprint $table){
            $table->string('name')->change();
            $table->text('description')->change();
            $table->integer('is_active')->change();
            $table->integer('volume_id')->change();
            $table->integer('page_id')->change();
            $table->integer('confidence')->change();
            $table->integer('source_id')->change();
        });
    }
}
