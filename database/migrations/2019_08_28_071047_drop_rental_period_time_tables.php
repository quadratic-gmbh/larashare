<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropRentalPeriodTimeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // drop rental period times
      Schema::drop('rental_period_times');
      
      // drop rental period time exceptions
      Schema::drop('rental_period_time_exceptions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      // restore rental period times
      Schema::create('rental_period_times', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('rental_period_id');
        $table->enum('weekday', [
          'MONDAY',
          'TUESDAY',
          'WEDNESDAY',
          'THURSDAY',
          'FRIDAY',
          'SATURDAY',
          'SUNDAY'
        ]);
        $table->time('time_from');
        $table->time('time_to');
        $table->timestamps();
      });
        
      Schema::table('rental_period_times', function (Blueprint $table) {
        $table->foreign('rental_period_id')->references('id')->on('rental_periods');
      });
      
      // restore time exceptions
      Schema::create('rental_period_time_exceptions', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('rental_period_id');
        $table->dateTime('date_time_from');
        $table->dateTime('date_time_to');
        $table->timestamps();
      });
        
      Schema::table('rental_period_time_exceptions', function (Blueprint $table) {
        $table->foreign('rental_period_id')
        ->references('id')
        ->on('rental_periods');
      });
    }
}
