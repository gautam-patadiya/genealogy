<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEventsTable281 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events',function(Blueprint $table){
            $table->integer('event_type_id')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->string('event_type')->nullable()->change();
            $table->string('hlink', 50)->nullable()->comment('To Hold XML hLink Reference')->after('name');
            $table->text('description')->nullable()->change();
            $table->string('date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events',function(Blueprint $table){
            $table->integer('event_type_id')->change();
            $table->string('name')->change();
            $table->string('event_type')->change();
            $table->dropColumn('hlink');
            $table->text('description')->change();
            // $table->date('date')->nullable()->change();
        });
    }
}
