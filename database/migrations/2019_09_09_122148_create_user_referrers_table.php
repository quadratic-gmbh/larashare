<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserReferrersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('user_referrers', function (Blueprint $table) {
        $table->tinyIncrements('id');
        $table->string('name');
      });
      
      DB::table('user_referrers')->insert([
        ['name' => 'KlimaEntLaster Mattersburg']
      ]);
      
      Schema::table('users', function (Blueprint $table) {
        $table->unsignedTinyInteger('user_referrer_id')->nullable();
        $table->foreign('user_referrer_id')->references('id')->on('user_referrers');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['user_referrer_id']);
        $table->dropColumn('user_referrer_id');
      });
      Schema::dropIfExists('user_referrers');
    }
}
