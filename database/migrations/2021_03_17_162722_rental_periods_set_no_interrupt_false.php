<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RentalPeriodsSetNoInterruptFalse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      foreach(['rental_periods', 'rental_period_exceptions'] as $table) {
        DB::table($table)->update(['no_interrupt' => false]);
      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
