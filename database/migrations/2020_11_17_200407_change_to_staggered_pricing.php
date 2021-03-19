<?php

use App\Bike;
use App\PricingRate;
use App\PricingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeToStaggeredPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('bikes', function (Blueprint $table) {
        $table->json('pricing_values')->nullable()->after('pricing_type_id');
      });
      
      $bikes = Bike::where([
        ['pricing_value', '!=', null],
        ['pricing_rate_id', '!=', null],
        ['pricing_type_id', '=', PricingType::getFixedId()]
      ])->get();
      
      $pricing_values = [
        'hourly' => null,
        'daily' => null,
        'weekly' => null,
      ];
      
      foreach($bikes as $b){
        $values = $pricing_values;
        $values[strtolower($b->pricingRate->name)] = $b->pricing_value;
        $b->pricing_values = $values;
        $b->save();
      }
      
      Schema::table('bikes', function (Blueprint $table) {
        $table->dropForeign(['pricing_rate_id']);
        $table->dropColumn('pricing_value');
        $table->dropColumn('pricing_rate_id');
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
        $table->dropColumn('pricing_values');
      });
      
      Schema::table('bikes', function (Blueprint $table) {
        $table->unsignedTinyInteger('pricing_rate_id')->nullable()->after('pricing_type_id');
        $table->unsignedInteger('pricing_value')->nullable()->after('pricing_type_id');
      });
      
      Schema::table('bikes', function (Blueprint $table) {
        $table->foreign('pricing_rate_id')->references('id')->on('pricing_rates');
      });
    }
}
