<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBikeAccessList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('bike_editors', function(Blueprint $table) {
        $table->unsignedBigInteger('bike_id');
        $table->unsignedBigInteger('user_id');                
        $table->primary(['bike_id','user_id']);
        $table->foreign('bike_id')->references('id')->on('bikes');
        $table->foreign('user_id')->references('id')->on('users');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('bike_editors');
    }
}
