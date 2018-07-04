<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotesTable261 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notes',function(Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->string('is_active')->nullable()->change();
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
        Schema::table('notes',function(Blueprint $table) {
            $table->dropColumn('hlink');
            $table->string('name')->change();
            $table->text('description')->change();
            $table->string('is_active')->change();
            $table->integer('type_id')->change();
        });
    }
}
