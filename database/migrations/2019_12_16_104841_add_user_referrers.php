<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserReferrers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::table('user_referrers')->insert([
        ['name' => 'KlimaEntLaster Freistadt'],
        ['name' => 'KlimaEntLaster Amstetten'],
        ['name' => 'Soziale Medien'],
        ['name' => 'Gezielte Suche im Internet'],
        ['name' => 'Zeitung'],
        ['name' => 'Freunde/Bekannte'],
        ['name' => 'Sonstiges']
      ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::table('user_referrers')
      ->whereIn('name', 
        ['KlimaEntLaster Freistadt','KlimaEntLaster Amstetten', 'Soziale Medien', 'Gezielte Suche im Internet', 'Zeitung', 'Freunde/Bekannte','Sonstiges'],
        'or')->delete();
    }
}
