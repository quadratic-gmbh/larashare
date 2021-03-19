<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalPeriodsTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {    
    Schema::create('rental_periods', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('rental_place_id');      
      $table->date('date_from');
      $table->date('date_to');
      $table->timestamps();            
    });
    
    Schema::table('rental_periods', function (Blueprint $table) {
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
    Schema::dropIfExists('rental_periods');
  }
}
