<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveMaxRentalDurationAndEndToEndToRentalPeriods extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    // add fields to rental_periods
    Schema::table('rental_periods', function (Blueprint $table) {
      $table->unsignedInteger('rental_duration')->default(0);
      $table->boolean('rental_duration_in_days')->default(0);
      
      // original: end_to_end_renting
      $table->boolean('no_interrupt')->default(0);
    });
    
    // add same fields to rental_period_exceptions    
    Schema::table('rental_period_exceptions', function (Blueprint $table) {
      $table->unsignedInteger('rental_duration')->nullable();
      $table->boolean('rental_duration_in_days')->default(0);
      $table->boolean('no_interrupt')->default(0);
    });
    
    // update rental period with values from bikes
    DB::statement("UPDATE rental_periods
      INNER JOIN rental_places ON rental_places.id = rental_periods.rental_place_id 
      INNER JOIN bikes ON bikes.id = rental_places.bike_id 
      SET 
      rental_periods.rental_duration = bikes.rental_duration, 
      rental_periods.rental_duration_in_days = bikes.rental_duration_in_days,
      rental_periods.no_interrupt = bikes.end_to_end_renting
    ");
    
    // do the same for exceptions
    DB::statement("UPDATE rental_period_exceptions  
    INNER JOIN bikes ON bikes.id = rental_period_exceptions.bike_id 
    SET 
     rental_period_exceptions.rental_duration = bikes.rental_duration, 
     rental_period_exceptions.rental_duration_in_days = bikes.rental_duration_in_days,
     rental_period_exceptions.no_interrupt = bikes.end_to_end_renting
    ");
    
    // drop columns from bikes
    Schema::table('bikes', function (Blueprint $table) {
      $table->dropColumn('rental_duration');
      $table->dropColumn('rental_duration_in_days');
      $table->dropColumn('end_to_end_renting');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {        
    // restore bike fields    
    Schema::table('bikes', function (Blueprint $table) {
      $table->boolean('end_to_end_renting')->after('public')->default(0);
      $table->boolean('rental_duration_in_days')->after('public')->default(0);
      $table->unsignedInteger('rental_duration')->after('public')->default(0);      
    });
    
    // update bikes with best qualified values from rental periods
    $rp = DB::table('rental_places')
    ->join('rental_periods','rental_periods.rental_place_id','=','rental_places.id')
    ->select([
      'rental_places.bike_id',
      DB::raw('MAX(rental_periods.rental_duration) as rental_duration'),
      DB::raw('MIN(rental_periods.no_interrupt) as end_to_end_renting'),
    ])
    ->groupBy('rental_places.bike_id');      
    DB::table('bikes')
    ->joinSub($rp, 'rp', function($join) {
      $join->on('bikes.id','=','rp.bike_id');
    })
    ->update([      
      'bikes.rental_duration' => DB::raw('rp.rental_duration'),
      'bikes.rental_duration_in_days' => DB::raw('IF(MOD(rp.rental_duration,24) = 0, 1, 0)'),      
      'bikes.end_to_end_renting' => DB::raw('rp.end_to_end_renting'),      
    ]);     
    
    // drop columns from rental_periods
    Schema::table('rental_periods', function (Blueprint $table) {
      $table->dropColumn('rental_duration');
      $table->dropColumn('rental_duration_in_days');
      $table->dropColumn('no_interrupt');
    });
    // drop from rental_period_exceptions
    Schema::table('rental_period_exceptions', function (Blueprint $table) {
      $table->dropColumn('rental_duration');
      $table->dropColumn('rental_duration_in_days');
      $table->dropColumn('no_interrupt');
    });
  }
}
