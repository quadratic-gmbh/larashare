<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeekdaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekdays', function (Blueprint $table) {
          $table->unsignedTinyInteger('id')->primary();
          $table->string('name',9);  
        });
        
        DB::table('weekdays')->insert([
          ['id' => 1, 'name' => 'MONDAY'],
          ['id' => 2, 'name' => 'TUESDAY'],
          ['id' => 3, 'name' => 'WEDNESDAY'],
          ['id' => 4, 'name' => 'THURSDAY'],
          ['id' => 5, 'name' => 'FRIDAY'],
          ['id' => 6, 'name' => 'SATURDAY'],
          ['id' => 7, 'name' => 'SUNDAY']
        ]);
        
        // add reference to exceptions
        Schema::table('rental_period_exceptions', function(Blueprint $table) {
          $table->unsignedTinyInteger('weekday_id')->after('rental_place_id');
          $table->foreign('weekday_id')->references('id')->on('weekdays');
        });
        
        // create mapping betwee weekdays and rental_periods
        Schema::create('rental_period_weekday', function(Blueprint $table) {
          $table->unsignedBigInteger('rental_period_id');
          $table->unsignedTinyInteger('weekday_id');     
          
          $table->primary(['rental_period_id','weekday_id']);
          
          $table->foreign('rental_period_id')->references('id')->on('rental_periods');
          $table->foreign('weekday_id')->references('id')->on('weekdays');
        });
               
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('rental_period_exceptions', function(Blueprint $table) {
        $table->dropForeign(['weekday_id']);
        $table->dropColumn('weekday_id');        
      });
      
      Schema::dropIfExists('rental_period_weekday');
      
      Schema::dropIfExists('weekdays');
      
      
    }
}
