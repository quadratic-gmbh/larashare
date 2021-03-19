<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('genders', function (Blueprint $table) {
          $table->tinyIncrements('id');
          $table->string('name');
        });
        
        // insert male and female
        DB::table('genders')->insert([
          ['name' => 'MALE'],
          ['name' => 'FEMALE']
        ]);
        
        // create gender reference on user
        Schema::table('users', function (Blueprint $table) {
          $table->unsignedTinyInteger('gender_id')->nullable();
          $table->foreign('gender_id')->references('id')->on('genders');
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
        $table->dropForeign(['gender_id']);
        $table->dropColumn('gender_id');
      });
      Schema::dropIfExists('genders');
    }
}
