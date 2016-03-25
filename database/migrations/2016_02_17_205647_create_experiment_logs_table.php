<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExperimentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiment_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('experiment_id')->unsigned();
            $table->foreign('experiment_id')->references('id')->on('experiments');
            $table->text('input_arguments');
            $table->string('output_path')->nullable();
            $table->integer("requested_by")->nullable();
            $table->integer("duration")->nullable();
            $table->integer("measuring_rate")->nullable();
            $table->dateTime("finished_at")->nullable()->default(null);
            $table->dateTime("stopped_at")->nullable()->default(null);
            $table->dateTime("timedout_at")->nullable()->default(null);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('experiment_logs');
    }
}
