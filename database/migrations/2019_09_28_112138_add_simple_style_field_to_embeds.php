<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSimpleStyleFieldToEmbeds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('embeds', function (Blueprint $table) {
          $table->json('simple_css')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('embeds', function (Blueprint $table) {
          $table->dropColumn('simple_css');
        });
    }
}
