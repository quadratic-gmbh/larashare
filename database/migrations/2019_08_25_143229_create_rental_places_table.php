<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalPlacesTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('rental_places', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('bike_id');
      $table->string('name');
      $table->string('street_name');
      $table->string('house_number', 10);
      $table->string('postal_code', 10);
      $table->string('city');
      $table->text('description')->nullable();
      $table->timestamps();
    });
    
    Schema::table('rental_places', function (Blueprint $table) {
      $table->foreign('bike_id')
        ->references('id')
        ->on('bikes');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('rental_places');
  }
}
