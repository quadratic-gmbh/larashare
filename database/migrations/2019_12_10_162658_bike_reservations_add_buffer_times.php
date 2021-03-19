<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BikeReservationsAddBufferTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('bike_reservations', function (Blueprint $table) {
        $table->unsignedSmallInteger('buffer_time_before')->nullable()->after('purpose');
        $table->unsignedSmallInteger('buffer_time_after')->nullable()->after('buffer_time_before');
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
        $table->dropColumn('buffer_time_before');
        $table->dropColumn('buffer_time_after');
      });
    }
}
