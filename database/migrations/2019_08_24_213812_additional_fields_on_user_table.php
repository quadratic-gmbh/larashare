<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdditionalFieldsOnUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('users', function (Blueprint $table) {
        $table->date('date_of_birth')->nullable();
        $table->string('street_name')->nullable();
        $table->string('house_number',10)->nullable();        
        $table->string('postal_code',10)->nullable();
        $table->string('city')->nullable();
        $table->string('telephone',100)->nullable();
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
        $table->dropColumn('date_of_birth');
        $table->dropColumn('street_name');
        $table->dropColumn('house_number');
        $table->dropColumn('postal_code');
        $table->dropColumn('city');
        $table->dropColumn('telephone');
      });
    }
}
