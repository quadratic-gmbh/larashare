<?php

namespace App\Console\Commands;

use App\Bike;
use App\Mail\RentalPeriodReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailRentalPeriodReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:rental_period_reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if a bike doesnt have any rental times within the next 30 days.';

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
      $now = now();
      $one_week_ago = $now->clone()->subWeek();
      $in_30_days = $now->clone()->addDays(30)->format('Y-m-d');
      
      // get bikes that dont fit the criteria
      $bikes = Bike::where('public',true)
      ->with('owner')
      ->whereNested(function($query) use ($one_week_ago) {
        $query->where('rp_reminder_at',null)
        ->orWhere('rp_reminder_at','<',$one_week_ago);
      })
      ->whereDoesntHave('rentalPeriods',function($query) use ($in_30_days) {
        $query->where('date_to','>=',$in_30_days);
      })
      ->get();      
      
      // queue reminders for all involved bikes
      foreach($bikes as $b) {
        if($b->owner !== null) {
          Mail::to($b->owner->email)->send(new RentalPeriodReminder($b));
        }        
        $b->update(['rp_reminder_at' => $now]);
      }
    }
}
