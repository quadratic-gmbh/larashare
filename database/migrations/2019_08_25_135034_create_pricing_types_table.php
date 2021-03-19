<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pricing_types', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name',50);
        });
        
        // insert pricing types
        DB::table('pricing_types')->insert([
          ['name' => 'FREE'],
          ['NAME' => 'DONATION'],
          ['NAME' => 'FREE_OR_DONATION'],
          ['NAME' => 'FIXED'],
          ['NAME' => 'ON_REQUEST'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pricing_types');
    }
}
