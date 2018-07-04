<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSourcesTable261 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sources', function(Blueprint $table){
            $table->string('name')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->integer('is_active')->nullable()->change();
            $table->integer('author_id')->nullable()->change();
            $table->integer('repository_id')->nullable()->change();
            $table->integer('publication_id')->nullable()->change();
            $table->integer('type_id')->nullable()->change();
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
        Schema::table('sources', function(Blueprint $table){
            $table->string('name')->change();
            $table->text('description')->change();
            $table->integer('is_active')->change();
            $table->integer('author_id')->change();
            $table->integer('repository_id')->change();
            $table->integer('publication_id')->change();
            $table->integer('type_id')->change();
            $table->dropColumn('hlink');
        });
    }
}
