<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDbActivityLogTable extends Migration
{
    public function up()
    {
        Schema::create('db_activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('sql');
            $table->json('bindings')->nullable();
            $table->unsignedInteger('time')->nullable();
            $table->string('table_name')->nullable();
            $table->unsignedInteger('hit_count')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('db_activity_log');
    }
}