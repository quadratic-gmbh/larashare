<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('filename');
          $table->timestamp('created_at');
        });
        
        Schema::create('bike_image', function(Blueprint $table){
          $table->unsignedBigInteger('bike_id');
          $table->unsignedBigInteger('image_id');
          $table->primary(['bike_id','image_id']);          
        });
        
        Schema::table('bike_image', function (Blueprint $table) {
          $table->foreign('bike_id')->references('id')->on('bikes');
          $table->foreign('image_id')->references('id')->on('images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('bike_image');
      Schema::dropIfExists('images');
    }
}
