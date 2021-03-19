<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BikesBoxTypeIdNotNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::statement('ALTER TABLE `bikes` CHANGE `box_type_id` `box_type_id` TINYINT(3) UNSIGNED NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::statement('ALTER TABLE `bikes` CHANGE `box_type_id` `box_type_id` TINYINT(3) UNSIGNED NULL');
    }
}
