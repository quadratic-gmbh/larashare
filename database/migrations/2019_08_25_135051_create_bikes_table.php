<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBikesTable extends Migration
{

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    // pricing rate types
    Schema::create('pricing_rates', function(Blueprint $table) {
      $table->tinyIncrements('id');
      $table->string('name',50);
    });
    
    // insert rates
    DB::table('pricing_rates')->insert([
      ['name' => 'HOURLY'],
      ['name' => 'DAILY'],
      ['name' => 'WEEKLY']
    ]);
    
    // bike table
    Schema::create('bikes', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('model');
      $table->unsignedTinyInteger('wheels')->default(2);
      $table->unsignedTinyInteger('children')->default(0);
      $table->boolean('electric')->default(false);
      $table->unsignedTinyInteger('box_type_id')->nullable();
      $table->unsignedSmallInteger('cargo_weight');
      $table->unsignedSmallInteger('cargo_length');
      $table->unsignedSmallInteger('cargo_width');
      $table->text('misc_equipment')->nullable();
      $table->text('description')->nullable();
      $table->unsignedTinyInteger('pricing_type_id');
      $table->unsignedInteger('pricing_value')->nullable();
      $table->unsignedTinyInteger('pricing_rate_id')->nullable();
      $table->unsignedInteger('rental_duration');
      $table->boolean('rental_duration_in_days')->default(false);
      $table->boolean('end_to_end_renting')->default(false);
      $table->boolean('terms_of_use_file')->default(false);
      $table->boolean('accepts_tos')->default(false);
      $table->boolean('has_permission')->default(false);
      $table->boolean('public')->default(false);
      $table->softDeletes();
      $table->timestamps();
    });
    
    Schema::table('bikes', function(Blueprint $table) {
      $table->foreign('box_type_id')->references('id')->on('box_types');
      $table->foreign('pricing_type_id')->references('id')->on('pricing_types');
      $table->foreign('pricing_rate_id')->references('id')->on('pricing_rates');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {    
    Schema::dropIfExists('bikes');
    Schema::dropIfExists('pricing_rates');    
  }
}

