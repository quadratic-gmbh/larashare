<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExceptionRenteeLimitationTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('rental_period_exception_rentee_limitations', function (Blueprint $table) {
      $table->unsignedBigInteger('rental_period_exception_id');
      $table->string('email');
      $table->primary(['rental_period_exception_id', 'email'], 'rperl_rental_period_exception_id_email_primary');
    });
      
      Schema::table('rental_period_exception_rentee_limitations', function(Blueprint $table) {
        $table->foreign('rental_period_exception_id', 'rperl_rental_period_exception_id_foreign')->references('id')->on('rental_period_exceptions');
      });
  }
  
  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('rental_period_exception_rentee_limitations');
  }
}
