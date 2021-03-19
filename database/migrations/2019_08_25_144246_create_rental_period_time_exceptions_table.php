<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalPeriodTimeExceptionsTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
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

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
    {
        Schema::dropIfExists('rental_period_time_exceptions');
    }
}
