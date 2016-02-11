<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->string('status')->default("offline");
            $table->string('active_token')->nullable();
            $table->string('device_type');
            $table->integer('experiment_type_id')->unsigned()->nullable();
            $table->foreign("experiment_type_id")->references('id')->on('experiment_types');
            $table->string('port')->nullable();
            $table->integer('attached_pid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('devices');
    }
}
