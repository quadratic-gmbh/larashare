<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSurveyMailSentFlagToReservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bike_reservations', function (Blueprint $table) {
          $table->boolean('survey_mail_sent')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bike_reservations', function (Blueprint $table) {
          $table->dropColumn('survey_mail_sent');
        });
    }
}
