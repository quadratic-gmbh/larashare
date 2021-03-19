<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BikesAddDepositBufferFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('bikes', function (Blueprint $table) {
        $table->unsignedSmallInteger('pricing_deposit')->nullable()->after('pricing_rate_id');
        $table->unsignedSmallInteger('buffer_time_before')->nullable()->after('description');
        $table->unsignedSmallInteger('buffer_time_after')->nullable()->after('buffer_time_before');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('bikes', function (Blueprint $table) {
        $table->dropColumn('pricing_deposit');
        $table->dropColumn('buffer_time_before');
        $table->dropColumn('buffer_time_after');
      });
    }
}
