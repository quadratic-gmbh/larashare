<?php

use App\PricingType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePricingTypeOnRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $result =  DB::table('pricing_types')
      ->where('name', 'ON_REQUEST')
      ->first()
      ;
      
      DB::table('bikes')
        ->where('pricing_type_id', $result->id)
        ->update(['pricing_type_id' => PricingType::getDonationId()])
      ;
      
      DB::table('pricing_types')
        ->where('name', 'ON_REQUEST')
        ->delete()
      ;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::table('pricing_types')->insert([
        ['NAME' => 'ON_REQUEST']
      ]);
    }
}
