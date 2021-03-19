<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdaptRentalPeriodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // add time_from/time_to to table  
      Schema::table('rental_periods', function (Blueprint $table) {
        $table->time('time_to')->after('date_to');
        $table->time('time_from')->after('date_to');     
        $table->unsignedTinyInteger('rental_mode_id')->after('rental_place_id');
        $table->foreign('rental_mode_id')->references('id')->on('rental_modes');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('rental_periods', function (Blueprint $table) {
        $table->dropColumn('time_to');
        $table->dropColumn('time_from');
        $table->dropForeign(['rental_mode_id']);
        $table->dropColumn('rental_mode_id');
      });
    }
}
