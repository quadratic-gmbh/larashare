<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmbedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // create embeds table
      Schema::create('embeds', function (Blueprint $table) {
        $table->increments('id');
        $table->unsignedBigInteger('user_id');
        $table->boolean('has_custom_css');      
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
      });
      Schema::table('embeds', function (Blueprint $table) {
        $table->foreign('user_id')->references('id')->on('users');
      });
        
      // create mapping table for embed to bike
      Schema::create('bike_embed', function (Blueprint $table) {
        $table->unsignedBigInteger('bike_id');
        $table->unsignedInteger('embed_id');
        $table->primary(['bike_id','embed_id']);
      });
      Schema::table('bike_embed', function (Blueprint $table) {
        $table->foreign('bike_id')->references('id')->on('bikes');      
        $table->foreign('embed_id')->references('id')->on('embeds');;
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('bike_embed');
      Schema::dropIfExists('embeds');
    }
}
