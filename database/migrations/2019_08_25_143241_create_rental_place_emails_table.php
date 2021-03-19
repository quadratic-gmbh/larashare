<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalPlaceEmailsTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('rental_place_emails', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('rental_place_id');
      $table->string('email');
      $table->boolean('notify_on_reservation')->default(false);
      $table->timestamps();
    });
    
    Schema::table('rental_place_emails', function (Blueprint $table) {
      $table->foreign('rental_place_id')
        ->references('id')
        ->on('rental_places');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('rental_place_emails');
  }
}
