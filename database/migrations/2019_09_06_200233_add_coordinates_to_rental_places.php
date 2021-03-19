<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoordinatesToRentalPlaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('rental_places', function (Blueprint $table) {
        $table->decimal('lon',9,6)->nullable()->after('city');
        $table->decimal('lat',9,6)->nullable()->after('lon');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('rental_places', function (Blueprint $table) {
        $table->dropColumn('lon');
        $table->dropColumn('lat');        
      });
    }
}
