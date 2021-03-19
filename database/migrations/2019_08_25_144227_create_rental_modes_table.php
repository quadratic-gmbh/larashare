<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentalModesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_modes', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name',50);            
        });
        
        DB::table('rental_modes')->insert([
          ['name' => 'INSTANT_RESERVATION'],
          ['name' => 'INQUIRY']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rental_modes');
    }
}
