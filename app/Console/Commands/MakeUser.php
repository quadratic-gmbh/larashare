<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\User;

class MakeUser extends Command
{

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'make:user';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Make a new User';

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
    $firstname = $this->ask('firstname');
    $lastname = $this->ask('lastname');
    $email = $this->ask('email');
    $password = $this->secret('password');    
    
    $validator = Validator::make([
      'firstname' => $firstname,
      'lastname' => $lastname,
      'email' => $email,
      'password' => $password
    ], [
      'firstname' => [
        'required',
        'string',
        'max:255'
      ],
      'lastname' => [
        'required',
        'string',
        'max:255'
      ],
      'email' => [
        'required',
        'string',
        'email',
        'max:255',
        'unique:users'
      ],
      'password' => [
        'required',
        'string',
        'min:8'
      ]
    ]);
    try {
      $validator->validate();
    } catch (ValidationException $e) {
      $errors = $validator->errors();
      foreach ($errors->all() as $message) {
        echo $message . "\n";
      }
      
      return;
    }
    
    // create new user with given email/pw 
    $user = new User([
      'firstname' => $firstname,
      'lastname' => $lastname,
      'email' => $email,
      'password' => Hash::make($password)
    ]);    
    $user->save();
    
    echo "User created\n";
  }
}
