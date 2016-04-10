<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentExperimentLogIdToDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->integer('current_experiment_log_id')->unsigned()->nullable();
            $table->foreign('current_experiment_log_id')->references('id')->on('experiment_logs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign('devices_current_experiment_log_id_foreign');
            $table->dropColumn('current_experiment_log_id');
        });
    }
}
