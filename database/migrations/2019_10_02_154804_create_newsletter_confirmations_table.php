<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsletterConfirmationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletter_confirmations', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->string('email')->index();
          $table->string('token', 100);
          $table->timestamps();
        });
        
        Schema::table('users', function (Blueprint $table) {
          $table->timestamp('newsletter')->nullable()->after('gender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletter_confirmations');
        Schema::table('users', function (Blueprint $table) {
          $table->dropColumn('newsletter');
        });
    }
}
