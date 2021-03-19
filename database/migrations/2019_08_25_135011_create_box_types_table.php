<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoxTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('box_types', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name',50);            
        });
        
        // insert box types
        DB::table('box_types')->insert([
          ['name' => 'NON_LOCKABLE'],
          ['NAME' => 'LOCKABLE']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('box_types');
    }
}
