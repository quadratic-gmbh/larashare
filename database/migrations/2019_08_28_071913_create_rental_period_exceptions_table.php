<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalPeriodExceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {           
      Schema::create('rental_period_exceptions', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('bike_id');
        $table->unsignedBigInteger('rental_place_id');
        $table->dateTime('date_time_from');
        $table->dateTime('date_time_to');        
        $table->boolean('available');
        $table->timestamps();
      });
      
      Schema::table('rental_period_exceptions', function (Blueprint $table) {
        $table->foreign('bike_id')->references('id')->on('bikes');
        $table->foreign('rental_place_id')->references('id')->on('rental_places');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('rental_period_exceptions');
    }
}
