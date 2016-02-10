<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedProcessLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_process_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('process_log_id')->unsigned();
            $table->foreign('process_log_id')->references('id')->on('process_logs');
            $table->text('reason');
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
        Schema::drop('failed_process_logs');
    }
}
