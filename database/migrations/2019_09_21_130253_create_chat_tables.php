<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('chat_types', function (Blueprint $table) {
        $table->tinyIncrements('id');
        $table->string('name', 50);
      });
      
      DB::table('chat_types')->insert([
        ['name' => 'BIKE']
      ]);
      
      Schema::create('chats', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedTinyInteger('chat_type_id');
        $table->timestamps();
        $table->foreign('chat_type_id')->references('id')->on('chat_types');
      });
      
      Schema::create('chat_messages', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('chat_id');
        $table->unsignedBigInteger('user_id');
        $table->string('message', 500);
        $table->timestamps();
        $table->foreign('chat_id')->references('id')->on('chats');
        $table->foreign('user_id')->references('id')->on('users');
      });
      
      Schema::create('chat_last_reads', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('chat_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
        $table->foreign('chat_id')->references('id')->on('chats');
        $table->foreign('user_id')->references('id')->on('users');
      });
      
      Schema::create('chat_user', function (Blueprint $table) {
        $table->unsignedBigInteger('chat_id');
        $table->unsignedBigInteger('user_id');
        $table->primary(['chat_id','user_id']);
        $table->foreign('chat_id')->references('id')->on('chats');
        $table->foreign('user_id')->references('id')->on('users');
      });
      
      Schema::create('bike_chat', function (Blueprint $table) {
        $table->unsignedBigInteger('bike_id');
        $table->unsignedBigInteger('chat_id');
        $table->primary(['bike_id','chat_id']);  
        $table->foreign('bike_id')->references('id')->on('bikes');
        $table->foreign('chat_id')->references('id')->on('chats');
      });
      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('bike_chat');
      Schema::dropIfExists('chat_user');
      Schema::dropIfExists('chat_last_reads');
      Schema::dropIfExists('chat_messages');
      Schema::dropIfExists('chats');
      Schema::dropIfExists('chat_types');
    }
}
