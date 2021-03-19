<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePricingValuesToDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bikes', function (Blueprint $table) {            
          $table->decimal('pricing_value',10,2)->nullable()->change();
          $table->decimal('pricing_deposit',10,2)->nullable()->change();          
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
          $table->unsignedInteger('pricing_value')->nullable()->change();
          $table->unsignedSmallInteger('pricing_deposit')->nullable()->change();          
        });
    }
}
