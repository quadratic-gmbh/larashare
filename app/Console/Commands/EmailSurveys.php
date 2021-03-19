<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\BikeReservation;
use App\Mail\Survey;

class EmailSurveys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:surveys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send survey emails to reservations that ended about 24h ago';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       // get all bike reservations that ended between 48 and 24 hours ago.       
      $day1 = now()->subDay();      
      $day2 = $day1->copy()->subDay();

      $reservations = BikeReservation::where('survey_mail_sent',false)
      ->whereBetween('reserved_to',[$day2->format('Y-m-d H:i:s'),$day1->format('Y-m-d H:i:s')])
      ->whereNotNull('confirmed_on')
      ->whereHas('bike', function(Builder $query) {
        $query->isKelBike();
      })
      ->with('user')
      ->get();      

      foreach($reservations as $r) {        
        if ($r->user !== null) { // only send a mail if user wasnt deleted
          Mail::to($r->user->email)->queue(new Survey());
        }
        $r->survey_mail_sent = true;
        $r->save();
      }
    }
}
