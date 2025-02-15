<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKelBikeFlagToBikes extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('bikes', function (Blueprint $table) {
      $table->boolean('kel_bike')->default(false);
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
      $table->dropColumn('kel_bike');
    });
  }
}
