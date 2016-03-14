<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultExperimentIdToDevicesTable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->integer('default_experiment_id')->unsigned()->nullable();
            $table->foreign('default_experiment_id')->references('id')->on('experiments');
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
            $table->dropForeign('devices_default_experiment_id_foreign');
            $table->dropColumn('default_experiment_id');
        });
    }
}
