<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfirmationFieldToBikeReservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bike_reservations', function (Blueprint $table) {
          $table->timestamp('confirmed_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bike_reservations', function (Blueprint $table) {
          $table->dropColumn('confirmed_on');
        });
    }
}
