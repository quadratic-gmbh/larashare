<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRentalPeriodReminderTimestamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('bikes', function(Blueprint $table) {
        $table->timestamp('rp_reminder_at')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('bikes', function(Blueprint $table) {
        $table->dropColumn('rp_reminder_at');
      });
    }
}
