<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenteeLimitationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_period_rentee_limitations', function (Blueprint $table) {
          $table->unsignedBigInteger('rental_period_id');
          $table->string('email');
          $table->primary(['rental_period_id', 'email']);
        });
        
        Schema::table('rental_period_rentee_limitations', function(Blueprint $table) {
          $table->foreign('rental_period_id')->references('id')->on('rental_periods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rental_period_rentee_limitations');
    }
}
