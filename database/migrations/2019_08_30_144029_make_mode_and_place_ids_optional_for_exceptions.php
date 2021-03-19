<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeModeAndPlaceIdsOptionalForExceptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('rental_period_exceptions', function (Blueprint $table) {
        $table->unsignedBigInteger('rental_place_id')->nullable()->change();
        $table->unsignedTinyInteger('rental_mode_id')->after('rental_place_id')->nullable();
        $table->foreign('rental_mode_id')->references('id')->on('rental_modes');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('rental_period_exceptions', function (Blueprint $table) {
        // not making rental_place_id non-NULL again because it could cause issues
        $table->dropForeign(['rental_mode_id']);
        $table->dropColumn('rental_mode_id');
      });
    }
}
