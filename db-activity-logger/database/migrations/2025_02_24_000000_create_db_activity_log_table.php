<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDbActivityLogTable extends Migration
{
    public function up()
    {
        Schema::create('db_activity_log', function (Blueprint $table) {
            $table->increments('id');
            $table->text('sql');
            $table->text('bindings');
            $table->string('table_name'); // Ensure this exists
            $table->integer('time');
            $table->integer('hit_count')->default(1); // Must be included
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('db_activity_log');
    }
}