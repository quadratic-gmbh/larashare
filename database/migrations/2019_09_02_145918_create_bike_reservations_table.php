<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBikeReservationsTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('bike_reservations', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('bike_id');
      $table->unsignedBigInteger('user_id');
      $table->dateTime('reserved_from');
      $table->dateTime('reserved_to');
      $table->string('purpose', 512)->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
    
    Schema::table('bike_reservations', function (Blueprint $table) {
      $table->foreign('user_id')->references('id')->on('users');
      $table->foreign('bike_id')->references('id')->on('bikes');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('bike_reservations');
  }
}
